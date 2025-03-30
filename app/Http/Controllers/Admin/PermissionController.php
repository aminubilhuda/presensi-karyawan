<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('permission:admin.permissions.manage');
    }
    
    /**
     * Menampilkan halaman pengaturan izin akses
     */
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::getGroupedByModule();
        
        return view('admin.permissions.index', compact('roles', 'permissions'));
    }
    
    /**
     * Menyimpan perubahan izin akses untuk role tertentu
     */
    public function update(Request $request, Role $role)
    {
        // Tambahkan log untuk debugging
        \Log::debug('Permission Update Request', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $request->permissions,
            'all_data' => $request->all()
        ]);
        
        // Jika tidak ada permissions yang dipilih, set array kosong daripada null
        $permissions = $request->has('permissions') ? $request->permissions : [];
        
        // Pastikan data permissions valid
        $validator = Validator::make($request->all(), [
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            \Log::debug('Validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Cek jumlah permission sebelum update
            $beforeCount = $role->permissions()->count();
            \Log::debug('Permissions count before sync: ' . $beforeCount);
            
            // Simpan permission yang dipilih dengan array kosong jika null
            $syncResult = $role->permissions()->sync($permissions);
            
            // Cek jumlah permission setelah update
            $afterCount = $role->permissions()->count();
            \Log::debug('Permissions count after sync: ' . $afterCount, [
                'attached' => $syncResult['attached'] ?? [],
                'detached' => $syncResult['detached'] ?? []
            ]);
            
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Izin akses untuk peran ' . $role->name . ' berhasil diperbarui. ' . 
                      'Ditambahkan: ' . count($syncResult['attached'] ?? []) . ', ' .
                      'Dihapus: ' . count($syncResult['detached'] ?? []));
        } catch (\Exception $e) {
            \Log::error('Error syncing permissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan list permission untuk role tertentu (untuk AJAX)
     */
    public function getRolePermissions(Role $role)
    {
        $permissions = $role->permissions->pluck('id')->toArray();
        
        return response()->json(['permissions' => $permissions]);
    }
} 