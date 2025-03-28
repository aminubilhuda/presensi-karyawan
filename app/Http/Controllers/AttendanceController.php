<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Setting;
use App\Models\Workday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Menampilkan form absen masuk
     */
    public function checkInForm()
    {
        // Cek apakah sudah absen hari ini
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->first();
            
        if ($todayAttendance && $todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }
        
        // Cek apakah hari ini adalah hari kerja
        $today = now()->locale('id')->isoFormat('dddd'); // Mendapatkan nama hari dalam bahasa Indonesia
        
        if (!Workday::isWorkingDay($today)) {
            return redirect()->route('dashboard')->with('error', 'Hari ini bukan hari kerja.');
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
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string',
        ]);
        
        // Cek apakah sudah absen hari ini
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->first();
            
        if ($todayAttendance && $todayAttendance->check_in_time) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }
        
        // Cek apakah berada di lokasi yang diizinkan
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $inLocation = false;
        
        $locations = AttendanceLocation::getActiveLocations();
        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                $inLocation = true;
                break;
            }
        }
        
        if (!$inLocation) {
            return back()->with('error', 'Anda berada di luar area yang diizinkan untuk absensi.');
        }
        
        // Simpan foto dari base64
        $photoPath = null;
        if ($request->has('photo')) {
            $image = $request->photo;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            
            // Generate unique filename
            $filename = 'attendance_' . time() . '.jpg';
            $photoPath = 'attendance_photos/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($photoPath, $imageData);
        }
        
        // Mendapatkan jam masuk dari pengaturan
        $checkInTime = Setting::getValue('check_in_time', '08:00');
        $checkInTimeObj = Carbon::createFromFormat('H:i', $checkInTime);
        $now = Carbon::now();
        
        // Tentukan status
        $status = 'hadir';
        if ($now->gt($checkInTimeObj)) {
            $status = 'terlambat';
        }
        
        // Jika belum ada record untuk hari ini, buat baru
        if (!$todayAttendance) {
            $todayAttendance = new Attendance();
            $todayAttendance->user_id = Auth::id();
            $todayAttendance->date = now()->toDateString();
            $todayAttendance->status = $status;
        }
        
        // Update data absensi
        $todayAttendance->check_in_time = now()->toTimeString();
        $todayAttendance->check_in_photo = $photoPath;
        $todayAttendance->check_in_latitude = $latitude;
        $todayAttendance->check_in_longitude = $longitude;
        $todayAttendance->save();
        
        return redirect()->route('dashboard')->with('success', 'Absen masuk berhasil dicatat.');
    }
    
    /**
     * Menampilkan form absen pulang
     */
    public function checkOutForm()
    {
        // Cek apakah sudah absen masuk hari ini
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
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
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string',
        ]);
        
        // Cek apakah sudah absen masuk hari ini
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
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
        
        $locations = AttendanceLocation::getActiveLocations();
        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                $inLocation = true;
                break;
            }
        }
        
        if (!$inLocation) {
            return back()->with('error', 'Anda berada di luar area yang diizinkan untuk absensi.');
        }
        
        // Simpan foto dari base64
        $photoPath = null;
        if ($request->has('photo')) {
            $image = $request->photo;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            
            // Generate unique filename
            $filename = 'attendance_' . time() . '.jpg';
            $photoPath = 'attendance_photos/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($photoPath, $imageData);
        }
        
        // Update data absensi
        $todayAttendance->check_out_time = now()->toTimeString();
        $todayAttendance->check_out_photo = $photoPath;
        $todayAttendance->check_out_latitude = $latitude;
        $todayAttendance->check_out_longitude = $longitude;
        $todayAttendance->save();
        
        return redirect()->route('dashboard')->with('success', 'Absen pulang berhasil dicatat.');
    }
    
    /**
     * Menampilkan riwayat absensi
     */
    public function history(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(10);
            
        return view('attendance.history', compact('attendances', 'month', 'year'));
    }
}
