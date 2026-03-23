<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the `deleted_at` column required by franzose/closure-table's Entity
     * base model which includes the SoftDeletes trait out of the box.
     * Without this column every category query will fail with
     * "Undefined column: deleted_at".
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

