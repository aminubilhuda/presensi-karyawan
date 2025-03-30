<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Daftar permission default
        $permissions = [
            // Absensi
            ['name' => 'attendance.check-in', 'display_name' => 'Absen Masuk', 'module' => 'absensi', 'description' => 'Akses untuk melakukan absen masuk'],
            ['name' => 'attendance.check-out', 'display_name' => 'Absen Pulang', 'module' => 'absensi', 'description' => 'Akses untuk melakukan absen pulang'],
            ['name' => 'attendance.history', 'display_name' => 'Riwayat Absensi', 'module' => 'absensi', 'description' => 'Akses untuk melihat riwayat absensi sendiri'],
            ['name' => 'attendance.qr-generate', 'display_name' => 'QR Code Absensi', 'module' => 'absensi', 'description' => 'Akses untuk membuat QR code absensi'],
            
            // Izin/Cuti
            ['name' => 'leave.view', 'display_name' => 'Lihat Izin Sendiri', 'module' => 'perizinan', 'description' => 'Akses untuk melihat izin/cuti sendiri'],
            ['name' => 'leave.create', 'display_name' => 'Buat Izin', 'module' => 'perizinan', 'description' => 'Akses untuk membuat permohonan izin/cuti'],
            ['name' => 'leave.cancel', 'display_name' => 'Batalkan Izin', 'module' => 'perizinan', 'description' => 'Akses untuk membatalkan permohonan izin/cuti sendiri'],
            
            // Dokumen
            ['name' => 'documents.view', 'display_name' => 'Lihat Dokumen', 'module' => 'dokumen', 'description' => 'Akses untuk melihat dokumen'],
            ['name' => 'documents.upload', 'display_name' => 'Unggah Dokumen', 'module' => 'dokumen', 'description' => 'Akses untuk mengunggah dokumen'],
            ['name' => 'documents.download', 'display_name' => 'Unduh Dokumen', 'module' => 'dokumen', 'description' => 'Akses untuk mengunduh dokumen'],
            ['name' => 'documents.delete', 'display_name' => 'Hapus Dokumen', 'module' => 'dokumen', 'description' => 'Akses untuk menghapus dokumen'],
            
            // Tiket Support
            ['name' => 'support.view', 'display_name' => 'Lihat Tiket Support', 'module' => 'support', 'description' => 'Akses untuk melihat tiket support'],
            ['name' => 'support.create', 'display_name' => 'Buat Tiket Support', 'module' => 'support', 'description' => 'Akses untuk membuat tiket support'],
            ['name' => 'support.reply', 'display_name' => 'Balas Tiket Support', 'module' => 'support', 'description' => 'Akses untuk membalas tiket support'],
            ['name' => 'support.close', 'display_name' => 'Tutup Tiket Support', 'module' => 'support', 'description' => 'Akses untuk menutup tiket support'],
            
            // Kalender
            ['name' => 'calendar.view', 'display_name' => 'Lihat Kalender', 'module' => 'kalender', 'description' => 'Akses untuk melihat kalender'],
            
            // Admin - Manajemen Pengguna
            ['name' => 'admin.users.view', 'display_name' => 'Lihat Pengguna', 'module' => 'admin', 'description' => 'Akses untuk melihat daftar pengguna'],
            ['name' => 'admin.users.create', 'display_name' => 'Tambah Pengguna', 'module' => 'admin', 'description' => 'Akses untuk menambah pengguna baru'],
            ['name' => 'admin.users.edit', 'display_name' => 'Edit Pengguna', 'module' => 'admin', 'description' => 'Akses untuk mengedit pengguna'],
            ['name' => 'admin.users.delete', 'display_name' => 'Hapus Pengguna', 'module' => 'admin', 'description' => 'Akses untuk menghapus pengguna'],
            
            // Admin - Manajemen Peran
            ['name' => 'admin.roles.view', 'display_name' => 'Lihat Peran', 'module' => 'admin', 'description' => 'Akses untuk melihat daftar peran'],
            ['name' => 'admin.roles.create', 'display_name' => 'Tambah Peran', 'module' => 'admin', 'description' => 'Akses untuk menambah peran baru'],
            ['name' => 'admin.roles.edit', 'display_name' => 'Edit Peran', 'module' => 'admin', 'description' => 'Akses untuk mengedit peran'],
            ['name' => 'admin.roles.delete', 'display_name' => 'Hapus Peran', 'module' => 'admin', 'description' => 'Akses untuk menghapus peran'],
            
            // Admin - Manajemen Izin Akses
            ['name' => 'admin.permissions.manage', 'display_name' => 'Kelola Izin Akses', 'module' => 'admin', 'description' => 'Akses untuk mengelola izin akses peran'],
            
            // Admin - Laporan Absensi
            ['name' => 'admin.attendance.report', 'display_name' => 'Laporan Absensi', 'module' => 'admin', 'description' => 'Akses untuk melihat laporan absensi'],
            ['name' => 'admin.attendance.export', 'display_name' => 'Ekspor Laporan Absensi', 'module' => 'admin', 'description' => 'Akses untuk mengekspor laporan absensi'],
            
            // Admin - Monitoring Absensi
            ['name' => 'admin.attendance.monitor', 'display_name' => 'Monitoring Absensi', 'module' => 'admin', 'description' => 'Akses untuk memonitoring absensi realtime'],
            
            // Admin - Manajemen Izin/Cuti
            ['name' => 'admin.leave.view', 'display_name' => 'Lihat Permohonan Izin', 'module' => 'admin', 'description' => 'Akses untuk melihat daftar permohonan izin/cuti'],
            ['name' => 'admin.leave.approve', 'display_name' => 'Setujui Permohonan Izin', 'module' => 'admin', 'description' => 'Akses untuk menyetujui permohonan izin/cuti'],
            ['name' => 'admin.leave.reject', 'display_name' => 'Tolak Permohonan Izin', 'module' => 'admin', 'description' => 'Akses untuk menolak permohonan izin/cuti'],
            ['name' => 'admin.leave.export', 'display_name' => 'Ekspor Data Izin', 'module' => 'admin', 'description' => 'Akses untuk mengekspor data permohonan izin/cuti'],
            
            // Admin - Notifikasi
            ['name' => 'admin.notifications.broadcast', 'display_name' => 'Broadcast Pesan', 'module' => 'admin', 'description' => 'Akses untuk broadcast pesan ke pengguna'],
            ['name' => 'admin.notifications.settings', 'display_name' => 'Pengaturan Notifikasi', 'module' => 'admin', 'description' => 'Akses untuk mengatur notifikasi'],
            
            // Admin - Pengaturan Sistem
            ['name' => 'admin.workdays.manage', 'display_name' => 'Kelola Hari Kerja', 'module' => 'admin', 'description' => 'Akses untuk mengelola hari kerja'],
            ['name' => 'admin.holidays.manage', 'display_name' => 'Kelola Hari Libur', 'module' => 'admin', 'description' => 'Akses untuk mengelola hari libur'],
            ['name' => 'admin.locations.manage', 'display_name' => 'Kelola Lokasi Absensi', 'module' => 'admin', 'description' => 'Akses untuk mengelola lokasi absensi'],
            ['name' => 'admin.settings.manage', 'display_name' => 'Kelola Pengaturan Aplikasi', 'module' => 'admin', 'description' => 'Akses untuk mengelola pengaturan aplikasi'],
            
            // Dasbor
            ['name' => 'dashboard.view', 'display_name' => 'Lihat Dasbor', 'module' => 'dashboard', 'description' => 'Akses untuk melihat dasbor'],
            ['name' => 'dashboard.analytics', 'display_name' => 'Lihat Dasbor Analitik', 'module' => 'dashboard', 'description' => 'Akses untuk melihat dasbor analitik'],
        ];
        
        // Masukkan data permissions
        foreach ($permissions as $permission) {
            if (!DB::table('permissions')->where('name', $permission['name'])->exists()) {
                $permission['created_at'] = now();
                $permission['updated_at'] = now();
                DB::table('permissions')->insert($permission);
            }
        }
        
        // Berikan semua permission ke role Admin
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if ($adminRole) {
            $permissionIds = DB::table('permissions')->pluck('id')->toArray();
            foreach ($permissionIds as $permissionId) {
                if (!DB::table('role_permissions')->where('role_id', $adminRole->id)->where('permission_id', $permissionId)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Berikan permission ke role Kepala Sekolah
        $principalRole = DB::table('roles')->where('name', 'Kepala Sekolah')->first();
        if ($principalRole) {
            $principalPermissions = [
                // Permissions absensi
                'attendance.check-in', 'attendance.check-out', 'attendance.history', 'attendance.qr-generate',
                
                // Permissions izin/cuti
                'leave.view', 'leave.create', 'leave.cancel',
                
                // Permissions dokumen, support, kalender
                'documents.view', 'documents.upload', 'documents.download', 'documents.delete',
                'support.view', 'support.create', 'support.reply', 'support.close',
                'calendar.view',
                
                // Permissions monitoring dan laporan admin
                'admin.attendance.report', 'admin.attendance.export', 'admin.attendance.monitor',
                'admin.leave.view', 'admin.leave.approve', 'admin.leave.reject', 'admin.leave.export',
                
                // Permissions dasbor
                'dashboard.view', 'dashboard.analytics',
            ];
            
            foreach ($principalPermissions as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if ($permission && !DB::table('role_permissions')->where('role_id', $principalRole->id)->where('permission_id', $permission->id)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $principalRole->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Berikan permission ke role Guru
        $teacherRole = DB::table('roles')->where('name', 'Guru')->first();
        if ($teacherRole) {
            $teacherPermissions = [
                // Permissions absensi
                'attendance.check-in', 'attendance.check-out', 'attendance.history', 'attendance.qr-generate',
                
                // Permissions izin/cuti
                'leave.view', 'leave.create', 'leave.cancel',
                
                // Permissions dokumen, support, kalender
                'documents.view', 'documents.upload', 'documents.download',
                'support.view', 'support.create', 'support.reply',
                'calendar.view',
                
                // Permissions dasbor
                'dashboard.view',
            ];
            
            foreach ($teacherPermissions as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if ($permission && !DB::table('role_permissions')->where('role_id', $teacherRole->id)->where('permission_id', $permission->id)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $teacherRole->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Berikan permission ke role Staf TU
        $staffRole = DB::table('roles')->where('name', 'Staf TU')->first();
        if ($staffRole) {
            $staffPermissions = [
                // Permissions absensi
                'attendance.check-in', 'attendance.check-out', 'attendance.history', 'attendance.qr-generate',
                
                // Permissions izin/cuti
                'leave.view', 'leave.create', 'leave.cancel',
                
                // Permissions dokumen, support, kalender
                'documents.view', 'documents.upload', 'documents.download',
                'support.view', 'support.create', 'support.reply',
                'calendar.view',
                
                // Permissions dasbor
                'dashboard.view',
            ];
            
            foreach ($staffPermissions as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if ($permission && !DB::table('role_permissions')->where('role_id', $staffRole->id)->where('permission_id', $permission->id)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $staffRole->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
    }
}; 