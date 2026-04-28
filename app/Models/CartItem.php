<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'department_id',
        'product_type',
        'product_details',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'product_details' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function department()
    {
        return $this->belongsTo(SalesDepartment::class, 'department_id');
    }

    public function orderItem()
    {
        return $this->hasOne(PrototypeOrderItem::class, 'cart_item_id');
    }

    // Methods
    public function getProductDetailsAttribute($value)
    {
        $details = json_decode($value, true);
        
        // Format based on product type
        if ($this->product_type === 'garment') {
            return [
                'brand' => $details['brand'] ?? null,
                'size' => $details['size'] ?? null,
                'print_area' => $details['print_area'] ?? null,
                'print_width' => $details['print_width'] ?? null,
                'print_height' => $details['print_height'] ?? null,
                'color' => $details['color'] ?? null,
            ];
        } elseif ($this->product_type === 'tarpaulin') {
            return [
                'material' => $details['material'] ?? null,
                'width' => $details['width'] ?? null,
                'height' => $details['height'] ?? null,
                'unit' => $details['unit'] ?? null,
                'mounting' => $details['mounting'] ?? null,
                'finish' => $details['finish'] ?? null,
            ];
        } elseif ($this->product_type === 'embroidery') {
            return [
                'thread_colors' => $details['thread_colors'] ?? null,
                'stitch_count' => $details['stitch_count'] ?? null,
                'location' => $details['location'] ?? null,
                'width' => $details['width'] ?? null,
                'height' => $details['height'] ?? null,
            ];
        }
        
        return $details;
    }

    public function setProductDetailsAttribute($value)
    {
        $this->attributes['product_details'] = json_encode($value);
    }

    public function getDescription()
    {
        $details = $this->product_details;
        
        switch ($this->product_type) {
            case 'garment':
                return "{$details['brand']} - Size: {$details['size']}, Print: {$details['print_area']} ({$details['print_width']}x{$details['print_height']} inches)";
            case 'tarpaulin':
                return "{$details['material']} - {$details['width']}x{$details['height']} {$details['unit']}";
            case 'embroidery':
                return "Embroidery - {$details['thread_colors']} colors, {$details['stitch_count']} stitches";
            default:
                return ucfirst($this->product_type);
        }
    }

    public function getFormattedPrice()
    {
        return '₱' . number_format($this->total_price, 2);
    }

    public function getFormattedUnitPrice()
    {
        return '₱' . number_format($this->unit_price, 2);
    }
}