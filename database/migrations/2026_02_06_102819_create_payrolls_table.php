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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');

            // Gaji Pokok
            $table->decimal('basic_salary', 15, 2);

            // Tunjangan
            $table->decimal('tunjangan_makan', 15, 2)->default(0);
            $table->decimal('tunjangan_transport', 15, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);

            // Bonus
            $table->decimal('bonus_kehadiran', 15, 2)->default(0);
            $table->decimal('bonus_target', 15, 2)->default(0);

            // Potongan
            $table->decimal('potongan', 15, 2)->default(0);
            $table->text('potongan_notes')->nullable();

            // Total
            $table->decimal('net_salary', 15, 2);

            // Status
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
