<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Seed existing prototype_sales payment data into prototype_payments
        $sales = DB::table('prototype_sales')
            ->where(function ($q) {
                $q->whereNotNull('payment_account_id')
                  ->orWhere('deposit_paid', '>', 0);
            })
            ->get();

        $count = 0;
        foreach ($sales as $sale) {
            // Skip if already migrated (has payments)
            $existing = DB::table('prototype_payments')
                ->where('prototype_sale_id', $sale->id)
                ->count();
            if ($existing > 0) continue;

            // Determine payment_type based on deposit_paid vs total_amount
            $paymentType = 'down_payment';
            if ($sale->deposit_paid >= $sale->total_amount) {
                $paymentType = 'full_payment';
            } elseif ($sale->deposit_paid > 0) {
                $paymentType = $sale->payment_status === 'down_payment_verified' ? 'down_payment' : 'additional';
            }

            // Map legacy status
            $legacyStatus = $sale->payment_status;
            $mappedStatus = $legacyStatus;
            if (in_array($legacyStatus, ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])) {
                // Keep as-is for verified
            } elseif ($legacyStatus === 'pending' || $legacyStatus === 'rejected') {
                // Keep as-is
            } else {
                $mappedStatus = 'pending';
            }

            DB::table('prototype_payments')->insert([
                'prototype_sale_id' => $sale->id,
                'payment_type' => $paymentType,
                'amount' => $sale->deposit_paid ?? 0,
                'payment_method' => $sale->payment_method ?? 'cash',
                'payment_account_id' => $sale->payment_account_id,
                'reference_number' => $sale->reference_number,
                'screenshot_path' => $sale->payment_screenshot_path,
                'payment_status' => $mappedStatus,
                'verified_by' => $sale->verified_by,
                'verified_at' => $sale->verified_at,
                'payment_date' => $sale->payment_date,
                'notes' => 'Migrated from legacy sale record',
                'created_at' => $sale->created_at ?? now(),
                'updated_at' => $sale->updated_at ?? now(),
            ]);
            $count++;
        }

        echo "Seeded {$count} existing payments into prototype_payments.\n";
    }

    public function down(): void
    {
        // Can't easily reverse — keep data
    }
};
