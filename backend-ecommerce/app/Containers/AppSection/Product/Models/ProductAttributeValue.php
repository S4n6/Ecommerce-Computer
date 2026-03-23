<?php

namespace App\Containers\AppSection\Product\Models;

use App\Containers\AppSection\Attribute\Models\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductAttributeValue Model
 *
 * The "value" entity in the EAV (Entity-Attribute-Value) pattern.
 * Links a Product to an Attribute with a specific value string.
 * Enables the Custom PC Build compatibility logic by allowing queries like:
 * "Find all products where socket_type = LGA1700".
 *
 * @property int $id
 * @property int $product_id
 * @property int $attribute_id
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Product   $product
 * @property-read Attribute $attribute
 */
class ProductAttributeValue extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'product_attribute_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'attribute_id' => 'integer',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    /**
     * This value belongs to a product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * This value belongs to an attribute definition.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
