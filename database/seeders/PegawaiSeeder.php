<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'employee_id' => 'PEG-001',
                'name' => 'Budi Santoso',
                'position' => 'Manager Toko',
                'join_date' => '2023-01-15',
                'basic_salary' => 5000000,
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'is_active' => true,
            ],
            [
                'employee_id' => 'PEG-002',
                'name' => 'Siti Aminah',
                'position' => 'Kasir Senior',
                'join_date' => '2023-03-10',
                'basic_salary' => 3500000,
                'bank_name' => 'BRI',
                'bank_account' => '0987654321',
                'is_active' => true,
            ],
            [
                'employee_id' => 'PEG-003',
                'name' => 'Rudi Hartono',
                'position' => 'Staf Gudang',
                'join_date' => '2023-06-01',
                'basic_salary' => 3200000,
                'bank_name' => 'Mandiri',
                'bank_account' => '1122334455',
                'is_active' => true,
            ],
            [
                'employee_id' => 'PEG-004',
                'name' => 'Dewi Sartika',
                'position' => 'Kasir Junior',
                'join_date' => '2024-01-05',
                'basic_salary' => 2800000,
                'bank_name' => 'BCA',
                'bank_account' => '5566778899',
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                ['employee_id' => $employee['employee_id']],
                $employee
            );
        }
    }
}
