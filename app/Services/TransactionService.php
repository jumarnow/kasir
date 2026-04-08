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
                $product = null;
                $isCustom = empty($item['product_id']) && !empty($item['custom_name']);

                if (!$isCustom) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => ["Stok {$product->name} tidak mencukupi."],
                        ]);
                    }
                }

                return [
                    'product' => $product,
                    'is_custom' => $isCustom,
                    'custom_name' => $item['custom_name'] ?? null,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'cost_price' => isset($item['cost_price']) ? (float) $item['cost_price'] : (float) ($product->cost_price ?? $item['price']),
                    'finishing_id' => $item['finishing_id'] ?? null,
                    'material_id' => $item['material_id'] ?? null,
                    'display_id' => $item['display_id'] ?? null,
                    'width' => isset($item['width']) ? (float) $item['width'] : 0,
                    'length' => isset($item['length']) ? (float) $item['length'] : 0,
                    'notes' => $item['notes'] ?? null,
                ];
            });

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['Minimal satu item transaksi diperlukan.'],
                ]);
            }

            $discountPercent = (float) Arr::get($payload, 'discount_percent', 0);
            $explicitDiscount = (float) Arr::get($payload, 'discount_amount', 0);
            $shippingCost = (float) Arr::get($payload, 'shipping_cost', 0);

            $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);

            $percentDiscountValue = $subtotal * ($discountPercent / 100);
            $discountAmount = min($subtotal, $explicitDiscount + $percentDiscountValue);

            $total = max($subtotal - $discountAmount + $shippingCost, 0);
            $amountPaid = (float) Arr::get($payload, 'amount_paid', $total);

            $amountPaid = (float) Arr::get($payload, 'amount_paid', $total);
            $paymentMethod = Arr::get($payload, 'payment_method', 'cash');

            // Allow partial payment only for DP, Pending, or COD Kurir
            if ($amountPaid < $total && !in_array($paymentMethod, ['dp', 'pending', 'tempo', 'cod_kurir'])) {
                throw ValidationException::withMessages([
                    'amount_paid' => ['Jumlah pembayaran tidak boleh kurang dari total (kecuali DP, Pending, atau COD Kurir).'],
                ]);
            }

            // Determine status based on payment
            $status = 'completed';
            if ($amountPaid < $total) {
                $status = 'partial'; // or 'pending' depending on your business logic
            }
            if (in_array($paymentMethod, ['pending', 'cod_kurir'])) {
                $status = 'pending';
            }


            $profit = $items->sum(function ($item) {
                return ($item['price'] - $item['cost_price']) * $item['quantity'];
            }) - $discountAmount;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'customer_id' => Arr::get($payload, 'customer_id'),
                'eksekutor_id' => Arr::get($payload, 'eksekutor_id'),
                'eksekutor_2_id' => Arr::get($payload, 'eksekutor_2_id'),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'shipping_cost' => $shippingCost,
                'delivery_method' => Arr::get($payload, 'delivery_method'),
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_due' => $amountPaid - $total,
                'profit' => $profit,
                'payment_method' => Arr::get($payload, 'payment_method', 'cash'),
                'status' => Arr::get($payload, 'status', 'completed'),
                'due_date' => Arr::get($payload, 'due_date'),
                'notes' => Arr::get($payload, 'notes'),
            ]);

            $items->each(function ($item) use ($transaction) {
                /** @var Product|null $product */
                $product = $item['product'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product?->id,
                    'custom_name' => $item['custom_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'finishing_id' => $item['finishing_id'],
                    'material_id' => $item['material_id'],
                    'display_id' => $item['display_id'],
                    'width' => $item['width'],
                    'length' => $item['length'],
                    'notes' => $item['notes'],
                ]);

                if ($product) {
                    $product->decrementStock($item['quantity']);
                }
            });

            return $transaction->load(['items.product', 'customer', 'user']);
        });
    }

    public function update(Transaction $transaction, User $user, array $payload): Transaction
    {
        return DB::transaction(function () use ($transaction, $user, $payload) {
            $items = collect($payload['items'] ?? [])->map(function ($item) {
                $product = null;
                $isCustom = empty($item['product_id']) && !empty($item['custom_name']);

                if (!$isCustom) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => ["Stok {$product->name} tidak mencukupi."],
                        ]);
                    }
                }

                return [
                    'product' => $product,
                    'is_custom' => $isCustom,
                    'custom_name' => $item['custom_name'] ?? null,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'cost_price' => isset($item['cost_price']) ? (float) $item['cost_price'] : (float) ($product->cost_price ?? $item['price']),
                    'finishing_id' => $item['finishing_id'] ?? null,
                    'material_id' => $item['material_id'] ?? null,
                    'display_id' => $item['display_id'] ?? null,
                    'width' => isset($item['width']) ? (float) $item['width'] : 0,
                    'length' => isset($item['length']) ? (float) $item['length'] : 0,
                    'notes' => $item['notes'] ?? null,
                ];
            });

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['Minimal satu item transaksi diperlukan.'],
                ]);
            }

            $discountPercent = (float) Arr::get($payload, 'discount_percent', 0);
            $explicitDiscount = (float) Arr::get($payload, 'discount_amount', 0);
            $shippingCost = (float) Arr::get($payload, 'shipping_cost', 0);

            $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);

            $percentDiscountValue = $subtotal * ($discountPercent / 100);
            $discountAmount = min($subtotal, $explicitDiscount + $percentDiscountValue);

            $total = max($subtotal - $discountAmount + $shippingCost, 0);
            $amountPaid = (float) Arr::get($payload, 'amount_paid', $transaction->amount_paid);

            $profit = $items->sum(function ($item) {
                return ($item['price'] - $item['cost_price']) * $item['quantity'];
            }) - $discountAmount;

            $transaction->update([
                'customer_id' => Arr::get($payload, 'customer_id'),
                'eksekutor_id' => Arr::get($payload, 'eksekutor_id'),
                'eksekutor_2_id' => Arr::get($payload, 'eksekutor_2_id'),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'shipping_cost' => $shippingCost,
                'delivery_method' => Arr::get($payload, 'delivery_method'),
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_due' => max(0, $amountPaid - $total),
                'profit' => $profit,
                'payment_method' => Arr::get($payload, 'payment_method', $transaction->payment_method),
                'due_date' => Arr::get($payload, 'due_date'),
                'created_at' => Arr::get($payload, 'created_at', $transaction->created_at),
                'notes' => Arr::get($payload, 'notes'),
            ]);

            $items->each(function ($item) use ($transaction) {
                /** @var Product|null $product */
                $product = $item['product'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product?->id,
                    'custom_name' => $item['custom_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'finishing_id' => $item['finishing_id'],
                    'material_id' => $item['material_id'],
                    'display_id' => $item['display_id'],
                    'width' => $item['width'],
                    'length' => $item['length'],
                    'notes' => $item['notes'],
                ]);

                if ($product) {
                    $product->decrementStock($item['quantity']);
                }
            });

            return $transaction->load(['items.product', 'customer', 'user']);
        });
    }
}
