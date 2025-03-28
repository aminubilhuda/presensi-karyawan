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
}