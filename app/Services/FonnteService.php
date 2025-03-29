<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FonnteService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.fonnte.com';
    protected $countryCode = '62'; // Default Indonesia
    protected $cacheTimeout = 3600; // 1 jam

    public function __construct()
    {
        $this->apiKey = config('services.fonnte.api_key');
    }

    /**
     * Kirim notifikasi absen masuk
     *
     * @param string $phone Nomor telepon penerima
     * @param array $data Data yang akan dikirim
     * @return array|null
     */
    public function sendCheckInNotification(string $phone, array $data)
    {
        $message = "✅ *KONFIRMASI ABSEN MASUK*\n\n"
            . "Halo *{$data['name']}*,\n"
            . "Absen masuk Anda telah berhasil dicatat.\n\n"
            . "📅 Tanggal: {$data['date']}\n"
            . "⏰ Waktu: {$data['time']}\n"
            . "📍 Lokasi: {$data['location']}\n"
            . "📊 Status: *{$data['status']}*\n\n"
            . "Terima kasih telah melakukan absensi hari ini. Semoga hari kerja Anda menyenangkan!\n\n"
            . "Pesan ini dikirim secara otomatis oleh sistem. Mohon tidak membalas pesan ini.";

        return $this->sendWhatsAppMessage($phone, $message);
    }

    /**
     * Kirim notifikasi absen pulang
     *
     * @param string $phone Nomor telepon penerima
     * @param array $data Data yang akan dikirim
     * @return array|null
     */
    public function sendCheckOutNotification(string $phone, array $data)
    {
        $message = "✅ *KONFIRMASI ABSEN PULANG*\n\n"
            . "Halo *{$data['name']}*,\n"
            . "Absen pulang Anda telah berhasil dicatat.\n\n"
            . "📅 Tanggal: {$data['date']}\n"
            . "⏰ Waktu: {$data['time']}\n"
            . "📍 Lokasi: {$data['location']}\n"
            . "⏱️ Durasi Kerja: {$data['work_duration']}\n\n"
            . "Terima kasih atas kerja keras Anda hari ini. Selamat beristirahat!\n\n"
            . "Pesan ini dikirim secara otomatis oleh sistem. Mohon tidak membalas pesan ini.";

        return $this->sendWhatsAppMessage($phone, $message);
    }
    
    /**
     * Kirim pesan broadcast ke semua karyawan
     *
     * @param string $message Pesan yang akan dikirim
     * @param array $filters Filter tambahan (role_id, dll)
     * @return array
     */
    public function sendBroadcastMessage(string $message, array $filters = [])
    {
        // Jika API key tidak diatur, return array kosong
        if (empty($this->apiKey)) {
            Log::warning('Fonnte API key is not set. Broadcast message not sent.');
            return [];
        }
        
        // Gunakan cache untuk menghindari query berulang dalam waktu singkat
        $cacheKey = 'broadcast_recipients_' . md5(json_encode($filters));
        
        $users = Cache::remember($cacheKey, $this->cacheTimeout, function() use ($filters) {
            // Ambil semua user yang punya nomor telepon dan ingin menerima notifikasi
            $query = User::whereNotNull('phone')
                ->where('wa_notifications', true);
                
            // Tambahkan filter jika ada
            if (isset($filters['role_id'])) {
                $query->where('role_id', $filters['role_id']);
            }
            
            return $query->get();
        });
        
        // Jika tidak ada user yang memenuhi kriteria
        if ($users->isEmpty()) {
            Log::info('No users found for broadcast message');
            return [];
        }
        
        // Ambil semua nomor telepon dan format dengan benar
        $phones = $users->map(function($user) {
            return $this->formatPhoneNumber($user->phone);
        })->toArray();
        
        // Format pesan dengan awalan untuk broadcast
        $formattedMessage = "📢 *PENGUMUMAN*\n\n" . $message . "\n\n" .
            "Pesan ini dikirim oleh Admin. Mohon tidak membalas pesan ini.";
        
        // Kirim pesan broadcast
        $results = [];
        
        try {
            // Kirim grup pesan (max 1000 nomor per request)
            foreach (array_chunk($phones, 1000) as $chunk) {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey
                ])->post($this->baseUrl . '/send', [
                    'target' => implode(',', $chunk),
                    'message' => $formattedMessage,
                    'countryCode' => $this->countryCode,
                ]);
                
                $result = $response->json();
                
                if ($response->successful()) {
                    Log::info('Broadcast message sent successfully', [
                        'total_recipients' => count($chunk),
                        'status' => $result['status'] ?? 'unknown'
                    ]);
                } else {
                    Log::error('Failed to send broadcast message', [
                        'error' => $result['reason'] ?? $response->body()
                    ]);
                }
                
                $results[] = $result;
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Exception when sending broadcast message', [
                'exception' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Kirim pesan WhatsApp menggunakan API Fonnte
     *
     * @param string $phone Nomor telepon penerima
     * @param string $message Pesan yang akan dikirim
     * @return array|null
     */
    protected function sendWhatsAppMessage(string $phone, string $message)
    {
        // Jika API key tidak diatur, return null
        if (empty($this->apiKey)) {
            Log::warning('Fonnte API key is not set. WhatsApp message not sent.');
            return null;
        }

        try {
            // Format nomor telepon
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey
            ])->post($this->baseUrl . '/send', [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => $this->countryCode,
            ]);

            $result = $response->json();

            // Log hasil request
            if ($response->successful()) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $formattedPhone,
                    'status' => $result['status'] ?? 'unknown'
                ]);
            } else {
                Log::error('Failed to send WhatsApp notification', [
                    'phone' => $formattedPhone,
                    'error' => $result['reason'] ?? $response->body()
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp notification', [
                'phone' => $phone,
                'exception' => $e->getMessage()
            ]);

            return null;
        }
    }
    
    /**
     * Format nomor telepon untuk memastikan format yang benar
     * 
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone)
    {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/\D/', '', $phone);
        
        // Hapus kode negara jika ada
        if (substr($phone, 0, 2) == $this->countryCode) {
            $phone = substr($phone, 2);
        }
        
        // Hapus awalan 0 jika ada
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }
    
    /**
     * Format nomor WhatsApp untuk URL/deep link
     * 
     * @param string $phone
     * @return string
     */
    public static function formatWhatsAppNumber(string $phone)
    {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/\D/', '', $phone);
        
        // Hapus awalan 0 jika ada
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Jika belum ada kode negara, tambahkan 62 (Indonesia)
        if (substr($phone, 0, 2) != '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
} 