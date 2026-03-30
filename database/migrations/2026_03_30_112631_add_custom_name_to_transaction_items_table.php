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
            // Make product_id nullable for custom/manual items
            $table->foreignId('product_id')->nullable()->change();
            // Add custom_name for manually entered products
            $table->string('custom_name')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('custom_name');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
