<?php

namespace App\Containers\AppSection\Product\Models;

use App\Containers\AppSection\Attribute\Models\Attribute;
use App\Containers\AppSection\Category\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product Model
 *
 * Represents a product in the e-commerce catalog.
 * Uses EAV (Entity-Attribute-Value) pattern via the pivot table
 * `product_attribute_values` for flexible attribute management.
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property float $price
 * @property int $stock
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|ProductAttributeValue[] $attributeValues
 * @property-read \Illuminate\Database\Eloquent\Collection|Attribute[] $attributes
 */
class Product extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    /**
     * A product belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * A product has many attribute values (EAV values).
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

    /**
     * A product belongs to many attributes through the EAV pivot table.
     *
     * The pivot carries the actual `value` and timestamps.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            Attribute::class,
            'product_attribute_values',
            'product_id',
            'attribute_id'
        )->withPivot('value')->withTimestamps();
    }
}
