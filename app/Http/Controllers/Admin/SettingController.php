<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan
     */
    public function index()
    {
        $settings = [
            'check_in_time' => Setting::getValue('check_in_time', '08:00'),
            'check_out_time' => Setting::getValue('check_out_time', '16:00'),
            'late_threshold' => Setting::getValue('late_threshold', 15),
            'early_leave_threshold' => Setting::getValue('early_leave_threshold', 15),
            'default_radius' => Setting::getValue('default_radius', 100),
            'app_name' => Setting::getValue('app_name', 'Sistem Absensi Sekolah'),
            'app_description' => Setting::getValue('app_description', 'Aplikasi manajemen absensi karyawan'),
            'app_logo' => Setting::getValue('app_logo', null),
            'app_favicon' => Setting::getValue('app_favicon', null),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Menyimpan pengaturan
     */
    public function store(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string',
            'app_url' => 'required|url',
            'timezone' => 'required|string',
            'locale' => 'required|string',
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_encryption' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Simpan pengaturan ke file .env
        $this->updateEnvFile([
            'APP_NAME' => $request->app_name,
            'APP_DESCRIPTION' => $request->app_description,
            'APP_URL' => $request->app_url,
            'APP_TIMEZONE' => $request->timezone,
            'APP_LOCALE' => $request->locale,
            'MAIL_MAILER' => $request->mail_mailer,
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_ENCRYPTION' => $request->mail_encryption,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => $request->mail_from_name,
        ]);

        // Bersihkan cache konfigurasi
        Cache::forget('config');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Memperbarui file .env
     */
    protected function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $value = str_replace('"', '\\"', $value);
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Memperbarui pengaturan
     */
    public function update(Request $request)
    {
        $request->validate([
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i|after:check_in_time',
            'late_threshold' => 'required|integer|min:0',
            'early_leave_threshold' => 'required|integer|min:0',
            'default_radius' => 'required|integer|min:0',
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string',
            'app_logo' => 'nullable|image|mimes:jpg,png,jpeg,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png|max:1024',
            'remove_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
        ]);

        // Update pengaturan jam kerja
        Setting::setValue('check_in_time', $request->check_in_time, 'Jam masuk yang diizinkan untuk absensi');
        Setting::setValue('check_out_time', $request->check_out_time, 'Jam pulang yang diizinkan untuk absensi');
        Setting::setValue('late_threshold', $request->late_threshold, 'Batas waktu keterlambatan dalam menit');
        Setting::setValue('early_leave_threshold', $request->early_leave_threshold, 'Batas waktu pulang cepat dalam menit');
        Setting::setValue('default_radius', $request->default_radius, 'Radius default untuk lokasi absensi');
        
        // Update pengaturan aplikasi
        Setting::setValue('app_name', $request->app_name, 'Nama aplikasi');
        Setting::setValue('app_description', $request->app_description, 'Deskripsi aplikasi');
        
        // Simpan juga ke file .env untuk APP_NAME
        $this->updateEnvFile([
            'APP_NAME' => $request->app_name,
        ]);
        
        // Update logo aplikasi jika ada
        if ($request->hasFile('app_logo')) {
            $currentLogo = Setting::getValue('app_logo');
            
            // Hapus logo lama jika ada
            if ($currentLogo && \Storage::disk('public')->exists($currentLogo)) {
                \Storage::disk('public')->delete($currentLogo);
            }
            
            // Simpan logo baru
            $logoPath = $request->file('app_logo')->store('app_assets', 'public');
            Setting::setValue('app_logo', $logoPath, 'Logo aplikasi');
        } elseif ($request->has('remove_logo') && $request->remove_logo) {
            $currentLogo = Setting::getValue('app_logo');
            
            // Hapus logo jika ada
            if ($currentLogo && \Storage::disk('public')->exists($currentLogo)) {
                \Storage::disk('public')->delete($currentLogo);
            }
            
            Setting::setValue('app_logo', null, 'Logo aplikasi');
        }
        
        // Update favicon aplikasi jika ada
        if ($request->hasFile('app_favicon')) {
            $currentFavicon = Setting::getValue('app_favicon');
            
            // Hapus favicon lama jika ada
            if ($currentFavicon && \Storage::disk('public')->exists($currentFavicon)) {
                \Storage::disk('public')->delete($currentFavicon);
            }
            
            // Simpan favicon baru
            $faviconPath = $request->file('app_favicon')->store('app_assets', 'public');
            Setting::setValue('app_favicon', $faviconPath, 'Favicon aplikasi');
        } elseif ($request->has('remove_favicon') && $request->remove_favicon) {
            $currentFavicon = Setting::getValue('app_favicon');
            
            // Hapus favicon jika ada
            if ($currentFavicon && \Storage::disk('public')->exists($currentFavicon)) {
                \Storage::disk('public')->delete($currentFavicon);
            }
            
            Setting::setValue('app_favicon', null, 'Favicon aplikasi');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui');
    }
}