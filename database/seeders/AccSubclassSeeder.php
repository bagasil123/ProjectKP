<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akuntansi\AccSubclass;

class AccSubclassSeeder extends Seeder
{
    public function run()
    {
        $subclasses = [
            // Harta (cls_id = 1)
            ['cls_subid' => 110, 'cls_id' => 1, 'cls_ina' => 'Kas'],
            ['cls_subid' => 120, 'cls_id' => 1, 'cls_ina' => 'Bank'],
            ['cls_subid' => 130, 'cls_id' => 1, 'cls_ina' => 'Piutang Dagang'],
            ['cls_subid' => 140, 'cls_id' => 1, 'cls_ina' => 'Persediaan'],
            ['cls_subid' => 150, 'cls_id' => 1, 'cls_ina' => 'Biaya Dibayar Dimuka'],
            ['cls_subid' => 160, 'cls_id' => 1, 'cls_ina' => 'Investasi Jangka Panjang'],
            ['cls_subid' => 170, 'cls_id' => 1, 'cls_ina' => 'Harta Tetap Berwujud'],
            ['cls_subid' => 180, 'cls_id' => 1, 'cls_ina' => 'Harta Tetap Tidak Berwujud'],
            ['cls_subid' => 190, 'cls_id' => 1, 'cls_ina' => 'Harta Lainnya'],

            // Kewajiban (cls_id = 2)
            ['cls_subid' => 210, 'cls_id' => 2, 'cls_ina' => 'Hutang Lancar'],
            ['cls_subid' => 220, 'cls_id' => 2, 'cls_ina' => 'Pendapatan yang Diterima Dimuka'],
            ['cls_subid' => 230, 'cls_id' => 2, 'cls_ina' => 'Hutang Jangka Panjang'],
            ['cls_subid' => 250, 'cls_id' => 2, 'cls_ina' => 'Hutang Lainnya'],


            // Modal (cls_id = 3)
            ['cls_subid' => 310, 'cls_id' => 3, 'cls_ina' => 'Modal'],
            ['cls_subid' => 320, 'cls_id' => 3, 'cls_ina' => 'Laba'],

            // Pendapatan (cls_id = 4)
            ['cls_subid' => 410, 'cls_id' => 4, 'cls_ina' => 'Pendapatan Usaha'],
            ['cls_subid' => 420, 'cls_id' => 4, 'cls_ina' => 'Pendapatan Lain'],

            // Biaya (cls_id = 5)
            ['cls_subid' => 510, 'cls_id' => 5, 'cls_ina' => 'Biaya Produksi'],
            ['cls_subid' => 520, 'cls_id' => 5, 'cls_ina' => 'Biaya Lain'],
        ];

        foreach ($subclasses as $subclass) {
            AccSubclass::create($subclass);
        }
    }
}
