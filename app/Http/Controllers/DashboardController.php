<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
