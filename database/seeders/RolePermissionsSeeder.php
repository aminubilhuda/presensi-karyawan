<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua data role_permissions
        DB::table('role_permissions')->truncate();
        
        // Ambil semua role
        $roles = Role::all();
        
        // Ambil semua permission
        $permissions = Permission::all();
        
        // Admin dapat semua permission
        $adminRole = $roles->where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($permissions->pluck('id')->toArray());
            $this->command->info('Admin diberikan semua permission.');
        } else {
            $this->command->error('Role Admin tidak ditemukan!');
        }
        
        // Guru mendapatkan permission yang relevan
        $teacherRole = $roles->where('name', 'Guru')->first();
        if ($teacherRole) {
            $teacherPermissions = [
                'attendance.check-in', 'attendance.check-out', 'attendance.history', 'attendance.qr-generate',
                'leave.view', 'leave.create', 'leave.cancel',
                'documents.view', 'documents.upload', 'documents.download',
                'support.view', 'support.create', 'support.reply',
                'calendar.view',
                'dashboard.view'
            ];
            
            $teacherPermissionIds = Permission::whereIn('name', $teacherPermissions)->pluck('id')->toArray();
            $teacherRole->permissions()->sync($teacherPermissionIds);
            $this->command->info('Guru diberikan permission spesifik.');
        }
        
        // Staf TU
        $staffRole = $roles->where('name', 'Staf TU')->first();
        if ($staffRole) {
            $staffPermissions = [
                'attendance.check-in', 'attendance.check-out', 'attendance.history',
                'leave.view', 'leave.create', 'leave.cancel',
                'documents.view', 'documents.upload', 'documents.download', 'documents.delete',
                'support.view', 'support.create', 'support.reply', 'support.close',
                'calendar.view',
                'dashboard.view'
            ];
            
            $staffPermissionIds = Permission::whereIn('name', $staffPermissions)->pluck('id')->toArray();
            $staffRole->permissions()->sync($staffPermissionIds);
            $this->command->info('Staf TU diberikan permission spesifik.');
        }
        
        // Kepala Sekolah 
        $principalRole = $roles->where('name', 'Kepala Sekolah')->first();
        if ($principalRole) {
            $principalPermissions = Permission::where('module', '!=', 'admin')->pluck('id')->toArray();
            
            // Tambahkan beberapa permission admin yang relevan
            $additionalPermissions = Permission::whereIn('name', [
                'admin.attendance.report', 'admin.attendance.export',
                'admin.attendance.monitor', 'admin.leave.view',
                'admin.leave.approve', 'admin.leave.reject'
            ])->pluck('id')->toArray();
            
            $principalPermissionIds = array_merge($principalPermissions, $additionalPermissions);
            $principalRole->permissions()->sync($principalPermissionIds);
            $this->command->info('Kepala Sekolah diberikan permission spesifik.');
        }
        
        $this->command->info('Semua role_permissions berhasil di-seed!');
    }
} 