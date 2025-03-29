<?php

namespace App\Providers;

use App\Models\AttendanceLocation;
use App\Models\Setting;
use App\Models\Workday;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone to Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
        \Carbon\Carbon::setLocale('id');
        
        // Cek apakah aplikasi sudah diinstall (tabel sudah dibuat)
        if (Schema::hasTable('settings')) {
            // Mendapatkan pengaturan aplikasi untuk seluruh view
            $appSettings = $this->getAppSettings();
            View::share('appSettings', $appSettings);
            
            // Mendapatkan data hari kerja untuk seluruh view
            $workdays = $this->getWorkdays();
            View::share('workdays', $workdays);
            
            // Mendapatkan lokasi absensi aktif untuk view tertentu
            View::composer(['attendance.*', 'dashboard'], function ($view) {
                $view->with('attendanceLocations', $this->getAttendanceLocations());
            });
        }
    }
    
    /**
     * Mendapatkan pengaturan aplikasi dengan caching
     */
    private function getAppSettings()
    {
        return Cache::remember('app_settings', 60 * 24, function () {
            $settings = Setting::all();
            $formattedSettings = [];
            
            foreach ($settings as $setting) {
                $formattedSettings[$setting->key] = $setting->value;
            }
            
            return $formattedSettings;
        });
    }
    
    /**
     * Mendapatkan data hari kerja dengan caching
     */
    private function getWorkdays()
    {
        return Workday::getActiveWorkdays();
    }
    
    /**
     * Mendapatkan lokasi absensi aktif dengan caching
     */
    private function getAttendanceLocations()
    {
        return AttendanceLocation::getActiveLocations();
    }
}
