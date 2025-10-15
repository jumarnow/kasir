<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'username' => 'kasir',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'kasir',
            'password' => 'secret123',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'username',
                ],
            ]);
    }

    public function test_authenticated_user_can_create_transaction(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::create([
            'name' => 'Produk A',
            'barcode' => '1234567890123',
            'unit' => 'pcs',
            'price' => 15000,
            'cost_price' => 10000,
            'stock' => 5,
            'stock_alert' => 1,
        ]);

        $customer = Customer::create([
            'name' => 'Pelanggan 1',
            'email' => 'pelanggan@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Jakarta',
            'state' => 'DKI Jakarta',
            'postal_code' => '12345',
            'is_active' => true,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 15000,
                ],
            ],
            'amount_paid' => 40000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'payment_method' => 'cash',
        ];

        $response = $this->postJson('/api/transactions', $payload);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Transaksi berhasil dibuat.',
            ])
            ->assertJsonPath('data.total', 30000);

        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total' => '30000.00',
        ]);

        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_authenticated_user_can_list_transactions(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::create([
            'name' => 'Produk B',
            'barcode' => '9876543210987',
            'unit' => 'pcs',
            'price' => 10000,
            'cost_price' => 8000,
            'stock' => 10,
            'stock_alert' => 2,
        ]);

        /** @var TransactionService $transactionService */
        $transactionService = app(TransactionService::class);

        $transactionService->create($user, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 10000,
                ],
            ],
            'amount_paid' => 10000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'payment_method' => 'cash',
        ]);

        $response = $this->getJson('/api/transactions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'invoice_number',
                        'total',
                    ],
                ],
                'links',
                'meta',
            ]);
    }
}
