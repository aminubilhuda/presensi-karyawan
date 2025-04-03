<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeaveManagementController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:admin.leave.view');
    }
    
    /**
     * Menampilkan daftar semua permohonan izin
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'approver'])
            ->orderBy('created_at', 'desc');
        
        // Filter berdasarkan status
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan jenis izin
        if ($request->has('type') && in_array($request->type, ['izin', 'sakit'])) {
            $query->where('type', $request->type);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        // Filter berdasarkan user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        $leaveRequests = $query->paginate(10);
        $users = User::where('status', 'active')->orderBy('name')->get();
        
        return view('admin.leave.index', compact('leaveRequests', 'users'));
    }
    
    /**
     * Menampilkan detail permohonan izin
     */
    public function show(LeaveRequest $leave)
    {
        return view('admin.leave.show', compact('leave'));
    }
    
    /**
     * Menyetujui permohonan izin
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Hanya bisa menyetujui yang masih pending
        if (!$leave->isPending()) {
            return redirect()->route('admin.leave.index')->with('error', 'Permohonan izin ini sudah diproses sebelumnya.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update status permohonan izin
            $leave->status = 'approved';
            $leave->admin_notes = $request->admin_notes;
            $leave->approved_by = Auth::id();
            $leave->approved_at = Carbon::now();
            $leave->save();
            
            // Buat absensi izin untuk setiap hari dalam rentang tanggal
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Cek apakah sudah ada absensi di tanggal tersebut
                $existingAttendance = Attendance::where('user_id', $leave->user_id)
                    ->whereDate('date', $currentDate->toDateString())
                    ->first();
                
                if (!$existingAttendance) {
                    // Buat absensi baru dengan status izin/sakit
                    $attendance = new Attendance();
                    $attendance->user_id = $leave->user_id;
                    $attendance->date = $currentDate->toDateString();
                    $attendance->status = $leave->type; // izin atau sakit
                    $attendance->notes = "Permohonan izin #" . $leave->id . ": " . $leave->reason;
                    $attendance->save();
                }
                
                $currentDate->addDay();
            }
            
            DB::commit();
            
            return redirect()->route('admin.leave.index')->with('success', 'Permohonan izin berhasil disetujui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Menolak permohonan izin
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Hanya bisa menolak yang masih pending
        if (!$leave->isPending()) {
            return redirect()->route('admin.leave.index')->with('error', 'Permohonan izin ini sudah diproses sebelumnya.');
        }
        
        try {
            $leave->status = 'rejected';
            $leave->admin_notes = $request->admin_notes;
            $leave->approved_by = Auth::id();
            $leave->approved_at = Carbon::now();
            $leave->save();
            
            return redirect()->route('admin.leave.index')->with('success', 'Permohonan izin berhasil ditolak.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Ekspor data permohonan izin ke Excel
     */
    public function export(Request $request)
    {
        $query = LeaveRequest::with(['user', 'user.role', 'approver'])
            ->orderBy('created_at', 'desc');
        
        // Filter berdasarkan status
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan jenis izin
        if ($request->has('type') && in_array($request->type, ['izin', 'sakit'])) {
            $query->where('type', $request->type);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        // Filter berdasarkan user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        $leaveRequests = $query->get();
        
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul worksheet
        $sheet->setTitle('Permohonan Izin');
        
        // Header kolom
        $headers = [
            'No', 
            'Tanggal Pengajuan', 
            'Nama Karyawan',
            'Peran',
            'Jenis Izin',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (hari)',
            'Alasan',
            'Status',
            'Disetujui/Ditolak Oleh',
            'Catatan Admin'
        ];
        
        // Tambahkan header
        $sheet->fromArray([$headers], null, 'A1');
        
        // Format header (bold dan background color)
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
        
        // Data array untuk rows
        $leaveData = [];
        $no = 1;
        
        foreach ($leaveRequests as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $duration = $startDate->diffInDays($endDate) + 1;
            
            $status = [
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak'
            ][$leave->status] ?? $leave->status;
            
            $leaveData[] = [
                $no++,
                Carbon::parse($leave->created_at)->format('d/m/Y H:i'),
                $leave->user->name,
                $leave->user->role->name,
                ucfirst($leave->type), // 'izin' atau 'sakit'
                Carbon::parse($leave->start_date)->format('d/m/Y'),
                Carbon::parse($leave->end_date)->format('d/m/Y'),
                $duration,
                $leave->reason,
                $status,
                $leave->approver ? $leave->approver->name : '-',
                $leave->admin_notes ?? '-'
            ];
        }
        
        // Tambahkan data ke sheet
        if (!empty($leaveData)) {
            $sheet->fromArray($leaveData, null, 'A2');
        }
        
        // Auto-size column dimensions
        foreach(range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Membuat file Excel
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'leave_requests_');
        $writer->save($tempFile);
        
        // File name
        $fileName = 'permohonan_izin_' . date('Y-m-d') . '.xlsx';
        
        // Mengunduh file Excel dan menghapusnya setelah diunduh
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
} 