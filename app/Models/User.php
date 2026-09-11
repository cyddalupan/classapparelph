<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'company_name',
        'tax_id',
        'is_active',
        'last_login_at',
        'employee_id',
        'department',
        'sales_target',
        'commission_rate',
        'hire_date',
        'supervisor',
        'avatar',
        'position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'sales_target' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'hire_date' => 'date',
        ];
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has staff role
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user has customer role
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user has sales agent role
     * NOTE: 'hr_accountant_agent' (HR/Accountant/Agent) is treated as a sales agent
     * so it inherits ALL Sales Agent features (own customers/sales/layout/calendar).
     */
    public function isSalesAgent(): bool
    {
        return $this->role === 'sales_agent'
            || $this->role === 'sales_representative'
            || $this->role === 'hr_accountant_agent';
    }

    /**
     * Pricing tier: gumagamit ba ng AGENT pricing ang user?
     * Per-user override (`users.price_tier`); default 'sales'.
     * Sales price → default; Agent price → 'agent'.
     */
    public function usesAgentPricing(): bool
    {
        return ($this->price_tier ?? 'sales') === 'agent';
    }

    /**
     * Naka-link na account (parehong tao, magkaibang account — hal. Sales Agent <-> Agent).
     */
    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    /**
     * May naka-link na account ba (kaya may account switcher)?
     */
    public function canSwitchAccount(): bool
    {
        return !is_null($this->linked_user_id);
    }

    /**
     * Check if user has sales representative role
     */
    public function isSalesRepresentative(): bool
    {
        return $this->role === 'sales_representative';
    }

    /**
     * Check if user has procurement role
     */
    public function isProcurement(): bool
    {
        return $this->role === 'procurement';
    }

    /**
     * Check if user has artist role
     */
    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    /**
     * Check if user has GA (Graphic Artist / GA Agent) role
     */
    public function isGa(): bool
    {
        return $this->role === 'ga';
    }

    /**
     * Check if user has COO role
     */
    public function isCoo(): bool
    {
        return $this->role === 'coo';
    }

    /**
     * Check if user has CPO role
     */
    public function isCpo(): bool
    {
        return $this->role === 'cpo';
    }

    public function isCmo(): bool
    {
        return $this->role === 'cmo';
    }

    public function isProdManager(): bool
    {
        return $this->role === 'prod_manager';
    }

    /**
     * Check if user has QA (Quality Assurance / Sales Agent) role.
     * QA is configured like the Class Production Manager but with
     * restricted features (no dashboard, delay list, feedback list,
     * bell buttons, reprocess/add-on approval, refunds, calendar drag).
     */
    public function isQa(): bool
    {
        return $this->role === 'qa';
    }

    /**
     * Class-department-scoped roles: prod_manager + QA.
     * Both see ONLY the Class department (department_id = 4) sales.
     */
    public function isClassScoped(): bool
    {
        return $this->role === 'prod_manager' || $this->role === 'qa';
    }

    /**
     * Accountant — verifies payment review requests (balance close-out):
     * proof image check, then ACCEPT (zeroes balance → unlocks DONE)
     * or REJECT (required reason). Accepted requests go to CEO/COO queue.
     */
    public function isAccountant(): bool
    {
        return $this->role === 'accountant' || $this->role === 'hr_accountant_agent';
    }

    /**
     * HR/Accountant/Agent — combined role.
     * Agent (like Sales Agent) + Accountant (payment review + payment verification).
     * HR is title-only for now (no HR features yet).
     */
    public function isHrAccountantAgent(): bool
    {
        return $this->role === 'hr_accountant_agent';
    }

    /**
     * Manager-level access: admins, 'manager' role, and production managers.
     * Used by add-on approval, change/reprocess approval, refunds, etc.
     */
    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'prod_manager']);
    }

    /**
     * Get the user's avatar URL (or null when none uploaded).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    /**
     * Get the user's first name (first word of the full name).
     */
    public function getFirstNameAttribute(): string
    {
        return trim(explode(' ', $this->name)[0]) ?: $this->name;
    }

    /**
     * Get the display name: position (e.g. C.E.O.) when set, otherwise the name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->position ?: $this->name;
    }

    /**
     * Get a short label: "First Name - Position" (e.g. Andrew - C.E.O.)
     * when a position is set, otherwise the full name. Used in comments,
     * creation info and notifications so the full name isn't shown.
     */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->position) {
            return $this->first_name . ' - ' . $this->position;
        }
        return $this->name;
    }

    /**
     * Check if user can input sales
     */
    public function canInputSales(): bool
    {
        return in_array($this->role, ['admin', 'staff', 'sales_agent', 'sales_representative', 'coo', 'cpo', 'cmo']);
    }

    /**
     * Get sales agents (for dropdowns, etc.)
     */
    public static function salesAgents()
    {
        return self::whereIn('role', ['sales_agent', 'sales_representative'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
    }
}
