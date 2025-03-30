<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\Role;
use App\Models\User;
use App\Models\Workday;
use App\Models\Setting;
use Database\Seeders\WorkdaySeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat role
        $adminRole = Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Guru']);
        Role::create(['name' => 'Staf TU']);

        // Membuat user admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);
        // Membuat user guru
        User::factory()->create([
            'name' => 'Huda',
            'email' => 'huda@example.com',
            'role_id' => '2',
        ]);
        // Membuat user staf tu
        User::factory()->create([
            'name' => 'danang',
            'email' => 'danang@example.com',
            'role_id' => '3',
        ]);

        // Membuat hari kerja default (Senin-Jumat)
        $workdays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        foreach ($workdays as $day) {
            Workday::create([
                'day' => $day,
                'is_active' => true,
            ]);
        }
        
        // Membuat hari Sabtu dan Minggu sebagai hari non-kerja
        Workday::create([
            'day' => 'Sabtu',
            'is_active' => false,
        ]);
        
        Workday::create([
            'day' => 'Minggu',
            'is_active' => false,
        ]);
        
        // Membuat hari libur nasional tahun 2025
        $holidays = [
            [
                'name' => 'Tahun Baru 2025',
                'date' => '2025-01-01',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Tahun Baru Imlek 2576 Kongzili',
                'date' => '2025-01-29',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Isra Mikraj Nabi Muhammad SAW',
                'date' => '2025-02-19',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Raya Nyepi Tahun Baru Saka 1947',
                'date' => '2025-03-28',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Wafat Isa Al Masih',
                'date' => '2025-04-18',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Buruh Internasional',
                'date' => '2025-05-01',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Raya Idul Fitri 1446 Hijriah',
                'date' => '2025-05-22',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Raya Idul Fitri 1446 Hijriah',
                'date' => '2025-05-23',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Lahir Pancasila',
                'date' => '2025-06-01',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Raya Waisak 2569 BE',
                'date' => '2025-06-12',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Raya Idul Adha 1446 Hijriah',
                'date' => '2025-07-29',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Kemerdekaan Republik Indonesia',
                'date' => '2025-08-17',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Maulid Nabi Muhammad SAW',
                'date' => '2025-09-16',
                'description' => 'Hari Libur Nasional',
            ],
            [
                'name' => 'Hari Natal',
                'date' => '2025-12-25',
                'description' => 'Hari Libur Nasional',
            ],
        ];
        
        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        // Seed pengaturan jam kerja
        $settings = [
            [
                'key' => 'check_in_time',
                'value' => '08:00',
                'description' => 'Jam masuk kerja'
            ],
            [
                'key' => 'check_out_time',
                'value' => '16:00',
                'description' => 'Jam pulang kerja'
            ],
            [
                'key' => 'late_threshold',
                'value' => '15',
                'description' => 'Batas keterlambatan dalam menit'
            ],
            [
                'key' => 'early_leave_threshold',
                'value' => '15',
                'description' => 'Batas pulang cepat dalam menit'
            ],
            [
                'key' => 'default_radius',
                'value' => '100',
                'description' => 'Radius default untuk lokasi absensi dalam meter'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // Seed hari kerja
        $this->call(WorkdaySeeder::class);
    }
}