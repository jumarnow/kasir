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
                'price_2' => 22500,
                'price_3' => 20000,
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
                'price_2' => 32500,
                'price_3' => 30000,
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
                'price_2' => 45000,
                'price_3' => 40000,
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
                'price_2' => 37500,
                'price_3' => 35000,
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
                'price_2' => 1200,
                'price_3' => 1000,
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
                'price_2' => 250,
                'price_3' => 200,
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
                'price_2' => 450,
                'price_3' => 400,
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
                'price_2' => 700,
                'price_3' => 600,
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
                'price_2' => 1000,
                'price_3' => 900,
                'stock' => 300,
                'stock_alert' => 50,
            ],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['code' => $material['code']],
                $material
            );
        }
    }
}
