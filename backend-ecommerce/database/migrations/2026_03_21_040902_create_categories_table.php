<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `categories` table following the franzose/closure-table pattern.
     * The `parent_id` is a self-referencing foreign key used for direct parent lookup.
     * The actual tree traversal is handled by the `category_closures` table.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();

            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
