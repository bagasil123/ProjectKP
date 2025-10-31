<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ts_div')->insert([

            [
                'div_auto'      => '1',
                'Div_Code'      => '001',
                'Div_Name'      => 'Divisi Direksi',
                'DIV_SHIFTYN'   => 'T',
                'Div_EntryID'   => '1',
                'Div_Entrydate' => now(),
            ],
            [
                'div_auto'      => '2',
                'Div_Code'      => '002',
                'Div_Name'      => 'Divisi Opersional',
                'DIV_SHIFTYN'   => 'T',
                'Div_EntryID'   => '1',
                'Div_Entrydate' => now(),
            ],
            [
                'div_auto'      => '3',
                'Div_Code'      => '003',
                'Div_Name'      => 'Divisi Keuangan & Administrasi',
                'DIV_SHIFTYN'   => 'T',
                'Div_EntryID'   => '1',
                'Div_Entrydate' => now(),
            ],
            [
                'div_auto'      => '4',
                'Div_Code'      => '004',
                'Div_Name'      => 'Divisi Teknologi Informasi',
                'DIV_SHIFTYN'   => 'T',
                'Div_EntryID'   => '1',
                'Div_Entrydate' => now(),
            ],
            [
                'div_auto'      => '5',
                'Div_Code'      => '005',
                'Div_Name'      => 'Divisi Penjualan & Pemasaran',
                'DIV_SHIFTYN'   => 'T',
                'Div_EntryID'   => '1',
                'Div_Entrydate' => now(),
            ],
        ]);
    }
}
