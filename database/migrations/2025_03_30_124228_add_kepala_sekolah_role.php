<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan role Kepala Sekolah jika belum ada
        if (!DB::table('roles')->where('name', 'Kepala Sekolah')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Kepala Sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus role Kepala Sekolah
        DB::table('roles')->where('name', 'Kepala Sekolah')->delete();
    }
};
