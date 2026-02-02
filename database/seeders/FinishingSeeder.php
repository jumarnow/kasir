<?php

namespace Database\Seeders;

use App\Models\Finishing;
use Illuminate\Database\Seeder;

class FinishingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $finishings = [
            [
                'name' => 'Laminasi Glossy',
                'code' => 'LAM-GLOSSY',
                'description' => 'Laminasi mengkilap',
                'pricing_type' => 'per_dimension',
                'price' => 5000,
            ],
            [
                'name' => 'Laminasi Doff',
                'code' => 'LAM-DOFF',
                'description' => 'Laminasi tidak mengkilap/matte',
                'pricing_type' => 'per_dimension',
                'price' => 5000,
            ],
            [
                'name' => 'Cutting Sticker',
                'code' => 'CUTTING',
                'description' => 'Potong stiker sesuai bentuk',
                'pricing_type' => 'per_unit',
                'price' => 2000,
            ],
            [
                'name' => 'Mounting',
                'code' => 'MOUNTING',
                'description' => 'Pemasangan pada papan/akrilik',
                'pricing_type' => 'per_dimension',
                'price' => 10000,
            ],
            [
                'name' => 'Lem/Pasang',
                'code' => 'LEM-PASANG',
                'description' => 'Pemasangan/penempelan',
                'pricing_type' => 'per_dimension',
                'price' => 8000,
            ],
            [
                'name' => 'Mata Ayam',
                'code' => 'MATA-AYAM',
                'description' => 'Pemasangan mata ayam untuk banner',
                'pricing_type' => 'per_unit',
                'price' => 500,
            ],
            [
                'name' => 'Jahit Tepi',
                'code' => 'JAHIT-TEPI',
                'description' => 'Jahitan tepi untuk banner/spanduk',
                'pricing_type' => 'per_meter',
                'price' => 3000,
            ],
            [
                'name' => 'Lipat',
                'code' => 'LIPAT',
                'description' => 'Lipat brosur/leaflet',
                'pricing_type' => 'per_unit',
                'price' => 200,
            ],
        ];

        foreach ($finishings as $finishing) {
            Finishing::firstOrCreate(
                ['code' => $finishing['code']],
                $finishing
            );
        }
    }
}
