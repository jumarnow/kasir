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
            $table->decimal('width', 8, 2)->nullable()->after('quantity'); // Lebar (cm)
            $table->decimal('length', 8, 2)->nullable()->after('width'); // Panjang (cm)
            $table->decimal('area', 12, 4)->nullable()->after('length'); // Luas (m²)
            $table->text('notes')->nullable()->after('profit'); // Catatan per item
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['width', 'length', 'area', 'notes']);
        });
    }
};
