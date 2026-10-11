<?php

namespace Database\Seeders;

use App\Models\Satuan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Seeder ini HANYA untuk instalasi baru yang kosong (tanpa data lama).
    // Kalau mau memakai data dari database lama, JANGAN jalankan seeder ini.
    // Jalankan: php artisan import:lama
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'bos',
        ]);

        Satuan::create(['kode' => 'PCS', 'nama' => 'PCS']);
    }
}
