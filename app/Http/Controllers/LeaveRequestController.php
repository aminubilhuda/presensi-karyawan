<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    protected $timezone = 'Asia/Jakarta';
    
    /**
     * Menampilkan daftar permohonan izin
     */
    public function index()
    {
        $user = Auth::user();
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('leave.index', compact('leaveRequests'));
    }
    
    /**
     * Menampilkan form untuk membuat permohonan izin baru
     */
    public function create()
    {
        return view('leave.create');
    }
    
    /**
     * Menyimpan permohonan izin baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:izin,sakit',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:2048',
        ], [
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.after_or_equal' => 'Tanggal mulai harus hari ini atau setelahnya',
            'end_date.required' => 'Tanggal selesai wajib diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai',
            'type.required' => 'Jenis izin wajib dipilih',
            'type.in' => 'Jenis izin tidak valid',
            'reason.required' => 'Alasan wajib diisi',
            'reason.max' => 'Alasan maksimal 500 karakter',
            'attachment.mimes' => 'File harus berupa jpeg, png, jpg, pdf, doc, atau docx',
            'attachment.max' => 'Ukuran file maksimal 2MB',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Simpan lampiran jika ada
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . Auth::id() . '.' . $file->getClientOriginalExtension();
                $attachmentPath = $file->storeAs('leave_attachments', $fileName, 'public');
            }
            
            // Buat permohonan izin baru
            $leaveRequest = new LeaveRequest();
            $leaveRequest->user_id = Auth::id();
            $leaveRequest->start_date = $request->start_date;
            $leaveRequest->end_date = $request->end_date;
            $leaveRequest->type = $request->type;
            $leaveRequest->reason = $request->reason;
            $leaveRequest->attachment = $attachmentPath;
            $leaveRequest->status = 'pending';
            $leaveRequest->save();
            
            DB::commit();
            
            return redirect()->route('leave.index')->with('success', 'Permohonan izin berhasil diajukan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Menampilkan detail permohonan izin
     */
    public function show(LeaveRequest $leave)
    {
        // Pastikan user hanya bisa melihat permohonan izinnya sendiri
        if ($leave->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk melihat permohonan izin ini.');
        }
        
        return view('leave.show', compact('leave'));
    }
    
    /**
     * Membatalkan permohonan izin yang masih pending
     */
    public function cancel(LeaveRequest $leave)
    {
        // Pastikan user hanya bisa membatalkan permohonan izinnya sendiri
        if ($leave->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk membatalkan permohonan izin ini.');
        }
        
        // Hanya bisa membatalkan yang masih pending
        if (!$leave->isPending()) {
            return redirect()->route('leave.index')->with('error', 'Hanya permohonan izin yang masih menunggu persetujuan yang dapat dibatalkan.');
        }
        
        try {
            DB::beginTransaction();
            
            // Hapus lampiran jika ada
            if ($leave->attachment && Storage::disk('public')->exists($leave->attachment)) {
                Storage::disk('public')->delete($leave->attachment);
            }
            
            // Hapus permohonan izin
            $leave->delete();
            
            DB::commit();
            
            return redirect()->route('leave.index')->with('success', 'Permohonan izin berhasil dibatalkan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 