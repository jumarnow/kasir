<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'barcode',
        'sku',
        'unit',
        'stock',
        'min_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (RawMaterial $material) {
            if (empty($material->sku)) {
                $material->sku = strtoupper(Str::random(8));
            }
        });
    }

    public function mutations()
    {
        return $this->hasMany(RawMaterialMutation::class);
    }

    public function incrementStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->save();
    }

    public function decrementStock(int $quantity): void
    {
        $this->stock = max(0, $this->stock - $quantity);
        $this->save();
    }
}
