<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\keamanan\Role; // Pastikan namespace ini sesuai dengan lokasi model Role Anda

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat role 'Admin'
        Role::firstOrCreate(
            ['name' => 'Admin'], // Kondisi pencarian
            ['name' => 'Admin', 'guard_name' => 'web']  // Data untuk dibuat jika tidak ditemukan, tambahkan 'guard_name'
        );
    }
}