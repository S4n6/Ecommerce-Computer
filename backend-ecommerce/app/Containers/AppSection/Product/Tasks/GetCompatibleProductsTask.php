<?php

namespace App\Containers\AppSection\Product\Tasks;

use App\Containers\AppSection\Category\Models\Category;
use App\Containers\AppSection\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * GetCompatibleProductsTask
 *
 * Determines which products in a target category are compatible with a given
 * source product, based on the application's EAV attribute/value rules.
 */
class GetCompatibleProductsTask
{
    /**
     * Return compatible products for the given source product and target category.
     *
     * @param  int  $sourceProductId  ID of the selected source product (e.g. Mainboard)
     * @param  string  $targetCategorySlug  Slug of the category to search in (e.g. 'cpu')
     * @return Collection<int, Product> Matching compatible products
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function run(int $sourceProductId, string $targetCategorySlug): Collection
    {
        // 1. Load the source product with its EAV attribute values.
        $sourceProduct = Product::with('attributeValues')->findOrFail($sourceProductId);

        // 2. Resolve the target category by slug.
        $targetCategory = Category::where('slug', $targetCategorySlug)->firstOrFail();

        // 3. Build a lookup of [attribute_id => value] from the source product.
        //    Example: [1 => 'LGA1700']
        $sourceAttributes = $sourceProduct->attributeValues
            ->pluck('value', 'attribute_id'); // keyed by attribute_id

        // 4. If the source product has no attributes, there is nothing to match on.
        if ($sourceAttributes->isEmpty()) {
            return new Collection;
        }

        // 5. Query: products in target category that are compatible.
        //    A product is compatible if, for every attribute_id present in the
        //    source, its own value matches the source value.
        //
        //    Implementation: for each source (attribute_id, value) pair, ensure
        //    the candidate has AT LEAST that pair. Products missing any pair are
        //    excluded via the chained whereHas calls.
        $query = Product::where('category_id', $targetCategory->id)
            ->where('id', '!=', $sourceProductId); // exclude the source itself

        foreach ($sourceAttributes as $attributeId => $value) {
            $query->whereHas('attributeValues', function ($q) use ($attributeId, $value) {
                $q->where('attribute_id', $attributeId)
                    ->where('value', $value);
            });
        }

        return $query->with('attributeValues.attribute', 'category')->get();
    }
}
