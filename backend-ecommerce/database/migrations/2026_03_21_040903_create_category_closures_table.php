<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `category_closures` table following the franzose/closure-table pattern.
     * Each row represents a path from an ancestor to a descendant with the depth of separation.
     * Every node has a self-referencing row (ancestor = descendant, depth = 0).
     */
    public function up(): void
    {
        Schema::create('category_closures', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');
            $table->unsignedInteger('depth')->default(0);

            // Primary key on the (ancestor, descendant) pair
            $table->primary(['ancestor', 'descendant']);

            // Foreign keys with cascade delete
            $table->foreign('ancestor')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();

            $table->foreign('descendant')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();

            // Index for efficient descendant lookups (e.g., "find all ancestors of X")
            $table->index('descendant');

            // Index for depth-based queries (e.g., "find direct children only")
            $table->index('depth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_closures');
    }
};
