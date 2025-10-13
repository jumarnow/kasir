<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'total',
        'amount_paid',
        'change_due',
        'profit',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = now()->format('Ymd');
        $latestNumber = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = 1;

        if ($latestNumber && str_starts_with($latestNumber, 'INV-' . $prefix)) {
            $sequence = (int) Str::of($latestNumber)->afterLast('-') + 1;
        }

        return sprintf('INV-%s-%04d', $prefix, $sequence);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        return $query;
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
