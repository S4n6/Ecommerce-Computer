<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This is the EAV "value" table that links products to attribute values.
     * It enables the Custom PC Build compatibility logic by allowing queries like:
     * "Find all products where socket_type = LGA1700".
     */
    public function up(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->foreignId('attribute_id')
                  ->constrained('attributes')
                  ->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            // Composite index for efficient lookups:
            // "Find all values for a specific product" or "find products with a specific attribute"
            $table->index(['product_id', 'attribute_id']);

            // Index for value-based filtering:
            // "Find all products where attribute X has value Y"
            $table->index(['attribute_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
