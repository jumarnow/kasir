<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    // Order Status Constants
    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_DESIGNING = 'designing';
    const ORDER_STATUS_PRODUCTION = 'production';
    const ORDER_STATUS_COMPLETED = 'completed';
    const ORDER_STATUS_FINISHED = 'finished';
    const ORDER_STATUS_DELIVERED = 'delivered';

    // Payment Status Constants
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_DP = 'dp';
    const PAYMENT_STATUS_PAID = 'paid';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'eksekutor_id',
        'eksekutor_2_id',
        'desainer_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'shipping_cost',
        'delivery_method',
        'total',
        'amount_paid',
        'change_due',
        'profit',
        'payment_method',
        'payment_type',
        'status',
        'order_status',
        'payment_status',
        'dp_amount',
        'remaining_amount',
        'due_date',
        'production_started_at',
        'completed_at',
        'delivered_at',
        'pickup_method',
        'picked_up_at',
        'picked_up_notes',
        'checked_by',
        'notes',
        'reject_reason',
        'payment_user_id',
        'created_at',
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
        'picked_up_at' => 'datetime',
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
        return DB::transaction(function () {
            $today = now()->toDateString();
            $prefix = now()->format('Ymd');

            $row = DB::table('invoice_sequences')
                ->where('date', $today)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('invoice_sequences')->insert([
                    'date' => $today,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = 1;
            } else {
                $sequence = $row->last_number + 1;

                DB::table('invoice_sequences')
                    ->where('date', $today)
                    ->update([
                        'last_number' => $sequence,
                        'updated_at' => now(),
                    ]);
            }

            return sprintf('INV-%s-%04d', $prefix, $sequence);
        });
    }


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentUser()
    {
        return $this->belongsTo(User::class, 'payment_user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function eksekutor()
    {
        return $this->belongsTo(Employee::class, 'eksekutor_id');
    }

    public function eksekutorTwo()
    {
        return $this->belongsTo(Employee::class, 'eksekutor_2_id');
    }

    public function desainer()
    {
        return $this->belongsTo(Employee::class, 'desainer_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function files()
    {
        return $this->hasMany(OrderFile::class);
    }

    public function trackings()
    {
        return $this->hasMany(ProductionTracking::class);
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
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
    public function updateStatusFromItems(): void
    {
        $items = $this->items()->get();
        if ($items->isEmpty()) return;

        if ($items->every(fn($item) => $item->status === 'finished')) {
            $this->order_status = self::ORDER_STATUS_FINISHED;
        } elseif ($items->every(fn($item) => in_array($item->status, ['completed', 'finished']))) {
            $this->order_status = self::ORDER_STATUS_COMPLETED;
            if (!$this->completed_at) $this->completed_at = now();
        } elseif ($items->contains(fn($item) => $item->status === 'production')) {
            $this->order_status = self::ORDER_STATUS_PRODUCTION;
            if (!$this->production_started_at) $this->production_started_at = now();
        } elseif ($items->contains(fn($item) => $item->status === 'designing')) {
            $this->order_status = self::ORDER_STATUS_DESIGNING;
        } else {
            $this->order_status = self::ORDER_STATUS_PENDING;
        }

        $this->save();
    }

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
            self::ORDER_STATUS_DESIGNING => 'Desain',
            self::ORDER_STATUS_PRODUCTION => 'Produksi',
            self::ORDER_STATUS_COMPLETED => 'Selesai',
            self::ORDER_STATUS_FINISHED => 'Menunggu Diambil',
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

