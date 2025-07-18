<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'total_amount',
        'paid_amount',
        'unpaid_amount',
        'discount',
        'description',
        'date',
        'status',
    ];

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function items(): BelongsToMany {
        return $this->belongsToMany(Item::class, 'order_items')
            ->withPivot('price', 'quantity')
            ->withTimestamps();
    }

    public function returnedItems(): BelongsToMany {
        return $this->belongsToMany(Item::class, 'returned_items')
            ->withPivot('quantity');
    }
}
