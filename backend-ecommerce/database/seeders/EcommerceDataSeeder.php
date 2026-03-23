<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Containers\AppSection\Attribute\Models\Attribute;
use App\Containers\AppSection\Category\Models\Category;
use App\Containers\AppSection\Category\Repositories\CategoryRepository;
use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Models\ProductAttributeValue;
use App\Containers\AppSection\Product\Repositories\ProductRepository;

/**
 * EcommerceDataSeeder
 *
 * Seeds realistic catalog data for the PC Builder MVP, including EAV values
 * used by the compatibility endpoint.
 */
class EcommerceDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            /** @var CategoryRepository $categoryRepository */
            $categoryRepository = app(CategoryRepository::class);

            /** @var ProductRepository $productRepository */
            $productRepository = app(ProductRepository::class);

            // ----------------------------------------------------------------
            // 1) Categories
            // ----------------------------------------------------------------
            $categories = [
                'cpu'       => ['name' => 'CPU', 'position' => 0],
                'mainboard' => ['name' => 'Mainboard', 'position' => 1],
                'ram'       => ['name' => 'RAM', 'position' => 2],
                'vga'       => ['name' => 'VGA', 'position' => 3],
                'psu'       => ['name' => 'PSU', 'position' => 4],
            ];

            $categoryMap = [];

            foreach ($categories as $slug => $payload) {
                /** @var Category|null $existing */
                $existing = Category::where('slug', $slug)->first();

                if ($existing) {
                    $categoryRepository->update([
                        'name'     => $payload['name'],
                        'position' => $payload['position'],
                        'parent_id' => null,
                    ], $existing->id);

                    $categoryMap[$slug] = Category::findOrFail($existing->id);
                    continue;
                }

                /** @var Category $created */
                $created = $categoryRepository->create([
                    'name'      => $payload['name'],
                    'slug'      => $slug,
                    'position'  => $payload['position'],
                    'parent_id' => null,
                ]);

                $categoryMap[$slug] = $created;
            }

            // ----------------------------------------------------------------
            // 2) EAV Attribute: Socket (code/slug = socket)
            // ----------------------------------------------------------------
            /** @var Attribute $socketAttribute */
            $socketAttribute = Attribute::updateOrCreate(
                ['code' => 'socket'],
                ['name' => 'Socket']
            );

            // Cleanup legacy socket_type values to keep compatibility checks deterministic.
            $legacySocketAttribute = Attribute::where('code', 'socket_type')->first();
            if ($legacySocketAttribute) {
                ProductAttributeValue::query()
                    ->where('attribute_id', $legacySocketAttribute->id)
                    ->delete();

                $legacySocketAttribute->delete();
            }

            // ----------------------------------------------------------------
            // 3) Product catalog
            // ----------------------------------------------------------------
            $products = [
                // Ecosystem 1 (Intel - LGA1700)
                [
                    'category_slug' => 'cpu',
                    'name' => 'Intel Core i9-13900KF',
                    'slug' => 'intel-core-i9-13900kf',
                    'price' => 8750000,
                    'stock' => 20,
                    'socket' => 'LGA1700',
                ],
                [
                    'category_slug' => 'cpu',
                    'name' => 'Intel Core i5-13600K',
                    'slug' => 'intel-core-i5-13600k',
                    'price' => 6500000,
                    'stock' => 30,
                    'socket' => 'LGA1700',
                ],
                [
                    'category_slug' => 'mainboard',
                    'name' => 'ASUS ROG STRIX Z790-E',
                    'slug' => 'asus-rog-strix-z790-e',
                    'price' => 12500000,
                    'stock' => 12,
                    'socket' => 'LGA1700',
                ],
                [
                    'category_slug' => 'mainboard',
                    'name' => 'MSI MAG B760 TOMAHAWK',
                    'slug' => 'msi-mag-b760-tomahawk',
                    'price' => 4500000,
                    'stock' => 25,
                    'socket' => 'LGA1700',
                ],

                // Ecosystem 2 (AMD - AM5)
                [
                    'category_slug' => 'cpu',
                    'name' => 'AMD Ryzen 9 7950X3D',
                    'slug' => 'amd-ryzen-9-7950x3d',
                    'price' => 15000000,
                    'stock' => 10,
                    'socket' => 'AM5',
                ],
                [
                    'category_slug' => 'cpu',
                    'name' => 'AMD Ryzen 5 7600X',
                    'slug' => 'amd-ryzen-5-7600x',
                    'price' => 5500000,
                    'stock' => 28,
                    'socket' => 'AM5',
                ],
                [
                    'category_slug' => 'mainboard',
                    'name' => 'GIGABYTE X670E AORUS MASTER',
                    'slug' => 'gigabyte-x670e-aorus-master',
                    'price' => 11000000,
                    'stock' => 9,
                    'socket' => 'AM5',
                ],
                [
                    'category_slug' => 'mainboard',
                    'name' => 'ASUS TUF GAMING B650-PLUS',
                    'slug' => 'asus-tuf-gaming-b650-plus',
                    'price' => 5000000,
                    'stock' => 18,
                    'socket' => 'AM5',
                ],

                // Universal components (no socket dependency)
                [
                    'category_slug' => 'ram',
                    'name' => 'Corsair Vengeance RGB 32GB (2x16GB) DDR5',
                    'slug' => 'corsair-vengeance-rgb-32gb-2x16gb-ddr5',
                    'price' => 3200000,
                    'stock' => 40,
                ],
                [
                    'category_slug' => 'ram',
                    'name' => 'G.Skill Trident Z5 Neo 64GB DDR5',
                    'slug' => 'g-skill-trident-z5-neo-64gb-ddr5',
                    'price' => 6500000,
                    'stock' => 22,
                ],
                [
                    'category_slug' => 'vga',
                    'name' => 'NVIDIA GeForce RTX 4090 24GB',
                    'slug' => 'nvidia-geforce-rtx-4090-24gb',
                    'price' => 45000000,
                    'stock' => 6,
                ],
                [
                    'category_slug' => 'vga',
                    'name' => 'AMD Radeon RX 7900 XTX 24GB',
                    'slug' => 'amd-radeon-rx-7900-xtx-24gb',
                    'price' => 26000000,
                    'stock' => 8,
                ],
                [
                    'category_slug' => 'psu',
                    'name' => 'Corsair RM1000x 1000W 80 Plus Gold',
                    'slug' => 'corsair-rm1000x-1000w-80-plus-gold',
                    'price' => 3800000,
                    'stock' => 26,
                ],
                [
                    'category_slug' => 'psu',
                    'name' => 'Seasonic Focus GX-850 850W',
                    'slug' => 'seasonic-focus-gx-850-850w',
                    'price' => 2900000,
                    'stock' => 33,
                ],
            ];

            foreach ($products as $payload) {
                $category = $categoryMap[$payload['category_slug']];

                $productData = [
                    'category_id' => $category->id,
                    'name' => $payload['name'],
                    'slug' => $payload['slug'] ?? Str::slug($payload['name']),
                    'price' => $payload['price'],
                    'stock' => $payload['stock'] ?? 0,
                ];

                /** @var Product|null $existingProduct */
                $existingProduct = Product::where('slug', $productData['slug'])->first();

                if ($existingProduct) {
                    $productRepository->update($productData, $existingProduct->id);
                    $product = Product::findOrFail($existingProduct->id);
                } else {
                    /** @var Product $product */
                    $product = $productRepository->create($productData);
                }

                if (! empty($payload['socket'])) {
                    $socketValue = ProductAttributeValue::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'attribute_id' => $socketAttribute->id,
                        ],
                        ['value' => $payload['socket']]
                    );

                    ProductAttributeValue::query()
                        ->where('product_id', $product->id)
                        ->where('attribute_id', $socketAttribute->id)
                        ->where('id', '!=', $socketValue->id)
                        ->delete();
                }
            }
        });

        $this->command?->info('EcommerceDataSeeder completed successfully.');
    }
}
