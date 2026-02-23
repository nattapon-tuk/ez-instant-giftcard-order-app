<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class LocalOrder extends Model
{

    /** @use HasFactory<\Database\Factories\LocalOrderFactory> */
    use HasFactory;

    protected $table = 'local_orders';

    // Fillable fields
    protected $fillable = ['localOrderId', 'localStatus', 'ezTransactionId'];

    //to config
    protected string $prefixTransaction;


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->prefixTransaction = config('ezApi.prefixTransactionRef');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Generate a unique reference before creating the record
            do {
                $reference = $model->prefixTransaction . str_pad(self::max('id')+1,8,0,STR_PAD_LEFT);
            } while (self::where('localOrderId', $reference)->exists());

            $model->localOrderId = $reference;
        });
    }

    public function ezOrder(): HasOne
    {
        return $this->hasOne(EzOrder::class, 'ezTransactionId', 'ezTransactionId');
    }

}
