<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akuntansi\AccClass;

class AccClassSeeder extends Seeder
{
    public function run()
    {
        $classes = [
            ['cls_id' => 1, 'cls_ina' => 'Harta'],
            ['cls_id' => 2, 'cls_ina' => 'Kewajiban'],
            ['cls_id' => 3, 'cls_ina' => 'Modal'],
            ['cls_id' => 4, 'cls_ina' => 'Pendapatan'],
            ['cls_id' => 5, 'cls_ina' => 'Harga Pokok Penjualan'],
            ['cls_id' => 6, 'cls_ina' => 'Pengeluaran Operasional'],
            ['cls_id' => 7, 'cls_ina' => 'Pengeluaran Non Operasional'],
            ['cls_id' => 8, 'cls_ina' => 'Pendapatan Lain'],
            ['cls_id' => 9, 'cls_ina' => 'Pengeluaran Lain'],
        ];

        foreach ($classes as $class) {
            AccClass::create($class);
        }
    }
}
