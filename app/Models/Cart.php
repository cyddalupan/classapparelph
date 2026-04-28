<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'customer_id',
        'user_id',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function order()
    {
        return $this->hasOne(PrototypeOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total_price');
        $tax = $subtotal * 0.12; // 12% tax
        $total = $subtotal + $tax;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return $this;
    }

    public function addItem($data)
    {
        $item = $this->items()->create([
            'department_id' => $data['department_id'],
            'product_type' => $data['product_type'],
            'product_details' => $data['product_details'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_price' => $data['quantity'] * $data['unit_price'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->calculateTotals();
        return $item;
    }

    public function removeItem($itemId)
    {
        $this->items()->where('id', $itemId)->delete();
        $this->calculateTotals();
        return $this;
    }

    public function updateItemQuantity($itemId, $quantity)
    {
        $item = $this->items()->find($itemId);
        if ($item) {
            $item->update([
                'quantity' => $quantity,
                'total_price' => $quantity * $item->unit_price,
            ]);
            $this->calculateTotals();
        }
        return $item;
    }

    public function getDepartmentBreakdown()
    {
        return $this->items()
            ->selectRaw('department_id, count(*) as item_count, sum(total_price) as department_total')
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department->name,
                    'item_count' => $item->item_count,
                    'department_total' => $item->department_total,
                ];
            });
    }

    public function convertToOrder($paymentData)
    {
        // Create the order
        $order = PrototypeOrder::create([
            'cart_id' => $this->id,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total_amount' => $this->total,
            'payment_method' => $paymentData['payment_method'],
            'deposit_paid' => $paymentData['deposit_paid'] ?? 0,
            'balance_due' => $this->total - ($paymentData['deposit_paid'] ?? 0),
            'payment_notes' => $paymentData['payment_notes'] ?? null,
            'payment_screenshot_path' => $paymentData['payment_screenshot_path'] ?? null,
            'payment_owner' => $paymentData['payment_owner'] ?? null,
            'internal_notes' => $paymentData['internal_notes'] ?? null,
            'priority' => $paymentData['priority'] ?? 'normal',
            'estimated_completion_date' => $paymentData['estimated_completion_date'] ?? null,
            'status' => 'confirmed',
            'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(PrototypeOrder::count() + 1, 4, '0', STR_PAD_LEFT),
        ]);

        // Convert cart items to order items
        foreach ($this->items as $cartItem) {
            $orderItem = PrototypeOrderItem::create([
                'order_id' => $order->id,
                'cart_item_id' => $cartItem->id,
                'department_id' => $cartItem->department_id,
                'item_number' => strtoupper(substr($cartItem->department->code, 0, 3)) . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($cartItem->id, 2, '0', STR_PAD_LEFT),
                'product_type' => $cartItem->product_type,
                'product_details' => $cartItem->product_details,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'total_price' => $cartItem->total_price,
                'notes' => $cartItem->notes,
                'status' => 'pending',
            ]);

            // Create KANBAN item for this order item
            PrototypeKanbanItem::create([
                'order_item_id' => $orderItem->id,
                'department_id' => $cartItem->department_id,
                'title' => "New Order: {$cartItem->product_type} x{$cartItem->quantity}",
                'description' => "Customer: {$this->customer->name}\nNotes: {$cartItem->notes}",
                'column' => 'todo',
                'position' => 0,
                'attachments' => null,
                'due_date' => $paymentData['estimated_completion_date'] ?? null,
            ]);
        }

        // Update cart status
        $this->update(['status' => 'converted']);

        return $order;
    }
}