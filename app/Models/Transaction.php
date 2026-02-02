<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    // Order Status Constants
    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_PRODUCTION = 'production';
    const ORDER_STATUS_COMPLETED = 'completed';
    const ORDER_STATUS_DELIVERED = 'delivered';

    // Payment Status Constants
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_DP = 'dp';
    const PAYMENT_STATUS_PAID = 'paid';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'shipping_cost',
        'total',
        'amount_paid',
        'change_due',
        'profit',
        'payment_method',
        'status',
        'order_status',
        'payment_status',
        'dp_amount',
        'remaining_amount',
        'due_date',
        'production_started_at',
        'completed_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due' => 'decimal:2',
        'profit' => 'decimal:2',
        'dp_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
        'production_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = self::generateInvoiceNumber();
            }
        });

        static::saving(function (Transaction $transaction) {
            // Auto-calculate remaining amount
            $transaction->remaining_amount = max(0, $transaction->total - $transaction->dp_amount - $transaction->amount_paid);

            // Auto-update payment status based on amounts
            if ($transaction->amount_paid >= $transaction->total) {
                $transaction->payment_status = self::PAYMENT_STATUS_PAID;
            } elseif ($transaction->dp_amount > 0 || $transaction->amount_paid > 0) {
                $transaction->payment_status = self::PAYMENT_STATUS_DP;
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
            $latestSequence = Str::afterLast($latestNumber, '-');
            $sequence = ((int) $latestSequence) + 1;
        }

        return sprintf('INV-%s-%04d', $prefix, $sequence);
    }

    // Relationships
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

    public function files()
    {
        return $this->hasMany(OrderFile::class);
    }

    // Scopes
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

    public function scopeOrderStatus($query, string $status)
    {
        return $query->where('order_status', $status);
    }

    public function scopePaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_UNPAID);
    }

    public function scopeHasDebt($query)
    {
        return $query->whereIn('payment_status', [self::PAYMENT_STATUS_UNPAID, self::PAYMENT_STATUS_DP]);
    }

    // Accessors
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    // Status Workflow Methods
    public function startProduction(): void
    {
        $this->order_status = self::ORDER_STATUS_PRODUCTION;
        $this->production_started_at = now();
        $this->save();
    }

    public function markCompleted(): void
    {
        $this->order_status = self::ORDER_STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    public function markDelivered(): void
    {
        $this->order_status = self::ORDER_STATUS_DELIVERED;
        $this->delivered_at = now();
        $this->save();
    }

    public function recordPayment(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->save();
    }

    public function recordDP(float $amount): void
    {
        $this->dp_amount += $amount;
        $this->save();
    }

    // Status Checks
    public function isPending(): bool
    {
        return $this->order_status === self::ORDER_STATUS_PENDING;
    }

    public function isInProduction(): bool
    {
        return $this->order_status === self::ORDER_STATUS_PRODUCTION;
    }

    public function isCompleted(): bool
    {
        return $this->order_status === self::ORDER_STATUS_COMPLETED;
    }

    public function isDelivered(): bool
    {
        return $this->order_status === self::ORDER_STATUS_DELIVERED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function hasDebt(): bool
    {
        return $this->remaining_amount > 0;
    }

    // Label Helpers
    public static function getOrderStatusOptions(): array
    {
        return [
            self::ORDER_STATUS_PENDING => 'Pending',
            self::ORDER_STATUS_PRODUCTION => 'Produksi',
            self::ORDER_STATUS_COMPLETED => 'Selesai',
            self::ORDER_STATUS_DELIVERED => 'Terkirim',
        ];
    }

    public static function getPaymentStatusOptions(): array
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'Belum Bayar',
            self::PAYMENT_STATUS_DP => 'DP',
            self::PAYMENT_STATUS_PAID => 'Lunas',
        ];
    }

    public function getOrderStatusLabelAttribute(): string
    {
        return self::getOrderStatusOptions()[$this->order_status] ?? $this->order_status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::getPaymentStatusOptions()[$this->payment_status] ?? $this->payment_status;
    }
}

