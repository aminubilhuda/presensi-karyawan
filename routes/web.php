<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceMonitorController;
use App\Http\Controllers\Admin\WorkdayController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\LeaveManagementController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\FaceAttendanceController;
use Illuminate\Support\Facades\Route;

// Rute publik
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rute absensi dengan wajah (dapat diakses tanpa login)
Route::get('/face-attendance', [FaceRecognitionController::class, 'faceAttendanceForm'])->name('face.attendance.form');
Route::post('/face-attendance/process', [FaceAttendanceController::class, 'processAttendance'])->name('face.attendance.process');

// Rute autentikasi
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute dashboard dan profil (butuh autentikasi)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics')->middleware('can:viewAnalytics,App\Models\User');
    
    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index')->middleware('permission:calendar.view');
    
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Two Factor Authentication
    Route::get('/two-factor', [TwoFactorController::class, 'showTwoFactorForm'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'enableTwoFactor'])->name('two-factor.enable');
    Route::delete('/two-factor', [TwoFactorController::class, 'disableTwoFactor'])->name('two-factor.disable');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    
    // Documents
    Route::resource('documents', DocumentController::class)->middleware('permission:documents.view,documents.upload,documents.download,documents.delete');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download')->middleware('permission:documents.download');
    
    // Support Tickets
    Route::resource('support', SupportController::class)->middleware('permission:support.view,support.create,support.reply');
    Route::post('/support/{support}/close', [SupportController::class, 'close'])->name('support.close')->middleware('permission:support.close');
    
    // Rute absensi - berdasarkan permission
    Route::get('/attendance/check-in', [AttendanceController::class, 'checkInForm'])
        ->name('attendance.check-in.form')
        ->middleware('permission:attendance.check-in');
    
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('attendance.check-in')
        ->middleware('permission:attendance.check-in');
    
    Route::get('/attendance/check-out', [AttendanceController::class, 'checkOutForm'])
        ->name('attendance.check-out.form')
        ->middleware('permission:attendance.check-out');
    
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('attendance.check-out')
        ->middleware('permission:attendance.check-out');
    
    Route::get('/attendance/history', [AttendanceController::class, 'history'])
        ->name('attendance.history')
        ->middleware('permission:attendance.history');
    
    // QR Code Absensi
    Route::get('/attendance/qr-generate', [QrAttendanceController::class, 'generateQrForm'])
        ->name('attendance.qr-generate')
        ->middleware('permission:attendance.qr-generate');
    
    // Menu Izin/Cuti
    Route::get('/leave', [LeaveRequestController::class, 'index'])
        ->name('leave.index')
        ->middleware('permission:leave.view');
    
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])
        ->name('leave.create')
        ->middleware('permission:leave.create');
    
    Route::post('/leave', [LeaveRequestController::class, 'store'])
        ->name('leave.store')
        ->middleware('permission:leave.create');
    
    Route::get('/leave/{leave}', [LeaveRequestController::class, 'show'])
        ->name('leave.show')
        ->middleware('permission:leave.view');
    
    Route::delete('/leave/{leave}/cancel', [LeaveRequestController::class, 'cancel'])
        ->name('leave.cancel')
        ->middleware('permission:leave.cancel');
    
    // Rute publik untuk scan QR Code absensi (tanpa auth)
    Route::get('/qr/scan/{token}', [QrAttendanceController::class, 'scanQrCode'])->name('qr.scan');
    Route::post('/qr/process', [QrAttendanceController::class, 'processQrAttendance'])->name('qr.process-attendance');
    
    // Rute admin - diubah menjadi berdasarkan permission bukan role
    Route::prefix('admin')->name('admin.')->group(function () {
        // Kelola pengguna
        Route::resource('users', UserController::class)->middleware('permission:admin.users.view,admin.users.create,admin.users.edit,admin.users.delete');
        
        // Export dan import pengguna
        Route::get('/users-export', [UserController::class, 'export'])
            ->name('users.export')
            ->middleware('permission:admin.users.view');
        
        Route::get('/users-import-template', [UserController::class, 'importTemplate'])
            ->name('users.import.template')
            ->middleware('permission:admin.users.create');
        
        Route::get('/users-import', [UserController::class, 'showImportForm'])
            ->name('users.import')
            ->middleware('permission:admin.users.create');
        
        Route::post('/users-import', [UserController::class, 'processImport'])
            ->name('users.import.process')
            ->middleware('permission:admin.users.create');
        
        // Kelola peran
        Route::resource('roles', RoleController::class)
            ->except(['create', 'edit', 'show'])
            ->middleware('permission:admin.roles.view,admin.roles.create,admin.roles.edit,admin.roles.delete');
        
        // Laporan absensi
        Route::get('/attendance/report', [AttendanceReportController::class, 'index'])
            ->name('attendance.report')
            ->middleware('permission:admin.attendance.report');
        
        Route::get('/attendance/report/export', [AttendanceReportController::class, 'export'])
            ->name('attendance.report.export')
            ->middleware('permission:admin.attendance.export');
        
        // Monitoring absensi
        Route::get('/attendance/monitor', [AttendanceMonitorController::class, 'index'])
            ->name('attendance.monitor')
            ->middleware('permission:admin.attendance.monitor');
        
        Route::get('/attendance/user/{user}', [AttendanceMonitorController::class, 'userAttendances'])
            ->name('attendances.user')
            ->middleware('permission:admin.attendance.monitor');
        
        // Kelola hari kerja
        Route::resource('workdays', WorkdayController::class)
            ->middleware('permission:admin.workdays.manage');
        
        // Kelola hari libur
        Route::resource('holidays', HolidayController::class)
            ->middleware('permission:admin.holidays.manage');
        
        // Kelola lokasi absensi
        Route::resource('locations', LocationController::class)
            ->middleware('permission:admin.locations.manage');
        
        // Pengaturan aplikasi
        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index')
            ->middleware('permission:admin.settings.manage');
        
        Route::put('/settings', [SettingController::class, 'update'])
            ->name('settings.update')
            ->middleware('permission:admin.settings.manage');
        
        // Kelola permohonan izin/cuti
        Route::get('/leave', [LeaveManagementController::class, 'index'])
            ->name('leave.index')
            ->middleware('permission:admin.leave.view');
        
        Route::get('/leave/{leave}', [LeaveManagementController::class, 'show'])
            ->name('leave.show')
            ->middleware('permission:admin.leave.view');
        
        Route::post('/leave/{leave}/approve', [LeaveManagementController::class, 'approve'])
            ->name('leave.approve')
            ->middleware('permission:admin.leave.approve');
        
        Route::post('/leave/{leave}/reject', [LeaveManagementController::class, 'reject'])
            ->name('leave.reject')
            ->middleware('permission:admin.leave.reject');
        
        Route::get('/leave/export', [LeaveManagementController::class, 'export'])
            ->name('leave.export')
            ->middleware('permission:admin.leave.export');
        
        // Manajemen Izin Akses
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index')
            ->middleware('permission:admin.permissions.manage');
        
        Route::post('/permissions/{role}', [PermissionController::class, 'update'])
            ->name('permissions.update')
            ->middleware('permission:admin.permissions.manage');
        
        Route::get('/permissions/{role}/get', [PermissionController::class, 'getRolePermissions'])
            ->name('permissions.get')
            ->middleware('permission:admin.permissions.manage');
    });

    // Rute untuk notifikasi (hanya admin)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/broadcast', [NotificationController::class, 'broadcastForm'])
            ->name('broadcast')
            ->middleware('permission:admin.notifications.broadcast');
        
        Route::post('/broadcast', [NotificationController::class, 'sendBroadcast'])
            ->name('send-broadcast')
            ->middleware('permission:admin.notifications.broadcast');
        
        Route::get('/settings', [NotificationController::class, 'settings'])
            ->name('settings')
            ->middleware('permission:admin.notifications.settings');
        
        Route::post('/settings', [NotificationController::class, 'saveSettings'])
            ->name('save-settings')
            ->middleware('permission:admin.notifications.settings');
    });

    // Pendaftaran wajah (memerlukan login)
    Route::get('/face-register', [FaceRecognitionController::class, 'registerFaceForm'])->name('face.register.form');
    Route::post('/face-register', [FaceRecognitionController::class, 'registerFace'])->name('face.register');
});