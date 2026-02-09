<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'working_days',
        'daily_salary',
        'employee_type',
        'basic_salary',
        'tunjangan_makan',
        'tunjangan_transport',
        'tunjangan_jabatan',
        'bonus_kehadiran',
        'bonus_target',
        'potongan',
        'potongan_notes',
        'net_salary',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'daily_salary' => 'decimal:2',
        'tunjangan_makan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'bonus_kehadiran' => 'decimal:2',
        'bonus_target' => 'decimal:2',
        'potongan' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Cek apakah payroll ini untuk karyawan harian
     */
    public function isDailyPaid(): bool
    {
        return in_array($this->employee_type, [
            Employee::TYPE_INTERN,
            Employee::TYPE_INTERNSHIP
        ]);
    }

    /**
     * Label tipe karyawan
     */
    public function getEmployeeTypeLabelAttribute(): string
    {
        return Employee::EMPLOYEE_TYPES[$this->employee_type] ?? '-';
    }
}
