<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.fonnte.com';

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
        
        // Ambil semua user yang punya nomor telepon dan ingin menerima notifikasi
        $query = User::whereNotNull('phone')
            ->where('wa_notifications', true);
            
        // Tambahkan filter jika ada
        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }
        
        $users = $query->get();
        
        // Jika tidak ada user yang memenuhi kriteria
        if ($users->isEmpty()) {
            Log::info('No users found for broadcast message');
            return [];
        }
        
        // Ambil semua nomor telepon
        $phones = $users->pluck('phone')->toArray();
        
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
                    'countryCode' => '62', // Kode negara Indonesia
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
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey
            ])->post($this->baseUrl . '/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62', // Kode negara Indonesia
            ]);

            $result = $response->json();

            // Log hasil request
            if ($response->successful()) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $phone,
                    'status' => $result['status'] ?? 'unknown'
                ]);
            } else {
                Log::error('Failed to send WhatsApp notification', [
                    'phone' => $phone,
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
} 