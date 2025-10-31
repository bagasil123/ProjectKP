<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LiburNasionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel terlebih dahulu
        DB::table('m_libur_nasional')->truncate();

        $holidays = [
            ['tanggal' => '2025-01-01', 'keterangan' => 'Tahun Baru Masehi 2025'],
            ['tanggal' => '2025-01-29', 'keterangan' => 'Isra Mi\'raj Nabi Muhammad SAW'],
            ['tanggal' => '2025-03-31', 'keterangan' => 'Hari Raya Idul Fitri 1446 H'],
            ['tanggal' => '2025-04-01', 'keterangan' => 'Hari Raya Idul Fitri 1446 H'],
            ['tanggal' => '2025-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2025-05-29', 'keterangan' => 'Kenaikan Isa Al Masih'],
            ['tanggal' => '2025-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2025-06-07', 'keterangan' => 'Hari Raya Idul Adha 1446 H'],
            ['tanggal' => '2025-08-17', 'keterangan' => 'Hari Kemerdekaan Republik Indonesia'],
            ['tanggal' => '2025-12-25', 'keterangan' => 'Hari Raya Natal'],
            // Tambahkan data libur lainnya di sini
        ];

        // Masukkan data ke dalam tabel
        DB::table('m_libur_nasional')->insert($holidays);
    }
}