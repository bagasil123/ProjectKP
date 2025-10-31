<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ts_position')->insert([

            [
                'pos_auto'      => '1',
                'Pos_Code'   => 'SA',
                'Pos_Name'      => 'Sales Asistant',
            ],
            [
                'pos_auto'      => '2',
                'Pos_Code'   => 'SPV',
                'Pos_Name'      => 'Supervisor',
            ],
            [
                'pos_auto'      => '3',
                'Pos_Code'   => 'ST',
                'Pos_Name'      => 'Staff',
            ],
            [
                'pos_auto'      => '4',
                'Pos_Code'   => 'MGR',
                'Pos_Name'      => 'Manager',
            ],
            [
                'pos_auto'      => '5',
                'Pos_Code'   => 'ASS',
                'Pos_Name'      => 'Asst. Manager',
            ],
            [
                'pos_auto'      => '6',
                'Pos_Code'   => 'SCO',
                'Pos_Name'      => 'Staff Counter',
            ],
        ]);
    }
}
