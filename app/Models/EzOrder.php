<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EzOrder extends Model
{

    /** @use HasFactory<\Database\Factories\EzOrderFactory> */
    use HasFactory;

    protected $table = 'ez_orders';

    // Fillable fields
    protected $fillable = ['ezTransactionId', 'ezOrderStatus', 'redeemCode', 'isReserved'];


    public function localOrders(): HasMany
    {
        return $this->hasMany(LocalOrder::class, 'ezTransactionId', 'ezTransactionId');
    }
}
