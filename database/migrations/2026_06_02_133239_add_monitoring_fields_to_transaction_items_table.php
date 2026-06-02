<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->enum('status', ['pending', 'designing', 'production', 'completed', 'finished'])->default('pending')->after('notes');
            $table->enum('pickup_method', ['customer', 'kurir', 'diantar'])->nullable()->after('status');
            $table->timestamp('picked_up_at')->nullable()->after('pickup_method');
            $table->string('picked_up_notes')->nullable()->after('picked_up_at');
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete()->after('picked_up_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropColumn(['status', 'pickup_method', 'picked_up_at', 'picked_up_notes', 'checked_by']);
        });
    }
};
