<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
     * Mengekspor laporan absensi ke Excel menggunakan PhpSpreadsheet
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
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul worksheet
        $sheet->setTitle('Laporan Absensi');
        
        // Header kolom
        $headers = [
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
        
        // Tambahkan header
        $sheet->fromArray([$headers], null, 'A1');
        
        // Format header (bold dan background color)
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
        
        // Data array untuk rows
        $attendanceData = [];
        $no = 1;
        
        foreach ($attendances as $attendance) {
            $attendanceData[] = [
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
        }
        
        // Tambahkan data ke sheet
        if (!empty($attendanceData)) {
            $sheet->fromArray($attendanceData, null, 'A2');
        }
        
        // Auto-size column dimensions
        foreach(range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Membuat file Excel
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'attendance_report_');
        $writer->save($tempFile);
        
        // File name
        $fileName = 'laporan_absensi_' . date('Y-m-d') . '.xlsx';
        
        // Mengunduh file Excel dan menghapusnya setelah diunduh
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}