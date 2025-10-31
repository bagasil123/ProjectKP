<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\CaraBayar;

class CaraBayarSeeder extends Seeder
{
    public function run()
    {
        $caraBayar = [
            ['nama' => 'Tunai'],
            ['nama' => 'Kredit'],
            ['nama' => 'Konsinyasi'],
        ];

        foreach ($caraBayar as $cb) {
            CaraBayar::create($cb);
        }
    }
}