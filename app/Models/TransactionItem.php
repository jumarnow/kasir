<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'custom_name',
        'quantity',
        'width',
        'length',
        'area',
        'price',
        'cost_price',
        'total',
        'profit',
        'notes',
        'finishing_id',
        'display_id',
        'material_id',
        'status',
        'pickup_method',
        'picked_up_at',
        'picked_up_notes',
        'checked_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'area' => 'decimal:4',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'total' => 'decimal:2',
        'profit' => 'decimal:2',
        'picked_up_at' => 'datetime',
    ];

    protected $touches = ['transaction'];

    protected static function booted(): void
    {
        static::saving(function (TransactionItem $item) {
            // Calculate area if dimensions are provided (in m²)
            if ($item->width && $item->length) {
                $item->area = ($item->width / 100) * ($item->length / 100);
            }

            // Calculate total based on pricing type
            if ($item->area && $item->product?->pricing_type === 'per_dimension') {
                // For dimension-based: price is already per-area unit price × area
                $item->total = $item->price * $item->quantity;
            } else {
                // For per-unit pricing
                $item->total = $item->price * $item->quantity;
            }

            // Calculate profit
            $costTotal = $item->area
                ? ($item->cost_price * $item->area * $item->quantity)
                : ($item->cost_price * $item->quantity);
            $item->profit = $item->total - $costTotal;
        });
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function finishing()
    {
        return $this->belongsTo(Finishing::class);
    }

    public function display()
    {
        return $this->belongsTo(Display::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function trackings()
    {
        return $this->hasMany(ProductionTracking::class);
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Get formatted dimensions
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->width && $this->length) {
            return "{$this->width} × {$this->length} cm";
        }
        return null;
    }

    /**
     * Get formatted area
     */
    public function getFormattedAreaAttribute(): ?string
    {
        if ($this->area) {
            return number_format($this->area, 2) . ' m²';
        }
        return null;
    }
}

