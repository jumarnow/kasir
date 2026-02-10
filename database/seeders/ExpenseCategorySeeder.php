<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Operasional Harian', 'type' => 'daily', 'is_active' => true],
            ['name' => 'Bahan Baku', 'type' => 'material', 'is_active' => true],
            ['name' => 'Gaji Karyawan', 'type' => 'monthly', 'is_active' => true],
            ['name' => 'Utilitas', 'type' => 'monthly', 'is_active' => true],
            ['name' => 'Lainnya', 'type' => 'daily', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            \App\Models\ExpenseCategory::create($category);
        }
    }
}
