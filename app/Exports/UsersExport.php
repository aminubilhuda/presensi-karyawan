<?php

namespace App\Exports;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UsersExport
{
    /**
     * Export data pengguna ke Excel
     */
    public static function download()
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul worksheet
        $sheet->setTitle('Users');
        
        // Set header
        $headers = ['ID', 'Nama', 'Email', 'Username', 'Nomor Telepon', 'Notifikasi WA', 'Peran', 'Terdaftar Pada'];
        $sheet->fromArray([$headers], null, 'A1');
        
        // Format header (bold dan background color)
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
        
        // Get data pengguna
        $users = User::with('role')->get();
        
        // Data array untuk rows
        $userData = [];
        
        foreach ($users as $user) {
            $userData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->username,
                $user->phone,
                $user->wa_notifications ? 'Ya' : 'Tidak',
                $user->role->name,
                $user->created_at->format('d-m-Y H:i:s')
            ];
        }
        
        // Tambahkan data ke sheet
        if (!empty($userData)) {
            $sheet->fromArray($userData, null, 'A2');
        }
        
        // Auto-size column dimensions
        foreach(range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer and output to temp file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export_users_');
        $writer->save($tempFile);
        
        // Hapus file setelah didownload
        return response()->download($tempFile, 'users-' . date('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }
} 