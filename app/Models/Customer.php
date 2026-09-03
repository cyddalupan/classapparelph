<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id_number',
        'name',
        'phone',
        'marketplace',
        'email',
        'location',
        'address',
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

    public function sales()
    {
        return $this->hasMany(PrototypeSaleHeader::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Tier thresholds
    const TIER_BRONZE = 'bronze';    // < ₱10,000
    const TIER_SILVER = 'silver';    // ₱10,000 - ₱50,000
    const TIER_GOLD = 'gold';        // ₱50,000 - ₱200,000
    const TIER_PLATINUM = 'platinum'; // > ₱200,000

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            // Auto-generate customer ID number
            $latest = static::withTrashed()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $customer->customer_id_number = 'CLASS-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Determine whether a phone value is a placeholder (no real number),
     * e.g. "N/A", "NA", "-", "none", or client-side "+63N/A" artifacts.
     * Placeholder phones must NEVER auto-link a sale to an existing customer
     * via phone match — that UNIQUE-column collision merged unrelated buyers
     * into one customer record (MAAM FAITHFUL / id 3).
     */
    public static function isPlaceholderPhone($phone)
    {
        if ($phone === null) {
            return true;
        }
        $phone = trim((string) $phone);
        if ($phone === '') {
            return true;
        }
        // No digits at all -> cannot be a real contact number
        if (!preg_match('/[0-9]/', $phone)) {
            return true;
        }
        // Tolerate artifacts like "+63N/A": check the letters-only core
        $alpha = strtolower($phone);
        $alpha = preg_replace('/[^a-z]/', '', $alpha);
        return in_array($alpha, ['na', 'none', 'nil', 'null', 'xxx', 'unknown', 'nophone', 'noprovided'], true);
    }

    /**
     * Generate a UNIQUE placeholder phone (letters only, digit-free so it stays
     * classified as a placeholder). Used when a buyer has no real phone number.
     */
    public static function uniquePlaceholderPhone($prefix = 'N/A')
    {
        do {
            $suffix = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 5));
            $candidate = $prefix . '-' . $suffix;
        } while (static::withTrashed()->where('phone', $candidate)->exists());
        return $candidate;
    }

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
        return round(now()->diffInDays($this->last_order_date), 2);
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
        // Source/channel only — repeat customer detection is automatic via Customer->sales() count
        return [
            'MP' => 'MP',
            'PAGE' => 'PAGE',
            'WALKIN' => 'WALKIN',
            'SHOPPEE' => 'SHOPPEE',
            'MARKETING-MP' => 'MARKETING-MP',
            'VIBER' => 'VIBER',
            'MP-PAGE' => 'MP-PAGE',
        ];
    }
}