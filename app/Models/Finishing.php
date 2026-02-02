<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Finishing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'pricing_type',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Finishing $finishing) {
            if (empty($finishing->code)) {
                $finishing->code = strtoupper(Str::slug($finishing->name, '-'));
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
                ->orWhere('code', 'like', '%' . $term . '%');
        });
    }

    /**
     * Calculate price based on pricing type
     */
    public function calculatePrice(int $quantity = 1, ?float $length = null, ?float $width = null): float
    {
        return match ($this->pricing_type) {
            'per_meter' => $this->price * ($length ?? 1),
            'per_dimension' => $this->price * ($length ?? 1) * ($width ?? 1),
            default => $this->price * $quantity,
        };
    }
}
