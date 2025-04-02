<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use App\Services\FaceRecognition\FaceApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FaceAttendanceController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    protected $faceApiService;
    
    /**
     * Constructor
     */
    public function __construct(FaceApiService $faceApiService)
    {
        $this->faceApiService = $faceApiService;
    }
    
    /**
     * Proses absensi dengan wajah
     */
    public function processAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'face_image' => 'required|string',
            'username' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device_info' => 'nullable|string',
            'ip_address' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ]);
        }
        
        try {
            // Simpan IP address dan device info untuk audit
            $ipAddress = $request->ip_address ?? $request->ip();
            $deviceInfo = $request->device_info ? json_decode($request->device_info, true) : [];
            
            // Tambahkan log untuk membantu deteksi fake GPS
            Log::info('Attendance attempt', [
                'username' => $request->username,
                'ip' => $ipAddress,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'device_info' => $deviceInfo
            ]);
            
            // Cek apakah IP konsisten dengan lokasi geografis yang dilaporkan
            $geoIpValid = $this->validateGeoIpConsistency($ipAddress, $request->latitude, $request->longitude);
            
            if (!$geoIpValid) {
                Log::warning('Possible Fake GPS detected', [
                    'username' => $request->username,
                    'ip' => $ipAddress,
                    'reported_location' => [$request->latitude, $request->longitude]
                ]);
                
                // Opsi 1: Tolak absensi
                // return response()->json([
                //     'success' => false,
                //     'message' => 'Terdeteksi anomali lokasi. Mohon gunakan lokasi asli Anda.'
                // ]);
                
                // Opsi 2: Lanjutkan tapi tandai sebagai mencurigakan (dipilih)
            }
            
            // Cari user berdasarkan username
            $user = User::where('username', $request->username)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ]);
            }
            
            // Cek perubahan lokasi yang tidak wajar
            $unrealisticMovement = $this->checkUnrealisticLocationChange($user, $request->latitude, $request->longitude);
            
            if ($unrealisticMovement) {
                Log::warning('Unrealistic location change detected', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'location' => [$request->latitude, $request->longitude]
                ]);
                
                // Opsi 1: Tolak absensi
                // return response()->json([
                //     'success' => false,
                //     'message' => 'Perubahan lokasi tidak wajar terdeteksi. Mohon hubungi admin.'
                // ]);
                
                // Opsi 2: Lanjutkan tapi tandai sebagai mencurigakan (dipilih)
            }
            
            // Cek apakah user memiliki foto wajah
            if (!$user->face_photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna belum mendaftarkan wajah. Silakan login dan daftarkan wajah terlebih dahulu.'
                ]);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area yang diizinkan untuk absensi.'
                ]);
            }
            
            // Proses foto
            $image = $request->face_image;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            
            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format foto tidak valid.'
                ]);
            }
            
            // Generate unique filename
            $filename = 'attendance_' . time() . '_' . $user->id . '.jpg';
            $photoPath = 'attendance_photos/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($photoPath, $imageData);
            
            // Simpan absensi
            $today = Carbon::now($this->timezone)->toDateString();
            $now = Carbon::now($this->timezone);
            
            // Siapkan data suspicion flags
            $suspicionFlags = [];
            if (!$geoIpValid) {
                $suspicionFlags['geo_ip_mismatch'] = true;
            }
            if ($unrealisticMovement) {
                $suspicionFlags['unrealistic_movement'] = true;
            }
            
            // Cek apakah sudah ada absensi hari ini
            $todayAttendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();
            
            if (!$todayAttendance) {
                // Absen masuk
                $todayAttendance = new Attendance();
                $todayAttendance->user_id = $user->id;
                $todayAttendance->date = $today;
                $todayAttendance->check_in_time = $now->format('H:i:s');
                $todayAttendance->check_in_photo = $photoPath;
                $todayAttendance->check_in_latitude = $latitude;
                $todayAttendance->check_in_longitude = $longitude;
                $todayAttendance->status = 'hadir';
                $todayAttendance->device_info = $deviceInfo;
                
                // Tandai jika ada kecurigaan
                if (!empty($suspicionFlags)) {
                    $todayAttendance->suspicion_flags = $suspicionFlags;
                    
                    // Log untuk admin
                    Log::warning('Suspicious attendance recorded', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'flags' => $suspicionFlags
                    ]);
                }
                
                $todayAttendance->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Absen masuk berhasil pada ' . $now->format('H:i') . ' WIB',
                    'data' => [
                        'user' => $user->name,
                        'time' => $now->format('H:i:s'),
                        'location' => $locationName,
                        'type' => 'check_in'
                    ]
                ]);
            } elseif ($todayAttendance->check_in_time && !$todayAttendance->check_out_time) {
                // Absen pulang
                $todayAttendance->check_out_time = $now->format('H:i:s');
                $todayAttendance->check_out_photo = $photoPath;
                $todayAttendance->check_out_latitude = $latitude;
                $todayAttendance->check_out_longitude = $longitude;
                
                // Update device info jika ada perubahan perangkat
                if (!empty($deviceInfo)) {
                    // Tambahkan info perangkat saat checkout
                    $existingDeviceInfo = $todayAttendance->device_info ?? [];
                    $deviceInfo['checkout_device'] = $deviceInfo;
                    $todayAttendance->device_info = $deviceInfo;
                }
                
                // Tandai jika ada kecurigaan saat checkout
                if (!empty($suspicionFlags)) {
                    $existingSuspicions = $todayAttendance->suspicion_flags ?? [];
                    $suspicionFlags['checkout_suspicious'] = true;
                    $todayAttendance->suspicion_flags = array_merge($existingSuspicions, $suspicionFlags);
                    
                    // Log untuk admin
                    Log::warning('Suspicious checkout recorded', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'flags' => $suspicionFlags
                    ]);
                }
                
                $todayAttendance->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Absen pulang berhasil pada ' . $now->format('H:i') . ' WIB',
                    'data' => [
                        'user' => $user->name,
                        'time' => $now->format('H:i:s'),
                        'location' => $locationName,
                        'type' => 'check_out'
                    ]
                ]);
            } else {
                // Sudah absen lengkap
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi masuk dan pulang hari ini.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Memeriksa konsistensi antara IP dan lokasi yang dilaporkan
     * 
     * @param string $ip
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    private function validateGeoIpConsistency($ip, $latitude, $longitude)
    {
        // Implementasi sederhana - akan selalu valid
        // Untuk implementasi lengkap, gunakan IP geolocation service seperti ipinfo.io atau maxmind
        
        // Periksa apakah IP adalah private network
        if ($this->isPrivateNetwork($ip)) {
            // Untuk IP lokal, kita bisa lebih permisif
            return true;
        }
        
        // Di lingkungan produksi, tambahkan validasi IP-Geolocation di sini
        
        return true;
    }
    
    /**
     * Cek apakah alamat IP adalah jaringan pribadi
     * 
     * @param string $ip
     * @return bool
     */
    private function isPrivateNetwork($ip)
    {
        return filter_var(
            $ip, 
            FILTER_VALIDATE_IP, 
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
    
    /**
     * Memeriksa apakah perubahan lokasi tidak wajar
     * 
     * @param User $user
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    private function checkUnrealisticLocationChange($user, $latitude, $longitude)
    {
        $lastAttendance = $user->attendances()->latest()->first();
        if (!$lastAttendance) return false;
        
        // Jika tidak ada lokasi sebelumnya, kita anggap valid
        if (!$lastAttendance->check_in_latitude || !$lastAttendance->check_in_longitude) {
            return false;
        }
        
        $lastLat = $lastAttendance->check_in_latitude;
        $lastLng = $lastAttendance->check_in_longitude;
        
        // Hitung jarak dalam kilometer
        $distance = $this->calculateDistance($lastLat, $lastLng, $latitude, $longitude);
        
        // Hitung perbedaan waktu dalam jam
        $timeDiff = Carbon::now()->diffInHours($lastAttendance->created_at);
        
        // Jika baru saja absen (< 1 jam), abaikan validasi ini
        if ($timeDiff < 1) return false;
        
        // Kecepatan dalam km/jam
        $speed = $distance / ($timeDiff ?: 1);
        
        // Jika kecepatan > 500 km/jam, kemungkinan tidak realistis
        // Kecepatan pesawat komersial rata-rata ~850 km/jam
        return $speed > 500;
    }
    
    /**
     * Menghitung jarak antara dua koordinat menggunakan rumus Haversine
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Jarak dalam kilometer
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer
        
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);
        
        $latDiff = $lat2Rad - $lat1Rad;
        $lngDiff = $lng2Rad - $lng1Rad;
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($lngDiff / 2) * sin($lngDiff / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}