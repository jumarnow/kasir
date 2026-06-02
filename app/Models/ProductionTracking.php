<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 
        'transaction_item_id',
        'user_id', 
        'type', 
        'tracked_at', 
        'notes'
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
    ];

    const TYPE_DESIGN_IN = 'design_in';
    const TYPE_DESIGN_OUT = 'design_out';
    const TYPE_PRODUCTION_IN = 'production_in';
    const TYPE_PRODUCTION_OUT = 'production_out';
    const TYPE_ADMIN_OUT = 'admin_out';

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
