<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Display extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'stock',
        'stock_alert',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_alert' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Display $display) {
            if (empty($display->code)) {
                $display->code = strtoupper(Str::slug($display->name, '-'));
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
                ->orWhere('code', 'like', '%' . $term . '%')
                ->orWhere('location', 'like', '%' . $term . '%');
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

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_alert;
    }
}
