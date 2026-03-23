<?php

namespace App\Containers\AppSection\Category\Models;

use Franzose\ClosureTable\Models\ClosureTable;

/**
 * CategoryClosure Model
 *
 * Maps to the `category_closures` table required by franzose/closure-table.
 * This model stores the ancestor-descendant relationships with depth information,
 * enabling efficient tree traversal queries.
 *
 * @property int $ancestor
 * @property int $descendant
 * @property int $depth
 */
class CategoryClosure extends ClosureTable
{
    /**
     * The table associated with the model.
     */
    protected $table = 'category_closures';
}
