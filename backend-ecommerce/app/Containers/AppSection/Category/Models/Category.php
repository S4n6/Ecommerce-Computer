<?php

namespace App\Containers\AppSection\Category\Models;

use App\Containers\AppSection\Product\Models\Product;
use Franzose\ClosureTable\Contracts\EntityInterface;
use Franzose\ClosureTable\Models\Entity;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Category Model
 *
 * Implements the franzose/closure-table Entity for hierarchical category
 * management with efficient ancestor/descendant tree queries.
 *
 * Important notes about franzose/closure-table:
 *  - Entity uses $closure (NOT $closureTable) to reference the closure model.
 *  - Entity sets $timestamps = false by default; we re-enable it below since
 *    our categories table has created_at / updated_at columns.
 *  - Entity includes SoftDeletes, so our migration adds deleted_at to categories.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property int $position
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $children
 */
class Category extends Entity implements EntityInterface
{
    /**
     * The table associated with the model.
     */
    protected $table = 'categories';

    /**
     * The ClosureTable model class for this entity.
     * MUST be named $closure — this is the property the Entity base class reads.
     *
     * @var string
     */
    protected $closure = CategoryClosure::class;

    /**
     * Re-enable timestamps — Entity sets this to false by default, but our
     * `categories` table has created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'position' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    /**
     * A category has many products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
