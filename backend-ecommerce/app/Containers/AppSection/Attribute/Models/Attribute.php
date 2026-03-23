<?php

namespace App\Containers\AppSection\Attribute\Models;

use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attribute Model
 *
 * Represents a configurable attribute (e.g., "Socket Type", "RAM Type")
 * used in the EAV pattern for flexible product specifications.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|ProductAttributeValue[] $productAttributeValues
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products
 */
class Attribute extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
    ];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    /**
     * An attribute has many product attribute values.
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    /**
     * An attribute belongs to many products through the EAV pivot table.
     *
     * The pivot carries the actual `value` and timestamps.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_attribute_values',
            'attribute_id',
            'product_id'
        )->withPivot('value')->withTimestamps();
    }
}
