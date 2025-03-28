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
    
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Rute absensi (untuk guru dan staff)
    Route::middleware('role:Guru,Staf TU')->group(function () {
        Route::get('/attendance/check-in', [AttendanceController::class, 'checkInForm'])->name('attendance.check-in');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::get('/attendance/check-out', [AttendanceController::class, 'checkOutForm'])->name('attendance.check-out');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    });
    
    // Rute admin
    Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
        // Kelola pengguna
        Route::resource('users', UserController::class);
        
        // Laporan absensi
        Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');
        Route::get('/attendance/report/export', [AttendanceReportController::class, 'export'])->name('attendance.report.export');
        
        // Monitoring absensi
        Route::get('/attendance/monitor', [AttendanceMonitorController::class, 'index'])->name('attendance.monitor');
        
        // Kelola hari kerja
        Route::resource('workdays', WorkdayController::class);
        
        // Kelola hari libur
        Route::resource('holidays', HolidayController::class);
        
        // Kelola lokasi absensi
        Route::resource('locations', LocationController::class);
        
        // Pengaturan aplikasi
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    });
});