<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $fonnteService;
    
    public function __construct(FonnteService $fonnteService)
    {
        $this->middleware(['auth', 'role:Admin']);
        $this->fonnteService = $fonnteService;
    }
    
    /**
     * Menampilkan halaman broadcast
     */
    public function broadcastForm()
    {
        $roles = Role::all();
        return view('notifications.broadcast', compact('roles'));
    }
    
    /**
     * Mengirim pesan broadcast
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'role_id' => 'nullable|exists:roles,id',
        ]);
        
        $filters = [];
        if ($request->filled('role_id')) {
            $filters['role_id'] = $request->role_id;
        }
        
        // Menghitung jumlah penerima
        $query = User::whereNotNull('phone')
            ->where('wa_notifications', true);
            
        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }
        
        $recipientCount = $query->count();
        
        if ($recipientCount == 0) {
            return back()->with('error', 'Tidak ada penerima yang ditemukan dengan kriteria yang dipilih.');
        }
        
        // Kirim pesan broadcast
        $results = $this->fonnteService->sendBroadcastMessage($request->message, $filters);
        
        if (empty($results)) {
            return back()->with('error', 'Gagal mengirim pesan broadcast. Silakan periksa pengaturan API Fonnte.');
        }
        
        return back()->with('success', "Pesan broadcast berhasil dikirim ke {$recipientCount} karyawan.");
    }
    
    /**
     * Menampilkan halaman pengaturan notifikasi
     */
    public function settings()
    {
        return view('notifications.settings');
    }
    
    /**
     * Menyimpan pengaturan notifikasi
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'fonnte_api_key' => 'nullable|string',
            'enable_notifications' => 'nullable|boolean',
        ]);
        
        // Cek status awal
        $oldEnableStatus = config('services.fonnte.enable_notifications');
        $newEnableStatus = $request->has('enable_notifications');
        
        // Simpan pengaturan ke file .env
        $this->updateEnvFile('FONNTE_API_KEY', $request->fonnte_api_key);
        $this->updateEnvFile('FONNTE_ENABLE_NOTIFICATIONS', $newEnableStatus ? 'true' : 'false');
        
        // Perbaharui nilai di cache config
        config(['services.fonnte.api_key' => $request->fonnte_api_key]);
        config(['services.fonnte.enable_notifications' => $newEnableStatus]);
        
        // Log perubahan status
        \Log::info('Pengaturan notifikasi WhatsApp diperbarui', [
            'api_key_updated' => $request->fonnte_api_key !== config('services.fonnte.api_key'),
            'enable_notifications_before' => $oldEnableStatus,
            'enable_notifications_after' => $newEnableStatus,
        ]);
        
        // Refresh konfigurasi aplikasi jika di production
        if (app()->environment('production')) {
            \Artisan::call('config:clear');
        }
        
        return back()->with('success', 'Pengaturan notifikasi berhasil disimpan. Status notifikasi: ' . ($newEnableStatus ? 'Aktif' : 'Tidak Aktif'));
    }
    
    /**
     * Helper untuk mengupdate file .env
     */
    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            // Kasus khusus untuk nilai boolean
            if (is_bool($value) || $value === 'true' || $value === 'false') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            }
            
            // Escape karakter khusus dalam value
            $value = str_replace('"', '\"', $value);
            
            // Cek apakah key sudah ada dalam file .env
            $content = file_get_contents($path);
            
            // Cari nilai yang ada
            $pattern = "/^{$key}=.*$/m";
            if (preg_match($pattern, $content)) {
                // Update nilai yang sudah ada
                $content = preg_replace($pattern, "{$key}={$value}", $content);
                file_put_contents($path, $content);
                
                // Log proses update
                \Log::debug("Updated .env key: {$key} with new value");
            } else {
                // Tambahkan key baru
                file_put_contents($path, $content . "\n{$key}={$value}\n");
                
                // Log proses penambahan
                \Log::debug("Added new .env key: {$key}");
            }
        }
    }
} 