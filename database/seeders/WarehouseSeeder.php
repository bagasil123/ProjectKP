<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('m_warehouse')->insert([
            [
                'WARE_Name' => 'Gudang Utama',
                'WARE_Address' => 'Jl. Utama No.1',
                'WARE_Phone' => '081234567890',
                'WARE_Fax' => '021-123456',
                'WARE_Email' => 'gudangutama@example.com',
                'WARE_Web' => 'www.gudangutama.com',
                'ware_note1' => 'Note satu',
                'ware_note2' => 'Note dua',
                'WARE_EntryDate' => Carbon::now(),
            ],
            [
                'WARE_Name' => 'Gudang Cabang',
                'WARE_Address' => 'Jl. Cabang No.2',
                'WARE_Phone' => '081298765432',
                'WARE_Fax' => '021-654321',
                'WARE_Email' => 'gudangcabang@example.com',
                'WARE_Web' => 'www.gudangcabang.com',
                'ware_note1' => 'Note satu cabang',
                'ware_note2' => 'Note dua cabang',
                'WARE_EntryDate' => Carbon::now(),
            ]
        ]);
    }
}
