<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\QrToken;
use App\Models\User;
use App\Models\AttendanceLocation;
use App\Models\Workday;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    
    /**
     * Menampilkan form untuk generate QR code absensi
     */
    public function generateQrForm()
    {
        // Generate token baru
        $user = Auth::user();
        $qrToken = $this->createToken($user);
        
        // Generate URL untuk QR Code (URL ini akan diakses oleh scanner)
        $qrUrl = route('qr.scan', ['token' => $qrToken->token]);
        
        // Generate QR Code
        $qrCode = QrCode::size(250)
                ->backgroundColor(255, 255, 255)
                ->color(0, 0, 0)
                ->margin(1)
                ->generate($qrUrl);
        
        return view('attendance.qr-generate', [
            'qrCode' => $qrCode,
            'qrToken' => $qrToken,
            'qrUrl' => $qrUrl
        ]);
    }
    
    /**
     * Membuat token baru untuk QR Code
     */
    private function createToken(User $user)
    {
        // Hapus semua token lama milik user ini yang masih aktif
        QrToken::where('user_id', $user->id)
               ->where('expires_at', '>', now())
               ->where('is_used', false)
               ->update(['is_used' => true]);
        
        // Buat token baru yang berlaku 5 menit
        return QrToken::create([
            'user_id' => $user->id,
            'token' => Str::random(40),
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);
    }
    
    /**
     * Menangani scanning QR code dan menampilkan form absensi
     */
    public function scanQrCode($token)
    {
        // Cari token yang aktif
        $qrToken = QrToken::where('token', $token)
                          ->where('expires_at', '>', now())
                          ->where('is_used', false)
                          ->first();
        
        if (!$qrToken) {
            return view('attendance.qr-scan', ['error' => 'QR Code tidak valid atau sudah kadaluarsa']);
        }
        
        // Ambil user pemilik token
        $user = User::findOrFail($qrToken->user_id);
        
        // Sementara nonaktifkan pengecekan hari kerja (PENTING: Ini adalah solusi sementara)
        $ignoreWorkdayCheck = true;
        
        // Cek apakah hari ini adalah hari kerja
        $todayName = Carbon::now($this->timezone)->locale('id')->isoFormat('dddd');
        if (!$ignoreWorkdayCheck && !Workday::isWorkingDay($todayName)) {
            return view('attendance.qr-scan', ['error' => 'Hari ini bukan hari kerja.']);
        }
        
        // Cek apakah hari ini adalah hari libur
        $today = Carbon::today();
        $isHoliday = Holiday::whereDate('date', $today)->exists();
        if (!$ignoreWorkdayCheck && $isHoliday) {
            return view('attendance.qr-scan', ['error' => 'Hari ini adalah hari libur.']);
        }
        
        // Cek status absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
                               ->whereDate('date', $today)
                               ->first();
        
        $attendanceStatus = null;
        
        if (!$attendance) {
            // Belum ada absensi hari ini
            $attendanceStatus = 'checkin';
        } elseif ($attendance && $attendance->check_in && !$attendance->check_out) {
            // Sudah check in, belum check out
            $attendanceStatus = 'checkout';
        } elseif ($attendance && $attendance->check_in && $attendance->check_out) {
            // Sudah check in dan check out
            $attendanceStatus = 'complete';
        }
        
        return view('attendance.qr-scan', [
            'user' => $user,
            'token' => $token,
            'attendance' => $attendance,
            'attendanceStatus' => $attendanceStatus
        ]);
    }
    
    /**
     * Memproses absensi dari QR code
     */
    public function processQrAttendance(Request $request)
    {
        // Validasi request
        $request->validate([
            'token' => 'required|string',
            'attendance_type' => 'required|in:checkin,checkout',
        ]);
        
        $token = $request->input('token');
        $attendanceType = $request->input('attendance_type');
        
        // Cek token
        $qrToken = QrToken::where('token', $token)
                          ->where('expires_at', '>', now())
                          ->where('is_used', false)
                          ->first();
        
        if (!$qrToken) {
            return back()->with('error', 'QR Code tidak valid atau sudah kadaluarsa');
        }
        
        $user = User::findOrFail($qrToken->user_id);
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Sementara nonaktifkan pengecekan hari kerja (PENTING: Ini adalah solusi sementara)
        $ignoreWorkdayCheck = true;
        
        // Cek apakah hari ini adalah hari kerja
        $todayName = Carbon::now($this->timezone)->locale('id')->isoFormat('dddd');
        if (!$ignoreWorkdayCheck && !Workday::isWorkingDay($todayName)) {
            return back()->with('error', 'Hari ini bukan hari kerja.');
        }
        
        // Cek apakah hari ini adalah hari libur
        $isHoliday = Holiday::whereDate('date', $today)->exists();
        if (!$ignoreWorkdayCheck && $isHoliday) {
            return back()->with('error', 'Hari ini adalah hari libur.');
        }
        
        // Cari atau buat data absensi hari ini
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => 'hadir']
        );
        
        // Proses berdasarkan jenis absensi
        if ($attendanceType === 'checkin') {
            // Check in
            if ($attendance->check_in) {
                return back()->with('error', 'Anda sudah melakukan absen masuk hari ini');
            }
            
            $attendance->check_in = $now;
            $attendance->check_in_location = 'Via QR Code';
            $attendance->save();
            
            // Tandai token sudah digunakan
            $qrToken->is_used = true;
            $qrToken->save();
            
            return redirect()->route('qr.scan', ['token' => $token])
                            ->with('success', 'Absen masuk berhasil pada ' . $now->format('H:i:s'));
                            
        } elseif ($attendanceType === 'checkout') {
            // Check out
            if (!$attendance->check_in) {
                return back()->with('error', 'Anda belum melakukan absen masuk hari ini');
            }
            
            if ($attendance->check_out) {
                return back()->with('error', 'Anda sudah melakukan absen pulang hari ini');
            }
            
            $attendance->check_out = $now;
            $attendance->check_out_location = 'Via QR Code';
            $attendance->save();
            
            // Tandai token sudah digunakan
            $qrToken->is_used = true;
            $qrToken->save();
            
            return redirect()->route('qr.scan', ['token' => $token])
                            ->with('success', 'Absen pulang berhasil pada ' . $now->format('H:i:s'));
        }
        
        return back()->with('error', 'Tipe absensi tidak valid');
    }
} 