<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Jumlah hari kerja dalam periode tersebut
            $table->unsignedTinyInteger('working_days')
                ->default(0)
                ->after('period_year');

            // Gaji harian (snapshot saat payroll dibuat)
            $table->decimal('daily_salary', 15, 2)
                ->default(0)
                ->after('working_days');

            // Tipe karyawan (snapshot)
            $table->enum('employee_type', ['permanent', 'intern', 'internship'])
                ->default('permanent')
                ->after('daily_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'daily_salary', 'employee_type']);
        });
    }
};
