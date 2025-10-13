<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_transaction_and_updates_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Air Mineral 1L',
            'sku' => 'SKU-12345',
            'barcode' => '1234567890123',
            'unit' => 'bottle',
            'price' => 10000,
            'cost_price' => 6000,
            'stock' => 10,
            'stock_alert' => 2,
        ]);

        $service = app(TransactionService::class);

        $transaction = $service->create($user, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 10000,
                ],
            ],
            'amount_paid' => 25000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'payment_method' => 'cash',
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertSame(20000.0, (float) $transaction->subtotal);
        $this->assertSame(20000.0, (float) $transaction->total);
        $this->assertSame(5000.0, (float) $transaction->change_due);
        $this->assertEquals(1, $transaction->items()->count());
        $this->assertSame(8, $product->fresh()->stock);
    }
}
