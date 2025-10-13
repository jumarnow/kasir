<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $adminRole = Role::where('name', 'admin')->first();
            $cashierRole = Role::where('name', 'cashier')->first();

            $admin = User::firstOrCreate(
                ['email' => 'admin@kasir.test'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($adminRole) {
                $admin->roles()->syncWithoutDetaching([$adminRole->id]);
            }

            $cashier = User::firstOrCreate(
                ['email' => 'kasir@kasir.test'],
                [
                    'name' => 'Kasir Toko',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($cashierRole) {
                $cashier->roles()->syncWithoutDetaching([$cashierRole->id]);
            }

            $categories = collect([
                ['name' => 'Minuman', 'description' => 'Aneka minuman siap saji'],
                ['name' => 'Makanan', 'description' => 'Produk makanan kemasan'],
                ['name' => 'Kebutuhan Rumah', 'description' => 'Barang kebutuhan rumah tangga'],
            ])->map(function ($category) {
                return Category::firstOrCreate(
                    ['slug' => Str::slug($category['name'])],
                    [
                        'name' => $category['name'],
                        'description' => $category['description'],
                    ]
                );
            });

            $products = collect([
                [
                    'name' => 'Air Mineral 600ml',
                    'sku' => 'PRD-0001',
                    'barcode' => '899100210001',
                    'unit' => 'bottle',
                    'price' => 5000,
                    'cost_price' => 3000,
                    'stock' => 120,
                    'category' => 'Minuman',
                ],
                [
                    'name' => 'Mi Instan Ayam Bawang',
                    'sku' => 'PRD-0002',
                    'barcode' => '899100220002',
                    'unit' => 'pack',
                    'price' => 3500,
                    'cost_price' => 2000,
                    'stock' => 200,
                    'category' => 'Makanan',
                ],
                [
                    'name' => 'Sabun Cuci Piring 500ml',
                    'sku' => 'PRD-0003',
                    'barcode' => '899100230003',
                    'unit' => 'bottle',
                    'price' => 15000,
                    'cost_price' => 9000,
                    'stock' => 60,
                    'category' => 'Kebutuhan Rumah',
                ],
            ])->map(function ($product) use ($categories) {
                $categoryId = $categories->firstWhere('name', $product['category'])?->id;

                return Product::updateOrCreate(
                    ['sku' => $product['sku']],
                    [
                        'category_id' => $categoryId,
                        'name' => $product['name'],
                        'slug' => Str::slug($product['name']),
                        'barcode' => $product['barcode'],
                        'unit' => $product['unit'],
                        'price' => $product['price'],
                        'cost_price' => $product['cost_price'],
                        'stock' => $product['stock'],
                    ]
                );
            });

            $customers = collect([
                [
                    'name' => 'Budi Hartono',
                    'email' => 'budi@example.com',
                    'phone' => '081234567890',
                    'city' => 'Jakarta',
                ],
                [
                    'name' => 'Siti Aminah',
                    'email' => 'siti@example.com',
                    'phone' => '082198765432',
                    'city' => 'Bandung',
                ],
            ])->map(fn ($customer) => Customer::updateOrCreate(['email' => $customer['email']], $customer));

            $transaction = Transaction::firstOrCreate(
                ['invoice_number' => 'INV-' . now()->format('Ymd') . '-0001'],
                [
                    'user_id' => $cashier->id,
                    'customer_id' => $customers->first()?->id,
                    'subtotal' => 85000,
                    'discount_amount' => 5000,
                    'discount_percent' => 5,
                    'total' => 80000,
                    'amount_paid' => 100000,
                    'change_due' => 20000,
                    'profit' => 25000,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                ]
            );

            foreach ($products->take(2) as $index => $product) {
                $quantity = $index === 0 ? 10 : 5;

                TransactionItem::updateOrCreate(
                    [
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'cost_price' => $product->cost_price,
                        'total' => $product->price * $quantity,
                        'profit' => ($product->price - $product->cost_price) * $quantity,
                    ]
                );
            }
        });
    }
}

