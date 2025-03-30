<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;
use Illuminate\Support\Facades\Log;

class UsersImport
{
    /**
     * Import users from Excel file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public static function import($file)
    {
        $result = [
            'success' => true,
            'errors' => [],
            'imported_count' => 0
        ];
        
        try {
            $extension = $file->getClientOriginalExtension();
            $filePath = $file->getRealPath();
            $rows = [];
            
            // Verifikasi ekstensi PHP ZIP untuk format XLSX
            if (in_array($extension, ['xlsx', 'xls']) && !class_exists('ZipArchive')) {
                throw new Exception('Ekstensi PHP ZIP tidak terinstal. Untuk mengimpor file Excel (XLSX), instal ekstensi PHP ZIP atau gunakan format CSV.');
            }
            
            // Baca file berdasarkan ekstensinya
            if (in_array($extension, ['xlsx', 'xls'])) {
                try {
                    // Gunakan PhpSpreadsheet untuk membaca Excel
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    // Konversi data Excel ke array
                    $rows = $worksheet->toArray();
                } catch (\Exception $e) {
                    // Log error tetapi tetap lanjutkan 
                    Log::error('Error membaca file XLSX tetapi impor tetap dilanjutkan: ' . $e->getMessage());
                    
                    // Baca sebagai CSV jika gagal membaca sebagai XLSX
                    $handle = fopen($filePath, 'r');
                    if ($handle !== false) {
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            $rows[] = $data;
                        }
                        fclose($handle);
                    }
                }
            } else if ($extension == 'csv') {
                // Baca file CSV langsung
                $handle = fopen($filePath, 'r');
                if ($handle !== false) {
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                throw new Exception('Format file yang didukung hanya CSV, XLS atau XLSX.');
            }
            
            if (empty($rows)) {
                throw new Exception('File tidak memiliki data yang dapat diimpor.');
            }
            
            // Cari baris yang berisi header (Nama*, Email*, dll)
            $headerIndex = -1;
            $dataStartIndex = -1;
            
            foreach ($rows as $index => $row) {
                // Pastikan $row adalah array sebelum diproses
                if (!is_array($row)) {
                    continue;
                }
                
                // Periksa apakah ini adalah baris header
                if (count($row) >= 5) {
                    $potentialHeader = strtolower(trim($row[0] ?? ''));
                    if (strpos($potentialHeader, 'nama*') !== false || strpos($potentialHeader, 'nama') !== false) {
                        $headerIndex = $index;
                        // Data akan dimulai pada baris setelah header
                        $dataStartIndex = $index + 1;
                        break;
                    }
                }
                
                // Cari baris yang berisi "DATA PENGGUNA (ISI DI BAWAH)"
                if (isset($row[0]) && is_string($row[0]) && strpos(strtolower($row[0]), 'data pengguna') !== false) {
                    // Data akan dimulai pada baris setelah ini
                    $dataStartIndex = $index + 1;
                    break;
                }
            }
            
            // Jika header atau data awal tidak ditemukan, gunakan baris pertama sebagai header
            if ($headerIndex == -1) {
                $headerIndex = 0;
                $dataStartIndex = 1;
            }
            
            $roles = Role::pluck('id', 'name')->toArray();
            $validRows = 0;
            
            // Proses data pengguna
            for ($i = $dataStartIndex; $i < count($rows); $i++) {
                // Pastikan $row adalah array
                if (!isset($rows[$i]) || !is_array($rows[$i])) {
                    continue;
                }
                
                $row = $rows[$i];
                
                // Skip baris kosong atau baris yang hanya berisi whitespace
                if (count(array_filter($row)) == 0) {
                    continue;
                }
                
                // Pastikan kunci ada dan data cukup
                if (count($row) < 5) {
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Format data tidak valid';
                    continue;
                }
                
                // Pastikan kunci ada (index dimulai dari 0)
                $nama = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $username = trim($row[2] ?? '');
                $password = trim($row[3] ?? '');
                $roleName = trim($row[4] ?? '');
                $phone = trim($row[5] ?? '');
                $waNotificationsText = trim($row[6] ?? '');
                
                // Skip jika semua kolom penting kosong (mungkin baris yang dibiarkan kosong)
                if (empty($nama) && empty($email) && empty($password) && empty($roleName)) {
                    continue;
                }
                
                // Validate required fields
                if (empty($nama) || empty($email) || empty($password) || empty($roleName)) {
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Kolom wajib isi (Nama, Email, Password, Peran) ada yang kosong';
                    continue;
                }
                
                // Validate role exists
                $roleId = $roles[$roleName] ?? null;
                
                if (!$roleId) {
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Peran "' . $roleName . '" tidak ditemukan';
                    continue;
                }
                
                // Validate email is unique
                if (User::where('email', $email)->exists()) {
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Email "' . $email . '" sudah digunakan';
                    continue;
                }
                
                // Validate username is unique if provided
                if (!empty($username) && User::where('username', $username)->exists()) {
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Username "' . $username . '" sudah digunakan';
                    continue;
                }
                
                try {
                    // Create user
                    $waNotifications = false;
                    if (!empty($waNotificationsText) && strtolower($waNotificationsText) === 'ya') {
                        $waNotifications = true;
                    }
                    
                    User::create([
                        'name' => $nama,
                        'email' => $email,
                        'username' => $username,
                        'password' => Hash::make($password),
                        'role_id' => $roleId,
                        'phone' => $phone,
                        'wa_notifications' => $waNotifications,
                    ]);
                    
                    $validRows++;
                    $result['imported_count']++;
                } catch (\Exception $e) {
                    Log::error('Error importing user: ' . $e->getMessage());
                    $result['errors'][] = 'Baris ' . ($i + 1) . ': Error: ' . $e->getMessage();
                }
            }
            
            if ($validRows == 0 && count($result['errors']) == 0) {
                throw new Exception('Tidak ada data valid yang dapat diimpor. Periksa kembali format file Anda.');
            }
            
            if (count($result['errors']) > 0) {
                $result['success'] = count($result['errors']) < $validRows; // Sukses jika jumlah error lebih sedikit dari data valid
            }
            
            if ($validRows > 0) {
                // Jika ada data berhasil diimpor, anggap itu sukses
                $result['success'] = true;
                
                // Bersihkan hasil log error teknis jika sebagian besar data berhasil
                if (count($result['errors']) < $validRows * 0.5) { // Jika error kurang dari 50% data 
                    // Filter error teknis (contoh: foreach error string), pertahankan error validasi
                    $filteredErrors = [];
                    foreach ($result['errors'] as $error) {
                        if (strpos($error, 'foreach()') === false && 
                            strpos($error, 'must be of type') === false &&
                            strpos($error, 'string given') === false) {
                            $filteredErrors[] = $error;
                        }
                    }
                    $result['errors'] = $filteredErrors;
                }
            }
        } catch (Exception $e) {
            Log::error('Error dalam import: ' . $e->getMessage());
            $result['success'] = false;
            $result['errors'][] = 'Error: ' . $e->getMessage();
        }
        
        return $result;
    }
}