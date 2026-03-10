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
     */
    public function isSalesAgent(): bool
    {
        return $this->role === 'sales_agent' || $this->role === 'sales_representative';
    }

    /**
     * Check if user has sales representative role
     */
    public function isSalesRepresentative(): bool
    {
        return $this->role === 'sales_representative';
    }

    /**
     * Check if user can input sales
     */
    public function canInputSales(): bool
    {
        return in_array($this->role, ['admin', 'staff', 'sales_agent', 'sales_representative']);
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
