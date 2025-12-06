<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'notes',
        'is_active',
        'price_tier',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_tier' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the price field name for this customer's price tier
     */
    public function getPriceField(): string
    {
        return match($this->price_tier) {
            2 => 'price_2',
            3 => 'price_3',
            default => 'price'
        };
    }

    /**
     * Get the price for a product based on this customer's tier
     */
    public function getPriceForProduct(Product $product): float
    {
        $priceField = $this->getPriceField();
        return (float) $product->{$priceField};
    }
}
