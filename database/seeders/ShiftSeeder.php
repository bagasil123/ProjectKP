<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Presensi\Shift; // Pastikan path model benar

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama untuk menghindari duplikasi
        DB::table('m_shift')->truncate();

        $shifts = [
            ['shift_code' => 'P1', 'shift_name' => 'Pagi 1', 'jam_in' => '08:00:00', 'jam_out' => '17:00:00'],
            ['shift_code' => 'P2', 'shift_name' => 'Pagi 2', 'jam_in' => '09:00:00', 'jam_out' => '18:00:00'],
            ['shift_code' => 'S1', 'shift_name' => 'Siang 1', 'jam_in' => '14:00:00', 'jam_out' => '22:00:00'],
            ['shift_code' => 'M1', 'shift_name' => 'Malam 1', 'jam_in' => '22:00:00', 'jam_out' => '06:00:00'],
            ['shift_code' => 'L', 'shift_name' => 'Libur', 'jam_in' => '00:00:00', 'jam_out' => '00:00:00'],
            ['shift_code' => 'OFF', 'shift_name' => 'Day Off', 'jam_in' => '00:00:00', 'jam_out' => '00:00:00'],
        ];

        // Masukkan data ke tabel
        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}