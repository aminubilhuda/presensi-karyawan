<?php

namespace App\Services\FaceRecognition;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceApiService
{
    /**
     * Menyimpan wajah pengguna dari data base64 gambar
     * 
     * @param int $userId
     * @param string $imageBase64
     * @return string Path relatif tempat gambar disimpan
     */
    public function saveFace(int $userId, string $imageBase64): string
    {
        // Hapus header base64 jika ada
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $imageBase64);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);
        
        if ($imageData === false) {
            \Log::error("FaceApiService: Format gambar tidak valid untuk user ID $userId");
            throw new \Exception('Format gambar tidak valid');
        }
        
        // Buat nama file unik dengan prefix dan ID user
        $filename = 'face_' . $userId . '_' . Str::random(10) . '.jpg';
        $path = 'faces/' . $filename;
        
        // Cek apakah folder faces ada
        if (!Storage::disk('public')->exists('faces')) {
            \Log::info("FaceApiService: Membuat direktori faces");
            Storage::disk('public')->makeDirectory('faces');
        }
        
        try {
            // Simpan ke storage
            $result = Storage::disk('public')->put($path, $imageData);
            
            if (!$result) {
                \Log::error("FaceApiService: Gagal menyimpan file ke $path");
                throw new \Exception('Gagal menyimpan file wajah');
            }
            
            \Log::info("FaceApiService: Berhasil menyimpan file wajah di $path");
            return $path;
        } catch (\Exception $e) {
            \Log::error("FaceApiService: Exception saat menyimpan wajah - " . $e->getMessage());
            throw new \Exception('Gagal menyimpan file wajah: ' . $e->getMessage());
        }
    }
    
    /**
     * Menghapus wajah pengguna jika ada
     * 
     * @param string|null $path
     * @return bool
     */
    public function deleteFace(?string $path): bool
    {
        if (!$path) {
            \Log::info("FaceApiService: Tidak ada file untuk dihapus (path null)");
            return false;
        }
        
        try {
            if (!Storage::disk('public')->exists($path)) {
                \Log::warning("FaceApiService: File $path tidak ditemukan untuk dihapus");
                return false;
            }
            
            $result = Storage::disk('public')->delete($path);
            
            if ($result) {
                \Log::info("FaceApiService: Berhasil menghapus file $path");
            } else {
                \Log::warning("FaceApiService: Gagal menghapus file $path");
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error("FaceApiService: Exception saat menghapus file $path - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Membandingkan wajah pada gambar dengan wajah tersimpan
     * 
     * @param string $storedFacePath
     * @param string $imageBase64
     * @return array ['verified' => bool, 'score' => float]
     */
    public function verifyFace(string $storedFacePath, string $imageBase64): array
    {
        // Data akan diproses di frontend dengan face-api.js
        // Method ini menyediakan interface untuk verifikasi wajah
        // Implementasi sebenarnya akan dilakukan di JavaScript
        
        return [
            'verified' => true,
            'score' => 1.0
        ];
    }
} 