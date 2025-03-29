<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceMonitorController extends Controller
{
    /**
     * Menampilkan halaman monitoring absensi real-time
     */
    public function index()
    {
        // Tanggal hari ini
        $today = Carbon::today()->toDateString();
        
        // Ambil semua pengguna non-admin
        $users = User::whereHas('role', function($query) {
            $query->where('name', '!=', 'Admin');
        })->with('role')->get();
        
        // Ambil semua lokasi absensi yang aktif
        $locations = AttendanceLocation::where('is_active', true)->get();
        
        // Ambil pengaturan jam kerja
        $checkInTime = Setting::getValue('check_in_time', '08:00');
        $lateThreshold = Setting::getValue('late_threshold', 15);
        
        // Ambil data absensi hari ini
        $attendancesToday = Attendance::with('user')
            ->whereDate('check_in_time', $today)
            ->get()
            ->map(function($attendance) use ($checkInTime) {
                // Hitung status keterlambatan
                $checkInTimeCarbon = Carbon::parse($attendance->check_in_time);
                $expectedCheckIn = Carbon::parse($checkInTime);
                
                // Jika jam masuk lebih dari jam yang diharapkan
                if ($checkInTimeCarbon->gt($expectedCheckIn)) {
                    $attendance->status = 'terlambat';
                } else {
                    $attendance->status = 'tepat_waktu';
                }
                
                return $attendance;
            });
        
        // Data untuk peta
        $mapData = [
            'locations' => $locations->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'radius' => $location->radius,
                ];
            }),
            'attendances' => $attendancesToday->map(function($attendance) {
                return [
                    'user_name' => $attendance->user->name,
                    'check_in_time' => date('H:i', strtotime($attendance->check_in_time)),
                    'check_in_latitude' => $attendance->check_in_latitude,
                    'check_in_longitude' => $attendance->check_in_longitude,
                    'check_out_time' => $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : null,
                    'check_out_latitude' => $attendance->check_out_latitude,
                    'check_out_longitude' => $attendance->check_out_longitude,
                    'status' => $attendance->status
                ];
            })
        ];
        
        // Ringkasan statistik
        $stats = [
            'total_users' => $users->count(),
            'checked_in' => $attendancesToday->count(),
            'checked_out' => $attendancesToday->where('check_out_time', '!=', null)->count(),
            'not_present' => $users->count() - $attendancesToday->count(),
            'late' => $attendancesToday->where('status', 'terlambat')->count(),
            'on_time' => $attendancesToday->where('status', 'tepat_waktu')->count()
        ];
        
        return view('admin.attendance.monitor', compact('users', 'attendancesToday', 'mapData', 'stats', 'today'));
    }
    
    /**
     * Menampilkan laporan absensi untuk satu pengguna tertentu
     */
    public function userAttendances(Request $request, User $user)
    {
        // Ambil filter bulan dan tahun, default bulan dan tahun ini
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Ambil data pengguna dengan peran
        $user->load('role');
        
        // Ambil absensi pengguna berdasarkan bulan dan tahun
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(15);
            
        // Statistik untuk bulan yang dipilih
        $stats = [
            'total' => $attendances->total(),
            'present' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
            'absent' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->count(),
            'late' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'terlambat')
                ->count(),
        ];
        
        // Data untuk chart/grafik
        $chartData = DB::table('attendances')
            ->selectRaw('DATE(date) as date, status, COUNT(*) as count')
            ->where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();
        
        // Hitung rata-rata durasi kerja (dalam menit)
        $avgDuration = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereNotNull('check_in_time')
            ->whereNotNull('check_out_time')
            ->avg(DB::raw('
                TIMESTAMPDIFF(
                    MINUTE, 
                    CONCAT(date, " ", check_in_time), 
                    CONCAT(date, " ", check_out_time)
                )
            '));
        
        // Format rata-rata durasi ke format jam:menit
        if ($avgDuration) {
            $hours = floor($avgDuration / 60);
            $minutes = $avgDuration % 60;
            $stats['avg_duration'] = sprintf('%02d:%02d', $hours, $minutes);
        } else {
            $stats['avg_duration'] = '00:00';
        }
        
        return view('admin.attendance.user_attendances', compact(
            'user', 
            'attendances', 
            'month', 
            'year', 
            'stats',
            'chartData'
        ));
    }
}