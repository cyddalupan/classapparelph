<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Timeline ng pagbabago ng production_stage ng isang sale.
 * Additive (Andrew 2026-09-18) — pang-Stage Timing analytics.
 */
class ProductionStageLog extends Model
{
    protected $table = 'production_stage_logs';

    protected $fillable = [
        'prototype_sale_id',
        'from_stage',
        'to_stage',
        'changed_by',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * I-log ang stage change (tahimik — hindi makakaapekto sa main flow kahit mag-fail).
     */
    public static function record(PrototypeSale $sale, ?string $from, ?string $to, ?int $userId): void
    {
        $from = $from !== null ? trim($from) : null;
        $to = $to !== null ? trim($to) : null;
        if ($from === $to || $to === null || $to === '') {
            return;
        }
        try {
            static::create([
                'prototype_sale_id' => $sale->id,
                'from_stage' => $from ?: null,
                'to_stage' => $to,
                'changed_by' => $userId,
            ]);
        } catch (\Throwable $e) {
            // Huwag ipag-crash ang stage update kung mabigo ang logging.
            \Log::warning('production_stage_logs insert failed: ' . $e->getMessage());
        }
    }
}
