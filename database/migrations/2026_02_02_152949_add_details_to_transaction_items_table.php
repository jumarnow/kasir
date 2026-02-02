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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('finishing_id')->nullable()->constrained('finishings')->nullOnDelete();
            $table->foreignId('display_id')->nullable()->constrained('displays')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['finishing_id']);
            $table->dropForeign(['display_id']);
            $table->dropColumn(['finishing_id', 'display_id']);
        });
    }
};
