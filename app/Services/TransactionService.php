<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function create(User $user, array $payload): Transaction
    {
        return DB::transaction(function () use ($user, $payload) {
            $items = collect($payload['items'] ?? [])->map(function ($item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stok {$product->name} tidak mencukupi."],
                    ]);
                }

                return [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'cost_price' => isset($item['cost_price']) ? (float) $item['cost_price'] : (float) $product->cost_price,
                ];
            });

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['Minimal satu item transaksi diperlukan.'],
                ]);
            }

            $discountPercent = (float) Arr::get($payload, 'discount_percent', 0);
            $explicitDiscount = (float) Arr::get($payload, 'discount_amount', 0);

            $subtotal = $items->sum(fn ($item) => $item['price'] * $item['quantity']);

            $percentDiscountValue = $subtotal * ($discountPercent / 100);
            $discountAmount = min($subtotal, $explicitDiscount + $percentDiscountValue);

            $total = max($subtotal - $discountAmount, 0);
            $amountPaid = (float) Arr::get($payload, 'amount_paid', $total);

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => ['Jumlah pembayaran tidak boleh kurang dari total.'],
                ]);
            }

            $profit = $items->sum(function ($item) {
                return ($item['price'] - $item['cost_price']) * $item['quantity'];
            }) - $discountAmount;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'customer_id' => Arr::get($payload, 'customer_id'),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_due' => $amountPaid - $total,
                'profit' => $profit,
                'payment_method' => Arr::get($payload, 'payment_method', 'cash'),
                'status' => Arr::get($payload, 'status', 'completed'),
                'notes' => Arr::get($payload, 'notes'),
            ]);

            $items->each(function ($item) use ($transaction) {
                /** @var Product $product */
                $product = $item['product'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                ]);

                $product->decrementStock($item['quantity']);
            });

            return $transaction->load(['items.product', 'customer', 'user']);
        });
    }
}
