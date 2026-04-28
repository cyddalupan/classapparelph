<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolumeDiscount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'master_item_id',
        'min_quantity',
        'max_quantity',
        'price_per_unit',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'price_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the master item that owns the volume discount.
     */
    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class);
    }

    /**
     * Get the user who created this volume discount.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this volume discount.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get active volume discounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get volume discounts for a specific quantity.
     */
    public function scopeForQuantity($query, $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
                    ->where(function($q) use ($quantity) {
                        $q->where('max_quantity', '>=', $quantity)
                          ->orWhereNull('max_quantity');
                    })
                    ->orderBy('min_quantity', 'desc');
    }

    /**
     * Get the display range for this tier.
     */
    public function getRangeDisplayAttribute()
    {
        if ($this->max_quantity) {
            return "{$this->min_quantity}-{$this->max_quantity} units";
        }
        return "{$this->min_quantity}+ units";
    }

    /**
     * Get the price display.
     */
    public function getPriceDisplayAttribute()
    {
        return '₱' . number_format($this->price_per_unit, 2);
    }
}