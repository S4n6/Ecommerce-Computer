<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Containers\AppSection\Category\Models\Category;
use App\Containers\AppSection\Attribute\Models\Attribute;
use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Models\ProductAttributeValue;

/**
 * PCBuildSeeder
 *
 * Populates the minimum data required to demonstrate the
 * "Get Compatible Products" feature.
 *
 * Data created:
 *   Categories  : Mainboard (root), CPU (root)
 *   Attributes  : Socket (code: socket_type)
 *   Products    :
 *     - ASUS ROG STRIX Z790-E  [Mainboard]  → socket_type = LGA1700
 *     - Intel Core i9-13900K   [CPU]         → socket_type = LGA1700  ✅ Compatible
 *     - AMD Ryzen 9 7950X      [CPU]         → socket_type = AM5       ❌ Incompatible
 */
class PCBuildSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. Categories
        //    franzose/closure-table requires using the model's static API
        //    to correctly write both the `categories` and `category_closures`
        //    rows in one call.
        // ----------------------------------------------------------------

        /** @var Category $mainboardCategory */
        $mainboardCategory = Category::firstOrCreate(
            ['slug' => 'mainboard'],
            ['name' => 'Mainboard', 'position' => 0]
        );

        /** @var Category $cpuCategory */
        $cpuCategory = Category::firstOrCreate(
            ['slug' => 'cpu'],
            ['name' => 'CPU', 'position' => 1]
        );

        // ----------------------------------------------------------------
        // 2. Attribute
        // ----------------------------------------------------------------

        /** @var Attribute $socketAttribute */
        $socketAttribute = Attribute::firstOrCreate(
            ['code' => 'socket_type'],
            ['name' => 'Socket']
        );

        // ----------------------------------------------------------------
        // 3. Products + EAV Values
        // ----------------------------------------------------------------

        // --- Mainboard ---
        /** @var Product $mainboard */
        $mainboard = Product::firstOrCreate(
            ['slug' => 'asus-rog-strix-z790-e'],
            [
                'category_id' => $mainboardCategory->id,
                'name' => 'ASUS ROG STRIX Z790-E',
                'price' => 12500000.00,
                'stock' => 10,
            ]
        );

        ProductAttributeValue::create([
            'product_id'   => $mainboard->id,
            'attribute_id' => $socketAttribute->id,
            'value'        => 'LGA1700',
        ]);

        // --- CPU #1 — Compatible (LGA1700) ---
        /** @var Product $compatibleCpu */
        $compatibleCpu = Product::firstOrCreate(
            ['slug' => 'intel-core-i9-13900k'],
            [
                'category_id' => $cpuCategory->id,
                'name' => 'Intel Core i9-13900K',
                'price' => 8750000.00,
                'stock' => 25,
            ]
        );

        ProductAttributeValue::create([
            'product_id'   => $compatibleCpu->id,
            'attribute_id' => $socketAttribute->id,
            'value'        => 'LGA1700',
        ]);

        // --- CPU #2 — Incompatible (AM5) ---
        /** @var Product $incompatibleCpu */
        $incompatibleCpu = Product::firstOrCreate(
            ['slug' => 'amd-ryzen-9-7950x'],
            [
                'category_id' => $cpuCategory->id,
                'name' => 'AMD Ryzen 9 7950X',
                'price' => 9200000.00,
                'stock' => 15,
            ]
        );

        ProductAttributeValue::create([
            'product_id'   => $incompatibleCpu->id,
            'attribute_id' => $socketAttribute->id,
            'value'        => 'AM5',
        ]);

        $this->command->info('✅  PCBuildSeeder completed:');
        $this->command->table(
            ['Type', 'Name', 'Socket'],
            [
                ['Mainboard', $mainboard->name,        'LGA1700'],
                ['CPU ✅',    $compatibleCpu->name,    'LGA1700'],
                ['CPU ❌',    $incompatibleCpu->name,  'AM5'],
            ]
        );
    }
}
