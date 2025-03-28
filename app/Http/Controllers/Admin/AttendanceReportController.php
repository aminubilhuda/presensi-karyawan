<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    /**
     * Menampilkan laporan absensi
     */
    public function index(Request $request)
    {
        $users = User::whereHas('role', function($query) {
            $query->where('name', '!=', 'Admin');
        })->get();
        
        $query = Attendance::with(['user', 'user.role'])
            ->select('*', DB::raw('DATE(check_in_time) as attendance_date'));
        
        // Filter berdasarkan user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        $attendances = $query->orderBy('check_in_time', 'desc')
            ->paginate(15)
            ->appends($request->all());
        
        return view('admin.attendance.report', compact('attendances', 'users'));
    }
    
    /**
     * Mengekspor laporan absensi ke CSV untuk Excel
     */
    public function export(Request $request)
    {
        $query = Attendance::with(['user', 'user.role'])
            ->select('*', DB::raw('DATE(check_in_time) as attendance_date'));
        
        // Filter berdasarkan user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        $attendances = $query->orderBy('check_in_time', 'desc')->get();
        
        // File name
        $fileName = 'laporan_absensi_' . date('Y-m-d') . '.csv';
        
        // Headers
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        // Buat callback untuk membuat file CSV
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk mendukung karakter Unicode di Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom
            $header = [
                'No', 
                'Tanggal', 
                'Nama',
                'Peran',
                'Jam Masuk',
                'Jam Pulang',
                'Lokasi Masuk (Lat)',
                'Lokasi Masuk (Long)',
                'Lokasi Pulang (Lat)',
                'Lokasi Pulang (Long)',
                'Status'
            ];
            
            // Gunakan semicolon sebagai delimiter untuk Excel
            fputcsv($file, $header, ';');
            
            // Data
            $no = 1;
            foreach ($attendances as $attendance) {
                $row = [
                    $no++,
                    date('d/m/Y', strtotime($attendance->check_in_time)),
                    $attendance->user->name,
                    $attendance->user->role->name,
                    date('H:i', strtotime($attendance->check_in_time)),
                    $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : '-',
                    $attendance->check_in_latitude,
                    $attendance->check_in_longitude,
                    $attendance->check_out_time ? $attendance->check_out_latitude : '-',
                    $attendance->check_out_time ? $attendance->check_out_longitude : '-',
                    $attendance->check_out_time ? 'Lengkap' : 'Belum Absen Pulang'
                ];
                
                // Gunakan semicolon sebagai delimiter untuk Excel
                fputcsv($file, $row, ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}