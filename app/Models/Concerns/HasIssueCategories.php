<?php

namespace App\Models\Concerns;

/**
 * Shared issue-category list para sa Damage Reports AT Production Feedback.
 *
 * ISANG source of truth ito — kapag nagdagdag/bumago ng category, DITO lang
 * baguhin, awtomatikong mag-sync ang dalawang dropdown. Andrew 2026-10-01.
 *
 * ⚠️ Huwag i-duplicate sa models. Ang existing keys ay dapat manatili
 * (para hindi masira ang lumang records na naka-save na).
 */
trait HasIssueCategories
{
    public const CATEGORIES = [
        // Files & Design
        'missing_file'          => 'Missing File',
        'wrong_file_sent'       => 'Wrong File Sent',
        'wrong_design'          => 'Wrong Design / Layout',
        'design_error'          => 'Design Error',
        'wrong_size'            => 'Wrong Size / Measurements',
        'wrong_color'           => 'Wrong Color',
        'typo_error'            => 'Typo / Spelling Error',
        'unapproved_design'     => 'Unapproved Design',

        // Production
        'production_error'      => 'Production Error',
        'print_quality_issue'   => 'Print Quality Issue',
        'quality_issue'         => 'Quality Issue',
        'rework'                => 'Rework',
        'machine_error'         => 'Machine Error',
        'material_defect'       => 'Material Defect',
        'wrong_quantity'        => 'Wrong Quantity',

        // Handling & Delivery
        'mishandling'           => 'Mishandling',
        'packaging_issue'       => 'Packaging Issue',
        'damage_in_transit'     => 'Damage in Transit',
        'late_delivery'         => 'Late Delivery / Delay',
        'wrong_shipping_address' => 'Wrong Shipping Address',

        // Communication & Process
        'no_response'           => 'No Response',
        'late_response'         => 'Late Response',
        'incomplete_info'       => 'Incomplete Info',
        'pricing_error'         => 'Pricing Error',
        'payment_issue'         => 'Payment Issue',
        'duplicate_order'       => 'Duplicate Order',
        'system_error'          => 'System Error',
        'other'                 => 'Other',
    ];
}
