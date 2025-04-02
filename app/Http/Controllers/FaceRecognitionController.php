<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FaceRecognition\FaceApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaceRecognitionController extends Controller
{
    protected $faceApiService;
    
    public function __construct(FaceApiService $faceApiService)
    {
        $this->faceApiService = $faceApiService;
    }
    
    /**
     * Halaman pendaftaran wajah
     */
    public function registerFaceForm()
    {
        $user = Auth::user();
        $hasFace = !empty($user->face_photo);
        $facePhoto = $hasFace ? asset('storage/'.$user->face_photo) : null;
        
        return view('face-recognition.register', compact('hasFace', 'facePhoto'));
    }
    
    /**
     * Proses pendaftaran wajah
     */
    public function registerFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'face_image' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            \Log::error('Validasi pendaftaran wajah gagal: ' . json_encode($validator->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ]);
        }
        
        try {
            $user = Auth::user();
            \Log::info('Pendaftaran wajah: User ' . $user->id . ' (' . $user->name . ') memulai pendaftaran');
            
            // Hapus foto wajah lama jika ada
            if ($user->face_photo) {
                \Log::info('Pendaftaran wajah: Menghapus foto lama ' . $user->face_photo);
                $this->faceApiService->deleteFace($user->face_photo);
            }
            
            // Simpan foto wajah baru
            $path = $this->faceApiService->saveFace($user->id, $request->face_image);
            \Log::info('Pendaftaran wajah: Foto baru tersimpan di ' . $path);
            
            // Update data user
            $user->face_photo = $path;
            $user->save();
            \Log::info('Pendaftaran wajah: Data user berhasil diupdate');
            
            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil terdaftar',
            ]);
        } catch (\Exception $e) {
            \Log::error('Pendaftaran wajah gagal: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan wajah: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Tampilan halaman absensi dengan wajah
     */
    public function faceAttendanceForm()
    {
        // Mengambil daftar user yang memiliki wajah terdaftar
        $users = User::whereNotNull('face_photo')->select('id', 'name', 'username', 'face_photo')->get();
        
        // Mengkonversi data user ke format yang bisa digunakan oleh face-api.js
        $faceData = [];
        foreach ($users as $user) {
            $faceData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'facePhoto' => asset('storage/'.$user->face_photo)
            ];
        }
        
        // Tentukan apakah ini absen masuk atau absen pulang
        $timezone = 'Asia/Jakarta';
        $today = \Carbon\Carbon::now($timezone)->toDateString();
        $attendanceType = 'check_in'; // Default absen masuk
        $attendanceStatus = null; // Status absensi hari ini
        $attendanceMessage = null; // Pesan yang akan ditampilkan
        
        // Jika user sudah login, cek status absensinya hari ini
        if (Auth::check()) {
            $user = Auth::user();
            $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();
            
            if ($todayAttendance) {
                // Ada catatan absensi hari ini
                if ($todayAttendance->check_in_time && !$todayAttendance->check_out_time) {
                    // Jika sudah absen masuk tapi belum absen pulang
                    $attendanceType = 'check_out';
                    $attendanceStatus = 'half';
                    $checkInTime = \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i');
                    $attendanceMessage = "Anda sudah melakukan absen masuk pada pukul {$checkInTime}. Silakan lakukan absen pulang ketika selesai bekerja.";
                } elseif ($todayAttendance->check_in_time && $todayAttendance->check_out_time) {
                    // Jika sudah absen masuk dan absen pulang
                    $attendanceStatus = 'complete';
                    $checkInTime = \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i');
                    $checkOutTime = \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i');
                    $attendanceMessage = "Anda sudah melakukan absensi lengkap hari ini. Absen masuk: {$checkInTime}, Absen pulang: {$checkOutTime}.";
                }
            } else {
                // Belum ada catatan absensi hari ini
                $attendanceStatus = 'none';
                $attendanceMessage = "Anda belum melakukan absensi hari ini. Silakan lakukan absen masuk.";
            }
        }
        
        return view('face-recognition.attendance', compact(
            'faceData', 
            'attendanceType', 
            'attendanceStatus',
            'attendanceMessage'
        ));
    }
    
    /**
     * Proses absensi dengan wajah
     */
    public function processFaceAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'face_image' => 'required|string',
            'username' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            \Log::error('Face attendance validation failed', [
                'errors' => $validator->errors(),
                'username' => $request->username ?? 'not provided',
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ]);
        }
        
        // Log koordinat untuk debugging
        \Log::info('Face attendance coordinates', [
            'username' => $request->username,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);
        
        try {
            // Cari user berdasarkan username
            $user = User::where('username', $request->username)->first();
            
            if (!$user) {
                \Log::warning('Face attendance user not found', ['username' => $request->username]);
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ]);
            }
            
            // Cek apakah user memiliki foto wajah
            if (!$user->face_photo) {
                \Log::warning('Face attendance user has no face photo', ['username' => $request->username, 'user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna belum mendaftarkan wajah'
                ]);
            }
            
            // Proses verifikasi wajah dilakukan di front-end dengan face-api.js
            // Di sini kita hanya menerima hasilnya
            
            // Redirect ke controller absensi untuk proses absensi aktual
            // Sementara kita response dengan sukses
            return response()->json([
                'success' => true,
                'message' => 'Wajah terverifikasi',
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Face attendance exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'username' => $request->username,
                'coordinates' => [$request->latitude, $request->longitude]
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi wajah: ' . $e->getMessage()
            ]);
        }
    }
} 