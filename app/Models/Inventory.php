<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'type',
        'category',
        'description',
        'unit_price',
        'unit_of_measure',
        'current_stock',
        'minimum_stock',
        'reorder_quantity',
        'supplier_id',
        'supplier_sku',
        'specifications',
        'storage_location',
        'last_restocked_at',
        'expiry_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:2',
        'current_stock' => 'decimal:3',
        'minimum_stock' => 'decimal:3',
        'reorder_quantity' => 'decimal:3',
        'is_active' => 'boolean',
        'specifications' => 'array',
        'last_restocked_at' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the supplier for this inventory item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock')
                    ->where('is_active', true);
    }

    /**
     * Check if the item is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Get the stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get the stock status color.
     */
    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get the stock status text.
     */
    public function getStockStatusTextAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
            default => 'Unknown',
        };
    }

    /**
     * Get the total value of current stock.
     */
    public function getStockValueAttribute(): float
    {
        return (float) $this->current_stock * (float) $this->unit_price;
    }
}