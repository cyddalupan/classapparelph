<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'marketplace',
        'email',
        'location',
        'company',
        'total_orders',
        'total_spent',
        'average_order_value',
        'first_order_date',
        'last_order_date',
        'customer_tier',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'first_order_date' => 'date',
        'last_order_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Tier thresholds
    const TIER_BRONZE = 'bronze';    // < ₱10,000
    const TIER_SILVER = 'silver';    // ₱10,000 - ₱50,000
    const TIER_GOLD = 'gold';        // ₱50,000 - ₱200,000
    const TIER_PLATINUM = 'platinum'; // > ₱200,000

    /**
     * Update customer tier based on total spent
     */
    public function updateTier()
    {
        if ($this->total_spent >= 200000) {
            $this->customer_tier = self::TIER_PLATINUM;
        } elseif ($this->total_spent >= 50000) {
            $this->customer_tier = self::TIER_GOLD;
        } elseif ($this->total_spent >= 10000) {
            $this->customer_tier = self::TIER_SILVER;
        } else {
            $this->customer_tier = self::TIER_BRONZE;
        }
    }

    /**
     * Get tier badge color
     */
    public function getTierBadgeColor()
    {
        return match($this->customer_tier) {
            self::TIER_BRONZE => 'secondary',
            self::TIER_SILVER => 'light',
            self::TIER_GOLD => 'warning',
            self::TIER_PLATINUM => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get tier icon
     */
    public function getTierIcon()
    {
        return match($this->customer_tier) {
            self::TIER_BRONZE => '🥉',
            self::TIER_SILVER => '🥈',
            self::TIER_GOLD => '🥇',
            self::TIER_PLATINUM => '💎',
            default => '👤',
        };
    }

    /**
     * Format total spent for display
     */
    public function getFormattedTotalSpent()
    {
        if ($this->total_spent >= 1000000) {
            return '₱' . number_format($this->total_spent / 1000000, 1) . 'M';
        } elseif ($this->total_spent >= 1000) {
            return '₱' . number_format($this->total_spent / 1000, 1) . 'K';
        }
        return '₱' . number_format($this->total_spent, 0);
    }

    /**
     * Calculate days since last order
     */
    public function getDaysSinceLastOrder()
    {
        if (!$this->last_order_date) {
            return null;
        }
        return now()->diffInDays($this->last_order_date);
    }

    /**
     * Scope: Search by phone, name, or email
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('phone', 'LIKE', "%{$searchTerm}%")
              ->orWhere('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('email', 'LIKE', "%{$searchTerm}%")
              ->orWhere('company', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: Active customers only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By tier
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('customer_tier', $tier);
    }

    /**
     * Get customer display name with tier
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' ' . $this->getTierIcon();
    }

    /**
     * Get marketplace options
     */
    public static function getMarketplaceOptions()
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'walk_in' => 'Walk-in',
            'referral' => 'Referral',
            'website' => 'Website',
            'google' => 'Google Search',
            'repeat' => 'Repeat Customer',
            'other' => 'Other',
        ];
    }
}