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
        // Implementasi export akan ditambahkan nanti
        return back()->with('info', 'Fitur ekspor data belum tersedia.');
    }
} 