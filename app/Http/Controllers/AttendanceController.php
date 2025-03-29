<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Setting;
use App\Models\Workday;
use App\Models\Holiday;
use App\Services\FonnteService;
use App\Services\FaceVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    protected $faceVerificationService;
    
    /**
     * Constructor
     */
    public function __construct(FaceVerificationService $faceVerificationService)
    {
        $this->faceVerificationService = $faceVerificationService;
        
        // Set threshold dari setting jika ada
        $threshold = Setting::getValue('face_verification_threshold', 0.7);
        $this->faceVerificationService->setThreshold((float) $threshold);
    }
    
    /**
     * Menampilkan form absen masuk
     */
    public function checkInForm()
    {
        // Cek apakah sudah absen hari ini
        $today = Carbon::now($this->timezone)->toDateString();
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();
            
        if ($todayAttendance && $todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }
        
        // Cek apakah hari ini adalah hari kerja
        $todayName = Carbon::now($this->timezone)->locale('id')->isoFormat('dddd'); // Mendapatkan nama hari dalam bahasa Indonesia
        
        if (!Workday::isWorkingDay($todayName)) {
            return redirect()->route('dashboard')->with('error', 'Hari ini bukan hari kerja.');
        }
        
        // Cek apakah hari ini adalah hari libur
        $isHoliday = Holiday::whereDate('date', $today)->exists();
        if ($isHoliday) {
            return redirect()->route('dashboard')->with('error', 'Hari ini adalah hari libur.');
        }
        
        // Mendapatkan lokasi absensi yang aktif
        $locations = AttendanceLocation::getActiveLocations();
        
        return view('attendance.check-in', compact('locations'));
    }
    
    /**
     * Proses absen masuk
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string|max:2000000', // Maksimal sekitar 2MB dalam base64
        ], [
            'photo.max' => 'Ukuran foto terlalu besar. Maksimal 2MB.',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Cek apakah sudah absen hari ini
        $today = Carbon::now($this->timezone)->toDateString();
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();
            
        if ($todayAttendance && $todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }
        
        // Cek apakah hari ini adalah hari libur
        $isHoliday = Holiday::whereDate('date', $today)->exists();
        if ($isHoliday) {
            return redirect()->route('dashboard')->with('error', 'Hari ini adalah hari libur.');
        }
        
        // Cek apakah berada di lokasi yang diizinkan
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $inLocation = false;
        $locationName = '';
        
        $locations = AttendanceLocation::getActiveLocations();
        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                $inLocation = true;
                $locationName = $location->name;
                break;
            }
        }
        
        if (!$inLocation) {
            return back()->with('error', 'Anda berada di luar area yang diizinkan untuk absensi.');
        }
        
        try {
            DB::beginTransaction();
            
            // Simpan foto dari base64
            $photoPath = null;
            if ($request->has('photo')) {
                $image = $request->photo;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageData = base64_decode($image);
                
                if ($imageData === false) {
                    return back()->with('error', 'Format foto tidak valid.');
                }
                
                // Generate unique filename
                $filename = 'attendance_' . time() . '_' . Auth::id() . '.jpg';
                $photoPath = 'attendance_photos/' . $filename;
                
                // Save to storage
                Storage::disk('public')->put($photoPath, $imageData);
                
                // Verifikasi wajah jika user memiliki foto profil
                $user = Auth::user();
                if ($user->photo) {
                    $verificationResult = $this->faceVerificationService->verifyFace($user->photo, $request->photo);
                    
                    // Jika verifikasi gagal
                    if (!$verificationResult['verified']) {
                        Storage::disk('public')->delete($photoPath);
                        
                        // Log untuk keperluan debugging
                        \Log::warning('Face verification failed', [
                            'user_id' => $user->id,
                            'score' => $verificationResult['score'],
                            'error' => $verificationResult['error']
                        ]);
                        
                        return back()->with('error', 'Verifikasi wajah gagal. Pastikan wajah Anda terlihat jelas.');
                    }
                }
            }
            
            // Mendapatkan jam masuk dari pengaturan
            $checkInTime = Setting::getValue('check_in_time', '08:00');
            $checkInTimeObj = Carbon::createFromFormat('H:i', $checkInTime, $this->timezone);
            $now = Carbon::now($this->timezone);
            
            // Tentukan status
            $status = 'hadir';
            if ($now->gt($checkInTimeObj)) {
                $status = 'terlambat';
            }
            
            // Log detail waktu untuk debug
            \Log::info('Check-in time details', [
                'user_id' => Auth::id(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'jakarta_time' => $now->format('Y-m-d H:i:s'),
                'check_in_time_setting' => $checkInTime,
                'check_in_time_obj' => $checkInTimeObj->format('H:i:s'),
                'now' => $now->format('H:i:s'),
                'is_late' => $now->gt($checkInTimeObj),
                'status' => $status
            ]);
            
            // Jika belum ada record untuk hari ini, buat baru
            if (!$todayAttendance) {
                $todayAttendance = new Attendance();
                $todayAttendance->user_id = Auth::id();
                $todayAttendance->date = $today;
            }
            
            // Update data absensi
            $todayAttendance->status = $status;
            $todayAttendance->check_in_time = $now->format('H:i:s');
            $todayAttendance->check_in_photo = $photoPath;
            $todayAttendance->check_in_latitude = $latitude;
            $todayAttendance->check_in_longitude = $longitude;
            $todayAttendance->save();
            
            // Kirim notifikasi WhatsApp
            $this->sendCheckInNotification($todayAttendance, $locationName, $status);
            
            DB::commit();
            
            return redirect()->route('dashboard')->with('success', 'Absen masuk berhasil dicatat pada ' . $now->format('H:i:s') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error during check-in: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi atau hubungi administrator.');
        }
    }
    
    /**
     * Menampilkan form absen pulang
     */
    public function checkOutForm()
    {
        // Cek apakah sudah absen masuk hari ini
        $today = Carbon::now($this->timezone)->toDateString();
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();
            
        if (!$todayAttendance || !$todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }
        
        if ($todayAttendance->check_out_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen pulang hari ini.');
        }
        
        // Mendapatkan lokasi absensi yang aktif
        $locations = AttendanceLocation::getActiveLocations();
        
        return view('attendance.check-out', compact('locations'));
    }
    
    /**
     * Proses absen pulang
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string|max:2000000', // Maksimal sekitar 2MB dalam base64
        ], [
            'photo.max' => 'Ukuran foto terlalu besar. Maksimal 2MB.',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Cek apakah sudah absen masuk hari ini
        $today = Carbon::now($this->timezone)->toDateString();
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();
            
        if (!$todayAttendance || !$todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }
        
        if ($todayAttendance->check_out_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen pulang hari ini.');
        }
        
        // Cek apakah berada di lokasi yang diizinkan
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $inLocation = false;
        $locationName = '';
        
        $locations = AttendanceLocation::getActiveLocations();
        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                $inLocation = true;
                $locationName = $location->name;
                break;
            }
        }
        
        if (!$inLocation) {
            return back()->with('error', 'Anda berada di luar area yang diizinkan untuk absensi.');
        }
        
        try {
            DB::beginTransaction();
            
            // Simpan foto dari base64
            $photoPath = null;
            if ($request->has('photo')) {
                $image = $request->photo;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageData = base64_decode($image);
                
                if ($imageData === false) {
                    return back()->with('error', 'Format foto tidak valid.');
                }
                
                // Generate unique filename
                $filename = 'attendance_' . time() . '_' . Auth::id() . '.jpg';
                $photoPath = 'attendance_photos/' . $filename;
                
                // Save to storage
                Storage::disk('public')->put($photoPath, $imageData);
                
                // Verifikasi wajah jika user memiliki foto profil
                $user = Auth::user();
                if ($user->photo) {
                    $verificationResult = $this->faceVerificationService->verifyFace($user->photo, $request->photo);
                    
                    // Jika verifikasi gagal
                    if (!$verificationResult['verified']) {
                        Storage::disk('public')->delete($photoPath);
                        
                        // Log untuk keperluan debugging
                        \Log::warning('Face verification failed', [
                            'user_id' => $user->id,
                            'score' => $verificationResult['score'],
                            'error' => $verificationResult['error']
                        ]);
                        
                        return back()->with('error', 'Verifikasi wajah gagal. Pastikan wajah Anda terlihat jelas.');
                    }
                }
            }
            
            $now = Carbon::now($this->timezone);
            
            // Log detail waktu untuk debug
            \Log::info('Check-out time details', [
                'user_id' => Auth::id(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'jakarta_time' => $now->format('Y-m-d H:i:s'),
            ]);
            
            // Update data absensi
            $todayAttendance->check_out_time = $now->format('H:i:s');
            $todayAttendance->check_out_photo = $photoPath;
            $todayAttendance->check_out_latitude = $latitude;
            $todayAttendance->check_out_longitude = $longitude;
            $todayAttendance->save();
            
            // Kirim notifikasi WhatsApp
            $this->sendCheckOutNotification($todayAttendance, $locationName);
            
            DB::commit();
            
            return redirect()->route('dashboard')->with('success', 'Absen pulang berhasil dicatat pada ' . $now->format('H:i:s') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error during check-out: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi atau hubungi administrator.');
        }
    }
    
    /**
     * Menampilkan riwayat absensi
     */
    public function history(Request $request)
    {
        $month = $request->get('month', Carbon::now($this->timezone)->month);
        $year = $request->get('year', Carbon::now($this->timezone)->year);
        
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(10);
            
        return view('attendance.history', compact('attendances', 'month', 'year'));
    }
    
    /**
     * Kirim notifikasi WhatsApp untuk absen masuk
     */
    private function sendCheckInNotification(Attendance $attendance, string $locationName, string $status)
    {
        // Cek apakah fitur notifikasi WhatsApp diaktifkan
        if (!config('services.fonnte.enable_notifications')) {
            return;
        }
        
        $user = $attendance->user;
        
        // Cek apakah user memiliki nomor telepon dan ingin menerima notifikasi
        if (!$user->phone || !$user->wa_notifications) {
            return;
        }
        
        // Siapkan data untuk notifikasi
        $data = [
            'name' => $user->name,
            'date' => Carbon::parse($attendance->date)->format('d/m/Y'),
            'time' => Carbon::parse($attendance->check_in_time)->format('H:i'),
            'location' => $locationName,
            'status' => ucfirst($status)
        ];
        
        // Kirim notifikasi
        $fonnteService = new FonnteService();
        $fonnteService->sendCheckInNotification($user->phone, $data);
    }
    
    /**
     * Kirim notifikasi WhatsApp untuk absen pulang
     */
    private function sendCheckOutNotification(Attendance $attendance, string $locationName)
    {
        // Cek apakah fitur notifikasi WhatsApp diaktifkan
        if (!config('services.fonnte.enable_notifications')) {
            return;
        }
        
        $user = $attendance->user;
        
        // Cek apakah user memiliki nomor telepon dan ingin menerima notifikasi
        if (!$user->phone || !$user->wa_notifications) {
            return;
        }
        
        // Siapkan data untuk notifikasi
        $data = [
            'name' => $user->name,
            'date' => Carbon::parse($attendance->date)->format('d/m/Y'),
            'time' => Carbon::parse($attendance->check_out_time)->format('H:i'),
            'location' => $locationName,
            'work_duration' => $attendance->formatted_duration
        ];
        
        // Kirim notifikasi
        $fonnteService = new FonnteService();
        $fonnteService->sendCheckOutNotification($user->phone, $data);
    }
}