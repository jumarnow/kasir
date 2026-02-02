<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'unit',
        'pricing_type',
        'price',
        'price_2',
        'price_3',
        'cost_price',
        'price_per_meter',
        'min_width',
        'min_length',
        'stock',
        'stock_alert',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_2' => 'decimal:2',
        'price_3' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'price_per_meter' => 'decimal:2',
        'min_width' => 'decimal:2',
        'min_length' => 'decimal:2',
        'stock' => 'integer',
        'stock_alert' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
                ->orWhere('sku', 'like', '%' . $term . '%')
                ->orWhere('barcode', 'like', '%' . $term . '%');
        });
    }

    public function decrementStock(int $quantity): void
    {
        $this->stock = max(0, $this->stock - $quantity);
        $this->save();
    }

    public function incrementStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->save();
    }

    /**
     * Check if product uses dimension-based pricing
     */
    public function isDimensionBased(): bool
    {
        return $this->pricing_type === 'per_dimension';
    }

    /**
     * Calculate price based on pricing type
     * 
     * @param int $quantity Jumlah item
     * @param float|null $width Lebar dalam cm
     * @param float|null $length Panjang dalam cm
     * @param int $priceTier Price tier (1, 2, atau 3)
     * @return array ['price' => harga satuan, 'total' => total, 'area' => luas m²]
     */
    public function calculatePrice(int $quantity = 1, ?float $width = null, ?float $length = null, int $priceTier = 1): array
    {
        if ($this->pricing_type === 'per_dimension' && $width && $length) {
            // Konversi cm ke meter dan hitung luas
            $widthM = $width / 100;
            $lengthM = $length / 100;
            $area = $widthM * $lengthM;

            // Gunakan price_per_meter untuk perhitungan
            $pricePerMeter = (float) ($this->price_per_meter ?? $this->price);
            $unitPrice = $pricePerMeter * $area;
            $total = $unitPrice * $quantity;

            return [
                'price' => round($unitPrice, 2),
                'total' => round($total, 2),
                'area' => round($area, 4),
                'pricing_type' => 'per_dimension',
            ];
        }

        // Per unit pricing
        $priceField = match ($priceTier) {
            2 => 'price_2',
            3 => 'price_3',
            default => 'price',
        };

        $unitPrice = (float) ($this->{$priceField} ?? $this->price);
        $total = $unitPrice * $quantity;

        return [
            'price' => round($unitPrice, 2),
            'total' => round($total, 2),
            'area' => null,
            'pricing_type' => 'per_unit',
        ];
    }
}

