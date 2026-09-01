<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'type',
        'quantity',
        'previous_stock',
        'current_stock',
        'reference',
        'user_id',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'current_stock' => 'integer',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
