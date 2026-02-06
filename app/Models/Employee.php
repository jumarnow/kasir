<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'position',
        'join_date',
        'basic_salary',
        'bank_name',
        'bank_account',
        'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
