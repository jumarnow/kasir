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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('pricing_type', ['per_unit', 'per_dimension'])->default('per_unit')->after('unit');
            $table->decimal('price_per_meter', 12, 2)->nullable()->after('cost_price'); // Harga per m²
            $table->decimal('min_width', 8, 2)->nullable()->after('price_per_meter'); // Lebar minimum (cm)
            $table->decimal('min_length', 8, 2)->nullable()->after('min_width'); // Panjang minimum (cm)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'price_per_meter', 'min_width', 'min_length']);
        });
    }
};
