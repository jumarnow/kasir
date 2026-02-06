<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;

class SlipGajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan PegawaiSeeder sudah dijalankan atau ada data pegawai
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->info('Tidak ada data pegawai. Jalankan PegawaiSeeder terlebih dahulu.');
            return;
        }

        $periods = [
            ['month' => date('n', strtotime('last month')), 'year' => date('Y', strtotime('last month'))],
            ['month' => date('n'), 'year' => date('Y')],
        ];

        foreach ($periods as $period) {
            foreach ($employees as $employee) {
                // Skip random employees for current month to simulate incomplete payrolls
                if ($period['month'] == date('n') && rand(0, 100) > 70) {
                    continue;
                }

                $basicSalary = $employee->basic_salary;
                $makan = 500000;
                $transport = 300000;
                $jabatan = $employee->position == 'Manager Toko' ? 1000000 : 0;
                $bonusKehadiran = rand(0, 1) ? 200000 : 0;
                $potongan = rand(0, 1) ? 50000 : 0;

                $netSalary = $basicSalary + $makan + $transport + $jabatan + $bonusKehadiran - $potongan;

                // Status: last month paid, this month random
                $status = 'draft';
                $paidAt = null;

                if ($period['month'] != date('n')) {
                    $status = 'paid';
                    $paidAt = date('Y-m-25', strtotime("{$period['year']}-{$period['month']}-01"));
                } else {
                    $status = rand(0, 1) ? 'paid' : 'draft';
                    if ($status == 'paid') {
                        $paidAt = date('Y-m-d');
                    }
                }

                Payroll::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period_month' => $period['month'],
                        'period_year' => $period['year'],
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'tunjangan_makan' => $makan,
                        'tunjangan_transport' => $transport,
                        'tunjangan_jabatan' => $jabatan,
                        'bonus_kehadiran' => $bonusKehadiran,
                        'bonus_target' => 0,
                        'potongan' => $potongan,
                        'potongan_notes' => $potongan > 0 ? 'Terlambat' : null,
                        'net_salary' => $netSalary,
                        'status' => $status,
                        'paid_at' => $paidAt,
                    ]
                );
            }
        }
    }
}
