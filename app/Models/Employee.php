<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    // Konstanta tipe karyawan
    const TYPE_PERMANENT = 'permanent';
    const TYPE_INTERN = 'intern';
    const TYPE_INTERNSHIP = 'internship';

    const EMPLOYEE_TYPES = [
        self::TYPE_PERMANENT => 'Karyawan Tetap',
        self::TYPE_INTERN => 'Karyawan Magang',
        self::TYPE_INTERNSHIP => 'Internship (PKL)',
    ];

    protected $fillable = [
        'employee_id',
        'name',
        'position',
        'employee_type',
        'join_date',
        'basic_salary',
        'daily_salary',
        'bank_name',
        'bank_account',
        'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'daily_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'eksekutor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cek apakah karyawan dibayar harian
     */
    public function isDailyPaid(): bool
    {
        return in_array($this->employee_type, [self::TYPE_INTERN, self::TYPE_INTERNSHIP]);
    }

    /**
     * Mendapatkan label tipe karyawan
     */
    public function getEmployeeTypeLabelAttribute(): string
    {
        return self::EMPLOYEE_TYPES[$this->employee_type] ?? '-';
    }

    /**
     * Hitung gaji pokok berdasarkan tipe dan hari kerja
     */
    public function calculateBasicSalary(int $workingDays = 0): float
    {
        if ($this->isDailyPaid()) {
            return $this->daily_salary * $workingDays;
        }

        return $this->basic_salary;
    }
}
