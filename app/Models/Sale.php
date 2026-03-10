<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'sale_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'product_name',
        'product_description',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_method',
        'payment_status',
        'sale_status',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total amount before saving.
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($sale) {
            if ($sale->quantity && $sale->unit_price) {
                $sale->total_amount = $sale->quantity * $sale->unit_price;
            }
        });
    }
}
