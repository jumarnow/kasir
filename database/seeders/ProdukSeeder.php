<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Categories
        $catPrinting = Category::firstOrCreate(
            ['slug' => 'digital-printing'],
            ['name' => 'Digital Printing', 'description' => 'Layanan cetak banner, spanduk, stiker', 'is_active' => true]
        );

        $catDocument = Category::firstOrCreate(
            ['slug' => 'dokumen'],
            ['name' => 'Dokumen', 'description' => 'Cetak dokumen A4, A3, brosur', 'is_active' => true]
        );

        $catMerchandise = Category::firstOrCreate(
            ['slug' => 'merchandise'],
            ['name' => 'Merchandise', 'description' => 'Pin, Mug, Gantungan Kunci', 'is_active' => true]
        );

        // 2. Seed Products
        $products = [
            // Per Dimension Products
            [
                'category_id' => $catPrinting->id,
                'name' => 'Cetak Banner Flexi 280gr',
                'sku' => 'BNR-280',
                'pricing_type' => 'per_dimension',
                'unit' => 'm²',
                'price' => 20000,
                'price_per_meter' => 25000, // Harga jual per meternya
                'cost_price' => 15000,
                'stock' => 1000,
                'description' => 'Cetak spanduk bahan flexi 280gr outdoor',
            ],
            [
                'category_id' => $catPrinting->id,
                'name' => 'Cetak Banner Flexi 440gr (High Res)',
                'sku' => 'BNR-440',
                'pricing_type' => 'per_dimension',
                'unit' => 'm²',
                'price' => 30000,
                'price_per_meter' => 35000,
                'cost_price' => 22000,
                'stock' => 1000,
                'description' => 'Cetak spanduk bahan flexi 440gr high resolution',
            ],
            [
                'category_id' => $catPrinting->id,
                'name' => 'Stiker Vinyl Meteran',
                'sku' => 'STK-VIN',
                'pricing_type' => 'per_dimension',
                'unit' => 'm²',
                'price' => 80000,
                'price_per_meter' => 85000,
                'cost_price' => 50000,
                'stock' => 500,
                'description' => 'Cetak stiker vinyl outdoor tahan air',
            ],

            // Per Unit Products
            [
                'category_id' => $catDocument->id,
                'name' => 'Cetak A3+ Art Paper 260gr',
                'sku' => 'PRT-A3-AP260',
                'pricing_type' => 'per_unit',
                'unit' => 'lembar',
                'price' => 5000,
                'cost_price' => 2500,
                'stock' => 1000,
                'description' => 'Cetak A3+ full color bahan Art Paper 260gr',
            ],
            [
                'category_id' => $catDocument->id,
                'name' => 'Kartu Nama 1 Box',
                'sku' => 'KN-1BOX',
                'pricing_type' => 'per_unit',
                'unit' => 'box',
                'price' => 35000,
                'cost_price' => 15000,
                'stock' => 100,
                'description' => 'Cetak kartu nama 1 box isi 100 lembar + box',
            ],
            [
                'category_id' => $catMerchandise->id,
                'name' => 'Mug Custom',
                'sku' => 'MG-CST',
                'pricing_type' => 'per_unit',
                'unit' => 'pcs',
                'price' => 25000,
                'cost_price' => 15000,
                'stock' => 50,
                'description' => 'Cetak mug custom full color',
            ],
            [
                'category_id' => $catMerchandise->id,
                'name' => 'X-Banner 60x160 (Lengkap)',
                'sku' => 'XB-60160',
                'pricing_type' => 'per_unit',
                'unit' => 'set',
                'price' => 85000,
                'cost_price' => 55000,
                'stock' => 20,
                'description' => 'Paket X-Banner ukuran 60x160cm (Tiang + Cetak)',
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );
        }
    }
}

