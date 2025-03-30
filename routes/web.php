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
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

// Rute publik
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rute autentikasi
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute dashboard dan profil (butuh autentikasi)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics')->middleware('can:viewAnalytics,App\Models\User');
    
    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    
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
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    
    // Support Tickets
    Route::resource('support', SupportController::class);
    Route::post('/support/{support}/close', [SupportController::class, 'close'])->name('support.close');
    
    // Rute absensi (untuk guru dan staff)
    Route::middleware('role:Guru,Staf TU')->group(function () {
        Route::get('/attendance/check-in', [AttendanceController::class, 'checkInForm'])->name('attendance.check-in.form');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::get('/attendance/check-out', [AttendanceController::class, 'checkOutForm'])->name('attendance.check-out.form');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
        
        // QR Code Absensi
        Route::get('/attendance/qr-generate', [QrAttendanceController::class, 'generateQrForm'])->name('attendance.qr-generate');
        
        // Menu Izin/Cuti
        Route::get('/leave', [LeaveRequestController::class, 'index'])->name('leave.index');
        Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
        Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
        Route::get('/leave/{leave}', [LeaveRequestController::class, 'show'])->name('leave.show');
        Route::delete('/leave/{leave}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');
    });
    
    // Rute publik untuk scan QR Code absensi (tanpa auth)
    Route::get('/qr/scan/{token}', [QrAttendanceController::class, 'scanQrCode'])->name('qr.scan');
    Route::post('/qr/process', [QrAttendanceController::class, 'processQrAttendance'])->name('qr.process-attendance');
    
    // Rute admin
    Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
        // Kelola pengguna
        Route::resource('users', UserController::class);
        
        // Kelola peran
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show']);
        
        // Laporan absensi
        Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');
        Route::get('/attendance/report/export', [AttendanceReportController::class, 'export'])->name('attendance.report.export');
        
        // Monitoring absensi
        Route::get('/attendance/monitor', [AttendanceMonitorController::class, 'index'])->name('attendance.monitor');
        Route::get('/attendance/user/{user}', [AttendanceMonitorController::class, 'userAttendances'])->name('attendances.user');
        
        // Kelola hari kerja
        Route::resource('workdays', WorkdayController::class);
        
        // Kelola hari libur
        Route::resource('holidays', HolidayController::class);
        
        // Kelola lokasi absensi
        Route::resource('locations', LocationController::class);
        
        // Pengaturan aplikasi
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        
        // Kelola permohonan izin/cuti
        Route::get('/leave', [App\Http\Controllers\Admin\LeaveManagementController::class, 'index'])->name('leave.index');
        Route::get('/leave/{leave}', [App\Http\Controllers\Admin\LeaveManagementController::class, 'show'])->name('leave.show');
        Route::post('/leave/{leave}/approve', [App\Http\Controllers\Admin\LeaveManagementController::class, 'approve'])->name('leave.approve');
        Route::post('/leave/{leave}/reject', [App\Http\Controllers\Admin\LeaveManagementController::class, 'reject'])->name('leave.reject');
        Route::get('/leave/export', [App\Http\Controllers\Admin\LeaveManagementController::class, 'export'])->name('leave.export');
    });

    // Rute untuk notifikasi (hanya admin)
    Route::middleware(['auth', 'role:Admin'])->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/broadcast', [NotificationController::class, 'broadcastForm'])->name('broadcast');
        Route::post('/broadcast', [NotificationController::class, 'sendBroadcast'])->name('send-broadcast');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'saveSettings'])->name('save-settings');
    });
});