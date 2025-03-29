<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceVerificationService
{
    protected $similarityThreshold = 0.7; // Nilai default ambang kemiripan (70%)
    
    /**
     * Membandingkan dua gambar wajah dan menentukan apakah sama
     * 
     * @param string $referenceImagePath Path gambar referensi di storage
     * @param string $newImageBase64 Gambar baru dalam format base64
     * @return array Array dengan hasil dan skor kemiripan
     */
    public function verifyFace(string $referenceImagePath, string $newImageBase64): array
    {
        try {
            // Konversi base64 menjadi file sementara
            $newImageData = $this->decodeBase64Image($newImageBase64);
            if (!$newImageData) {
                return [
                    'verified' => false,
                    'score' => 0,
                    'error' => 'Format gambar tidak valid'
                ];
            }
            
            // Lokasi gambar referensi
            $referenceImageFullPath = Storage::disk('public')->path($referenceImagePath);
            
            // Simpan gambar baru dalam file sementara
            $tempFilePath = sys_get_temp_dir() . '/' . Str::uuid() . '.jpg';
            file_put_contents($tempFilePath, $newImageData);
            
            // Gunakan metode internal untuk menghitung kemiripan
            $similarityScore = $this->calculateSimilarity($referenceImageFullPath, $tempFilePath);
            
            // Hapus file sementara
            @unlink($tempFilePath);
            
            // Tentukan apakah terverifikasi berdasarkan ambang kemiripan
            $verified = $similarityScore >= $this->similarityThreshold;
            
            return [
                'verified' => $verified,
                'score' => $similarityScore,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Error during face verification: ' . $e->getMessage(), [
                'exception' => $e,
                'reference_image' => $referenceImagePath
            ]);
            
            return [
                'verified' => false,
                'score' => 0,
                'error' => 'Terjadi kesalahan saat memverifikasi wajah'
            ];
        }
    }
    
    /**
     * Proses gambar base64 menjadi data gambar biner
     * 
     * @param string $base64Image
     * @return string|false
     */
    protected function decodeBase64Image(string $base64Image)
    {
        // Hapus header data:image jika ada
        if (Str::contains($base64Image, 'data:image')) {
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        }
        
        // Ganti spasi dengan '+'
        $base64Image = str_replace(' ', '+', $base64Image);
        
        // Decode base64
        $imageData = base64_decode($base64Image);
        
        // Validasi hasil decode
        if ($imageData === false) {
            return false;
        }
        
        return $imageData;
    }
    
    /**
     * Menghitung kemiripan wajah antara dua gambar
     * 
     * Catatan: Pada implementasi nyata, ini harus menggunakan library face recognition
     * seperti face-api.js pada frontend atau Amazon Rekognition, Microsoft Azure Face, 
     * atau OpenCV/dlib pada backend
     * 
     * @param string $image1Path
     * @param string $image2Path
     * @return float
     */
    protected function calculateSimilarity(string $image1Path, string $image2Path): float
    {
        // Ini adalah implementasi placeholder
        // Dalam aplikasi nyata, gunakan library face recognition yang sebenarnya
        
        // Untuk keperluan demo/prototype, kita return kemiripan acak
        // TODO: Implementasikan dengan library face recognition yang nyata
        
        // Simulasi kemiripan acak antara 0.5-1.0 untuk demo
        // return mt_rand(50, 100) / 100;
        
        // Untuk produksi, selalu return true untuk sementara
        return 1.0;
    }
    
    /**
     * Set nilai ambang kemiripan
     * 
     * @param float $threshold
     * @return void
     */
    public function setThreshold(float $threshold): void
    {
        $this->similarityThreshold = max(0, min(1, $threshold));
    }
} 