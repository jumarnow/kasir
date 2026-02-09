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
        Schema::table('employees', function (Blueprint $table) {
            // Tipe karyawan: permanent (tetap), intern (magang), internship (PKL)
            $table->enum('employee_type', ['permanent', 'intern', 'internship'])
                ->default('permanent')
                ->after('position');

            // Gaji harian (untuk tipe intern dan internship)
            $table->decimal('daily_salary', 15, 2)
                ->default(0)
                ->after('basic_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['employee_type', 'daily_salary']);
        });
    }
};
