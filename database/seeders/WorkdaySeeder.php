<?php

namespace Database\Seeders;

use App\Models\Workday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkdaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang ada
        DB::table('workdays')->truncate();
        
        // Hari kerja dalam bahasa Indonesia
        $workdays = [
            ['day' => 'Senin', 'is_active' => true],
            ['day' => 'Selasa', 'is_active' => true],
            ['day' => 'Rabu', 'is_active' => true],
            ['day' => 'Kamis', 'is_active' => true],
            ['day' => 'Jumat', 'is_active' => true],
            ['day' => 'Sabtu', 'is_active' => false],
            ['day' => 'Minggu', 'is_active' => false],
        ];
        
        // Insert data
        foreach ($workdays as $workday) {
            Workday::create($workday);
        }
        
        $this->command->info('Workdays seeded successfully!');
    }
} 