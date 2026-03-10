<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@classapparelph.com',
            'password' => Hash::make('AdminPassword123!'),
            'role' => 'admin',
            'phone' => '+639123456789',
            'address' => 'Maysan, Valenzuela, Philippines',
            'company_name' => 'CLASS Apparel PH',
            'tax_id' => '123-456-789-000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create staff user
        User::create([
            'name' => 'Production Staff',
            'email' => 'staff@classapparelph.com',
            'password' => Hash::make('StaffPassword123!'),
            'role' => 'staff',
            'phone' => '+639987654321',
            'address' => 'Valenzuela, Philippines',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample customer
        User::create([
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('CustomerPassword123!'),
            'role' => 'customer',
            'phone' => '+639555123456',
            'address' => 'Manila, Philippines',
            'company_name' => 'ABC Corporation',
            'tax_id' => '987-654-321-000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin, staff, and customer users created successfully!');
    }
}
