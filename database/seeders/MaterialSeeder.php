<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            [
                'name' => 'Flexi 280gr',
                'code' => 'FLEXI-280',
                'description' => 'Bahan banner/spanduk ketebalan 280 gram',
                'unit' => 'm²',
                'cost_price' => 15000,
                'selling_price' => 25000,
                'stock' => 100,
                'stock_alert' => 20,
            ],
            [
                'name' => 'Flexi 440gr',
                'code' => 'FLEXI-440',
                'description' => 'Bahan banner/spanduk ketebalan 440 gram',
                'unit' => 'm²',
                'cost_price' => 20000,
                'selling_price' => 35000,
                'stock' => 100,
                'stock_alert' => 20,
            ],
            [
                'name' => 'Vinyl Outdoor',
                'code' => 'VINYL-OUT',
                'description' => 'Stiker vinyl untuk outdoor',
                'unit' => 'm²',
                'cost_price' => 30000,
                'selling_price' => 50000,
                'stock' => 50,
                'stock_alert' => 10,
            ],
            [
                'name' => 'Vinyl Indoor',
                'code' => 'VINYL-IN',
                'description' => 'Stiker vinyl untuk indoor',
                'unit' => 'm²',
                'cost_price' => 25000,
                'selling_price' => 40000,
                'stock' => 50,
                'stock_alert' => 10,
            ],
            [
                'name' => 'Sticker Chromo',
                'code' => 'STICKER-CHROMO',
                'description' => 'Stiker chromo untuk label',
                'unit' => 'lembar',
                'cost_price' => 500,
                'selling_price' => 1500,
                'stock' => 500,
                'stock_alert' => 100,
            ],
            [
                'name' => 'Kertas HVS A4 70gr',
                'code' => 'HVS-A4-70',
                'description' => 'Kertas HVS A4 70 gram',
                'unit' => 'lembar',
                'cost_price' => 100,
                'selling_price' => 300,
                'stock' => 1000,
                'stock_alert' => 200,
            ],
            [
                'name' => 'Kertas HVS A3 70gr',
                'code' => 'HVS-A3-70',
                'description' => 'Kertas HVS A3 70 gram',
                'unit' => 'lembar',
                'cost_price' => 200,
                'selling_price' => 500,
                'stock' => 500,
                'stock_alert' => 100,
            ],
            [
                'name' => 'Art Paper 120gr',
                'code' => 'ART-120',
                'description' => 'Kertas art paper 120 gram untuk brosur',
                'unit' => 'lembar',
                'cost_price' => 300,
                'selling_price' => 800,
                'stock' => 500,
                'stock_alert' => 100,
            ],
            [
                'name' => 'Art Carton 260gr',
                'code' => 'ARTC-260',
                'description' => 'Kertas art carton 260 gram untuk kartu nama',
                'unit' => 'lembar',
                'cost_price' => 500,
                'selling_price' => 1200,
                'stock' => 300,
                'stock_alert' => 50,
            ],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(
                ['code' => $material['code']],
                $material
            );
        }
    }
}
