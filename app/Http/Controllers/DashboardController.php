<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Document;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    
    /**
     * Menampilkan halaman dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now($this->timezone)->toDateString();
        
        // Untuk admin
        if ($user->isAdmin()) {
            // Caching untuk data dashboard admin
            $dashboardData = Cache::remember('admin_dashboard_' . $today, 30, function() use ($today) {
                return [
                    'totalUsers' => User::count(),
                    'todayAttendances' => Attendance::whereDate('date', $today)->count(),
                    'presentToday' => Attendance::whereDate('date', $today)
                        ->whereIn('status', ['hadir', 'terlambat'])
                        ->count(),
                    'absentToday' => Attendance::whereDate('date', $today)
                        ->whereIn('status', ['izin', 'sakit', 'alfa'])
                        ->count()
                ];
            });
            
            return view('dashboard', $dashboardData);
        }
        
        // Untuk guru dan staf
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        // Get attendances for current month more efficiently
        $currentMonth = Carbon::now($this->timezone)->month;
        $currentYear = Carbon::now($this->timezone)->year;
        
        $monthlyStats = Cache::remember(
            'user_monthly_stats_' . $user->id . '_' . $currentMonth . '_' . $currentYear, 
            60 * 12, // cache for 12 hours
            function() use ($user, $currentMonth, $currentYear) {
                return DB::table('attendances')
                    ->select(
                        DB::raw('COUNT(CASE WHEN status IN ("hadir", "terlambat") THEN 1 ELSE NULL END) as present'),
                        DB::raw('COUNT(CASE WHEN status IN ("izin", "sakit", "alfa") THEN 1 ELSE NULL END) as absent'),
                        DB::raw('COUNT(CASE WHEN status = "terlambat" THEN 1 ELSE NULL END) as late')
                    )
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->first();
            }
        );
        
        return view('dashboard', [
            'todayAttendance' => $todayAttendance,
            'present' => $monthlyStats->present ?? 0,
            'absent' => $monthlyStats->absent ?? 0,
            'late' => $monthlyStats->late ?? 0
        ]);
    }
    
    /**
     * Menampilkan halaman analitik
     */
    public function analytics()
    {
        $this->authorize('viewAnalytics', User::class);
        
        // Menggunakan cache untuk data yang jarang berubah
        $userStats = Cache::remember('analytics_user_stats', 60 * 6, function() {
            // Statistik pengguna
            return [
                'total' => User::count(),
                'active' => User::count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'by_role' => DB::table('users')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->select('roles.name as role', DB::raw('count(*) as total'))
                    ->groupBy('roles.name')
                    ->pluck('total', 'role')
                    ->toArray()
            ];
        });
        
        // Data absensi harus selalu fresh
        $today = Carbon::now($this->timezone)->toDateString();
        $currentMonth = Carbon::now($this->timezone)->month;
        $currentYear = Carbon::now($this->timezone)->year;
        
        // Statistik absensi
        $attendanceStats = [
            'today' => $this->getAttendanceStatsByDate($today),
            'this_month' => $this->getAttendanceStatsByMonth($currentMonth, $currentYear),
            'daily_trend' => $this->getDailyAttendanceTrend()
        ];
        
        // Statistik dokumen - dapat di-cache
        $documentStats = Cache::remember('analytics_document_stats', 60 * 6, function() {
            return [
                'total' => Document::count(),
                'this_month' => Document::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'by_type' => Document::select('file_type', DB::raw('count(*) as total'))
                    ->groupBy('file_type')
                    ->pluck('total', 'file_type')
                    ->toArray()
            ];
        });
        
        // Statistik ticket - selalu fresh
        $ticketStats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
            'by_priority' => SupportTicket::select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray()
        ];
        
        return view('dashboard.analytics', compact(
            'userStats', 
            'attendanceStats', 
            'documentStats', 
            'ticketStats'
        ));
    }
    
    /**
     * Mendapatkan statistik kehadiran berdasarkan tanggal
     */
    private function getAttendanceStatsByDate($date)
    {
        return [
            'total' => Attendance::whereDate('date', $date)->count(),
            'present' => Attendance::whereDate('date', $date)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
            'absent' => Attendance::whereDate('date', $date)
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->count(),
            'late' => Attendance::whereDate('date', $date)
                ->where('status', 'terlambat')
                ->count(),
        ];
    }
    
    /**
     * Mendapatkan statistik kehadiran berdasarkan bulan dan tahun
     */
    private function getAttendanceStatsByMonth($month, $year)
    {
        return [
            'total' => Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count(),
            'present' => Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
            'absent' => Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->count(),
            'late' => Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'terlambat')
                ->count(),
        ];
    }
    
    /**
     * Mendapatkan data tren absensi harian
     */
    private function getDailyAttendanceTrend()
    {
        $startDate = Carbon::now($this->timezone)->subDays(30);
        $endDate = Carbon::now($this->timezone);
        
        $results = DB::table('attendances')
            ->select(
                DB::raw('DATE(date) as attendance_date'),
                DB::raw("SUM(CASE WHEN status IN ('hadir', 'terlambat') THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN status IN ('izin', 'sakit', 'alfa') THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as late_count")
            )
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
        
        return $results;
    }
}
