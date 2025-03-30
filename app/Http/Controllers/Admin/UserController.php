<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Exports\UsersExport;
use App\Exports\UsersImportTemplate;
use App\Imports\UsersImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:255|unique:users|alpha_dash',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'wa_notifications' => 'sometimes|boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'wa_notifications' => $request->has('wa_notifications') ? true : false,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('user_photos', 'public');
            $userData['photo'] = $photoPath;
        }

        $user = User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Get recent attendance data
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
            
        // Calculate attendance statistics for current month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $monthlyStats = [
            'present' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
            'absent' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->whereIn('status', ['izin', 'sakit', 'alfa'])
                ->count(),
            'late' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'terlambat')
                ->count(),
        ];
        
        return view('admin.users.show', compact('user', 'recentAttendances', 'monthlyStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id . '|alpha_dash',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'wa_notifications' => $request->has('wa_notifications') ? true : false,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            
            $photoPath = $request->file('photo')->store('user_photos', 'public');
            $userData['photo'] = $photoPath;
        }
        
        // Handle photo removal
        if ($request->input('remove_photo') == '1' && $user->photo) {
            if (Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $userData['photo'] = null;
        }

        $user->update($userData);

        // Handle password change if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }
        
        // Delete user photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Export user data to Excel
     */
    public function export()
    {
        return UsersExport::download();
    }
    
    /**
     * Download import template
     */
    public function importTemplate()
    {
        $format = request()->query('format', 'csv');
        
        if ($format === 'xlsx') {
            // Verifikasi dukungan ZipArchive
            if (!class_exists('ZipArchive')) {
                session()->flash('error', 'Ekstensi PHP ZIP tidak tersedia. Template XLSX tidak dapat dibuat. Silakan gunakan template CSV sebagai alternatif.');
                return response()->download(public_path('templates/template-import-users.csv'));
            }
            
            // Gunakan kelas template yang sudah ada
            return UsersImportTemplate::download();
        }
        
        return response()->download(public_path('templates/template-import-users.csv'));
    }
    
    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('admin.users.import');
    }
    
    /**
     * Process the import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'Format file harus CSV, XLS, atau XLSX',
            'file.max' => 'Ukuran file maksimal 2MB',
        ]);
        
        try {
            // Verifikasi dukungan ZipArchive untuk XLSX
            $extension = $request->file('file')->getClientOriginalExtension();
            if (in_array($extension, ['xlsx', 'xls']) && !class_exists('ZipArchive')) {
                return back()->with('error', 'Ekstensi PHP ZIP tidak tersedia. Untuk mengimpor file Excel (XLSX/XLS), instal ekstensi PHP ZIP atau gunakan format CSV.');
            }
            
            DB::beginTransaction();
            
            $result = UsersImport::import($request->file('file'));
            
            DB::commit();
            
            if ($result['success']) {
                // Hitung jumlah data yang diimpor
                $importedCount = $result['imported_count'] ?? 'beberapa';
                
                // Cek jika ada error tapi masih beberapa yang sukses
                if (!empty($result['errors'])) {
                    return redirect()->route('admin.users.index')
                        ->with('success', 'Data pengguna berhasil diimpor. ' . $importedCount . ' data telah ditambahkan.')
                        ->with('warning', 'Beberapa data tidak dapat diimpor: ' . count($result['errors']) . ' error ditemukan.');
                }
                
                return redirect()->route('admin.users.index')
                    ->with('success', 'Data pengguna berhasil diimpor. ' . $importedCount . ' data telah ditambahkan.');
            } else {
                // Cek jika ada beberapa data yang berhasil meskipun ada error
                if (isset($result['imported_count']) && $result['imported_count'] > 0) {
                    return back()
                        ->withErrors(['import_errors' => $result['errors']])
                        ->with('warning', 'Terjadi kesalahan saat mengimpor data, tetapi ' . $result['imported_count'] . ' data berhasil ditambahkan.')
                        ->with('error', 'Terjadi kesalahan saat mengimpor data');
                }
                
                return back()
                    ->withErrors(['import_errors' => $result['errors']])
                    ->with('error', 'Terjadi kesalahan saat mengimpor data');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}
