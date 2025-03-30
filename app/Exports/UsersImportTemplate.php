<?php

namespace App\Exports;

use App\Models\Role;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UsersImportTemplate
{
    /**
     * Download template Excel untuk import user
     */
    public static function download()
    {
        // Buat template XLSX
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul worksheet dan properti dokumen
        $sheet->setTitle('Template Import');
        $spreadsheet->getProperties()
            ->setCreator('Presensi Karyawan')
            ->setLastModifiedBy('Presensi Karyawan')
            ->setTitle('Template Import Pengguna')
            ->setSubject('Template Import Pengguna')
            ->setDescription('Template untuk mengimpor data pengguna ke sistem Presensi Karyawan')
            ->setKeywords('template import pengguna')
            ->setCategory('Template');
        
        // Tambahkan instruksi di bagian atas
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT PENGGUNA');
        $sheet->mergeCells('A1:G1');

        $sheet->setCellValue('A2', 'Petunjuk: Isi data sesuai format, kolom bertanda * wajib diisi');
        $sheet->mergeCells('A2:G2');
        
        // Format judul
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Format petunjuk
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2:G2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Dapatkan daftar peran yang tersedia
        $availableRoles = [];
        try {
            $roles = Role::pluck('name')->toArray();
            $availableRoles = implode(', ', $roles);
        } catch (\Exception $e) {
            $availableRoles = 'Admin, Karyawan, Manager';
        }
        
        // Tambahkan informasi tentang peran yang tersedia
        $sheet->setCellValue('A3', 'Peran yang tersedia: ' . $availableRoles);
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Set header pada baris ke-4
        $headers = ['Nama*', 'Email*', 'Username', 'Password*', 'Peran*', 'Nomor Telepon', 'Notifikasi WA (Ya/Tidak)'];
        $sheet->fromArray([$headers], null, 'A4');
        
        // Tambahkan keterangan di bawah header
        $descriptions = [
            'Nama lengkap',
            'Email (harus unik)',
            'Username (opsional)',
            'Min. 8 karakter',
            'Sesuai daftar di atas',
            'Format: 08xxx',
            'Isi: Ya atau Tidak'
        ];
        $sheet->fromArray([$descriptions], null, 'A5');
        
        // Data contoh mulai dari baris ke-6
        $exampleData = [
            ['John Doe', 'john.doe@example.com', 'johndoe', 'rahasia123', 'Karyawan', '08123456789', 'Ya'],
            ['Jane Smith', 'jane.smith@example.com', 'janesmith', 'rahasia456', 'Manager', '08198765432', 'Tidak']
        ];
        $sheet->fromArray($exampleData, null, 'A6');
        
        // Format header (baris ke-4)
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A4:G4')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Format keterangan (baris ke-5)
        $sheet->getStyle('A5:G5')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
        
        // Format data contoh
        $sheet->getStyle('A6:G7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9EDF7');
        
        // Tambahkan border
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '808080']
                ]
            ]
        ];
        $sheet->getStyle('A4:G7')->applyFromArray($borderStyle);
        
        // Auto-width columns
        foreach(range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Tambahkan kolom kosong untuk diisi pengguna
        $sheet->setCellValue('A8', ''); // Baris kosong untuk memisahkan dari contoh
        
        // Baris instruksi untuk data aktual
        $sheet->setCellValue('A9', 'DATA PENGGUNA (ISI DI BAWAH)');
        $sheet->mergeCells('A9:G9');
        $sheet->getStyle('A9')->getFont()->setBold(true);
        $sheet->getStyle('A9:G9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCC00');
        $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Baris 10 untuk mulai mengisi data
        $sheet->fromArray([['', '', '', '', '', '', '']], null, 'A10');
        
        // Tambahkan dropdown "Ya/Tidak" untuk kolom Notifikasi WA
        for ($i = 10; $i <= 20; $i++) {
            $validation = $sheet->getCell('G' . $i)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"Ya,Tidak"');
            $validation->setErrorTitle('Input error');
            $validation->setError('Nilai harus Ya atau Tidak.');
            $validation->setPromptTitle('Pilih nilai');
            $validation->setPrompt('Pilih Ya atau Tidak');
        }
        
        // Create writer and output to temp file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'template');
        $writer->save($tempFile);
        
        // Return as download
        return response()->download($tempFile, 'template-import-users.xlsx')->deleteFileAfterSend(true);
    }
} 