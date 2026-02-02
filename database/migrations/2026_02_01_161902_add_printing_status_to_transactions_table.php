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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('order_status', ['pending', 'production', 'completed', 'delivered'])->default('pending')->after('status');
            $table->enum('payment_status', ['unpaid', 'dp', 'paid'])->default('unpaid')->after('order_status');
            $table->decimal('dp_amount', 12, 2)->default(0)->after('payment_status'); // Jumlah DP
            $table->decimal('remaining_amount', 12, 2)->default(0)->after('dp_amount'); // Sisa pembayaran
            $table->date('due_date')->nullable()->after('remaining_amount'); // Jatuh tempo
            $table->timestamp('production_started_at')->nullable()->after('due_date');
            $table->timestamp('completed_at')->nullable()->after('production_started_at');
            $table->timestamp('delivered_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'order_status',
                'payment_status',
                'dp_amount',
                'remaining_amount',
                'due_date',
                'production_started_at',
                'completed_at',
                'delivered_at',
            ]);
        });
    }
};
