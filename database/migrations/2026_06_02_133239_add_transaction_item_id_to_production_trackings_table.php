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
        Schema::table('production_trackings', function (Blueprint $table) {
            $table->foreignId('transaction_item_id')->nullable()->after('transaction_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_trackings', function (Blueprint $table) {
            $table->dropForeign(['transaction_item_id']);
            $table->dropColumn('transaction_item_id');
        });
    }
};
