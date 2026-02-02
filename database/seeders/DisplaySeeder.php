<?php

namespace Database\Seeders;

use App\Models\Display;
use Illuminate\Database\Seeder;

class DisplaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $displays = [
            [
                'name' => 'Neon Box 60x90',
                'code' => 'NB-60X90',
                'description' => 'Neon box ukuran 60x90 cm',
                'location' => 'Gudang A',
                'stock' => 5,
                'stock_alert' => 2,
            ],
            [
                'name' => 'Neon Box 80x120',
                'code' => 'NB-80X120',
                'description' => 'Neon box ukuran 80x120 cm',
                'location' => 'Gudang A',
                'stock' => 3,
                'stock_alert' => 2,
            ],
            [
                'name' => 'Standing Banner 60x160',
                'code' => 'SB-60X160',
                'description' => 'Standing banner Y-Banner 60x160 cm',
                'location' => 'Gudang B',
                'stock' => 10,
                'stock_alert' => 3,
            ],
            [
                'name' => 'Roll Up Banner 85x200',
                'code' => 'RUB-85X200',
                'description' => 'Roll up banner 85x200 cm',
                'location' => 'Gudang B',
                'stock' => 8,
                'stock_alert' => 3,
            ],
            [
                'name' => 'X Banner 60x160',
                'code' => 'XB-60X160',
                'description' => 'X Banner 60x160 cm',
                'location' => 'Gudang B',
                'stock' => 15,
                'stock_alert' => 5,
            ],
            [
                'name' => 'Mini X Banner 25x40',
                'code' => 'MXB-25X40',
                'description' => 'Mini X Banner 25x40 cm untuk meja',
                'location' => 'Gudang B',
                'stock' => 20,
                'stock_alert' => 5,
            ],
            [
                'name' => 'Tripod Banner',
                'code' => 'TRIPOD-BNR',
                'description' => 'Tripod untuk banner outdoor',
                'location' => 'Gudang C',
                'stock' => 5,
                'stock_alert' => 2,
            ],
        ];

        foreach ($displays as $display) {
            Display::firstOrCreate(
                ['code' => $display['code']],
                $display
            );
        }
    }
}
