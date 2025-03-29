<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    /**
     * Menampilkan dashboard analitik
     */
    public function analytics()
    {
        // Jumlah pengguna berdasarkan role
        $usersByRole = User::select('roles.name as role', DB::raw('count(*) as total'))
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->groupBy('users.role_id', 'roles.name')
            ->get();
        
        // Data absensi 7 hari terakhir
        $lastSevenDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $lastSevenDays->push([
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'total' => 0,
            ]);
        }
        
        $attendances = Attendance::where('date', '>=', Carbon::now()->subDays(6)->format('Y-m-d'))
            ->select('date', DB::raw('count(*) as total'))
            ->groupBy('date')
            ->get();
        
        $attendances->each(function ($item) use ($lastSevenDays) {
            $index = $lastSevenDays->search(function ($day) use ($item) {
                return $day['date'] === $item->date;
            });
            
            if ($index !== false) {
                $lastSevenDays[$index]['total'] = $item->total;
            }
        });
        
        // Status absensi untuk hari ini
        $today = Carbon::now()->format('Y-m-d');
        $attendanceStatus = [
            'hadir' => Attendance::where('date', $today)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::where('date', $today)->where('status', 'terlambat')->count(),
            'izin' => Attendance::where('date', $today)->where('status', 'izin')->count(),
            'sakit' => Attendance::where('date', $today)->where('status', 'sakit')->count(),
        ];
        
        // Stats untuk month-to-date
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $mtdStats = [
            'total_absensi' => Attendance::where('date', '>=', $startOfMonth)->count(),
            'total_terlambat' => Attendance::where('date', '>=', $startOfMonth)->where('status', 'terlambat')->count(),
            'total_hadir' => Attendance::where('date', '>=', $startOfMonth)->where('status', 'hadir')->count(),
            'total_tidak_hadir' => Attendance::where('date', '>=', $startOfMonth)->whereIn('status', ['izin', 'sakit'])->count(),
        ];
        
        return view('admin.analytics', compact(
            'usersByRole', 
            'lastSevenDays', 
            'attendanceStatus', 
            'mtdStats'
        ));
    }
} 