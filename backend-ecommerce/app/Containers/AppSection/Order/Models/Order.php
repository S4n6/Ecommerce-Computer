<?php

namespace App\Containers\AppSection\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model
 *
 * @property int $id
 * @property int $user_id
 * @property float $total_price
 * @property string $status pending|processing|completed|cancelled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User       $user
 * @property-read \Illuminate\Database\Eloquent\Collection|OrderItem[] $items
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    /**
     * The customer who placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The line items belonging to this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
