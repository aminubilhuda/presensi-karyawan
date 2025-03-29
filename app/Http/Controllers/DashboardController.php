<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Document;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Untuk admin
        if ($user->isAdmin()) {
            $totalUsers = User::count();
            $todayAttendances = Attendance::where('date', now()->toDateString())->count();
            $presentToday = Attendance::where('date', now()->toDateString())
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            $absentToday = Attendance::where('date', now()->toDateString())
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->count();
            
            return view('dashboard', compact('totalUsers', 'todayAttendances', 'presentToday', 'absentToday'));
        }
        
        // Untuk guru dan staf
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
        
        $thisMonthAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        $present = $thisMonthAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $absent = $thisMonthAttendances->whereIn('status', ['izin', 'sakit', 'alfa'])->count();
        $late = $thisMonthAttendances->where('status', 'terlambat')->count();
        
        return view('dashboard', compact('todayAttendance', 'present', 'absent', 'late'));
    }
    
    /**
     * Menampilkan halaman analitik
     */
    public function analytics()
    {
        $this->authorize('viewAnalytics', User::class);
        
        // Statistik pengguna
        $userStats = [
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
        
        // Statistik absensi
        $attendanceStats = [
            'today' => [
                'total' => Attendance::whereDate('date', now()->toDateString())->count(),
                'present' => Attendance::whereDate('date', now()->toDateString())
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->count(),
                'absent' => Attendance::whereDate('date', now()->toDateString())
                    ->whereIn('status', ['izin', 'sakit', 'alfa'])
                    ->count(),
                'late' => Attendance::whereDate('date', now()->toDateString())
                    ->where('status', 'terlambat')
                    ->count(),
            ],
            'this_month' => [
                'total' => Attendance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
                'present' => Attendance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->count(),
                'absent' => Attendance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->whereIn('status', ['izin', 'sakit', 'alfa'])
                    ->count(),
                'late' => Attendance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->where('status', 'terlambat')
                    ->count(),
            ],
            'daily_trend' => $this->getDailyAttendanceTrend()
        ];
        
        // Statistik dokumen
        $documentStats = [
            'total' => Document::count(),
            'this_month' => Document::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'by_type' => Document::select('file_type', DB::raw('count(*) as total'))
                ->groupBy('file_type')
                ->pluck('total', 'file_type')
                ->toArray()
        ];
        
        // Statistik support ticket
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
     * Mendapatkan data tren absensi harian
     */
    private function getDailyAttendanceTrend()
    {
        $startDate = now()->subDays(30);
        $endDate = now();
        
        $results = DB::table('attendances')
            ->select(
                DB::raw('DATE(date) as attendance_date'),
                DB::raw('SUM(CASE WHEN status IN ("hadir", "terlambat") THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN status IN ("izin", "sakit", "alfa") THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as late_count')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
        
        return $results;
    }
}
