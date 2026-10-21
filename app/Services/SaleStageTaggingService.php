<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sale Stage Tagging Timeline — per-sale, read-only na talaan ng bawat production stage tag.
 *
 * Pinagsama ang dalawang READ-ONLY source (kapareho ng ginagamit ng
 * ProductionStageTimingService, pero PER-SALE ang output):
 *   1. `ga_assignment_logs`    — GA stage actions (assigned/completed) sa FOR SAMPLE / FOR FORMAT / PRINTING
 *   2. `production_stage_logs` — bawat production_stage change (mula 2026-09-18)
 *
 * Ibinibigay: stage, sino ang nag-tag, anong oras na-tag, at gaano katagal
 * (duration = move-on − first entry ng stage, ongoing kung wala pang move-on).
 *
 * ⚠️ Purely additive at display-only — walang binabago sa existing tables/features.
 * (Andrew/CEO 2026-09-22)
 */
class SaleStageTaggingService
{
    /**
     * Timeline ng stage tags para sa isang sale.
     *
     * @return array<int, array{stage:string,label:string,tagged_at:string,tagged_by:string,ga:?string,hours:float,duration:string,ongoing:bool,current:bool}>
     */
    public static function forSale(int $saleId): array
    {
        $entries = [];   // [['stage','t','by','ga'], ...]
        $exits   = [];   // stage => [timestamps] (GA 'completed' actions)

        // (1) GA assignment logs.
        if (Schema::hasTable('ga_assignment_logs')) {
            $ga = DB::table('ga_assignment_logs')
                ->where('prototype_sale_id', $saleId)
                ->orderBy('created_at')->orderBy('id')
                ->get(['stage', 'action', 'user_id', 'actor_id', 'created_at']);
            foreach ($ga as $r) {
                $stage = trim((string) $r->stage) !== '' ? $r->stage : '—';
                $t = (int) strtotime($r->created_at);
                if ($r->action === 'assigned') {
                    $entries[] = [
                        'stage' => $stage,
                        't'     => $t,
                        'by'    => $r->actor_id ?: $r->user_id,
                        'ga'    => $r->user_id,
                    ];
                } elseif ($r->action === 'completed') {
                    $exits[$stage][] = $t;
                }
            }
        }

        // (2) Production stage change logs (SEWING / QA / CUTTING / atbp.).
        if (Schema::hasTable('production_stage_logs')) {
            $sl = DB::table('production_stage_logs')
                ->where('prototype_sale_id', $saleId)
                ->orderBy('created_at')->orderBy('id')
                ->get(['to_stage', 'changed_by', 'created_at']);
            foreach ($sl as $r) {
                if ($r->to_stage === null || $r->to_stage === '') continue;
                $entries[] = [
                    'stage' => $r->to_stage,
                    't'     => (int) strtotime($r->created_at),
                    'by'    => $r->changed_by,
                    'ga'    => null,
                ];
            }
        }

        // ── (3) Priority tag events (Prio 1..15) — ADDITIVE (Andrew 2026-10-08) ──
        // Isinasama sa timeline ang bawat pag-tag / pag-change / pag-alis ng Prio.
        // Read-only; HINDI ito kasama sa duration computation ng stages (hiwalay na rows).
        $prioRows = [];
        if (Schema::hasTable('prototype_sale_audit_logs')) {
            $pl = DB::table('prototype_sale_audit_logs')
                ->where('sale_id', $saleId)
                ->whereIn('action', ['priority_tagged', 'priority_forced', 'priority_cleared'])
                ->orderBy('created_at')->orderBy('id')
                ->get(['action', 'description', 'details', 'user_id', 'created_at']);
            foreach ($pl as $r) {
                $prio = null;
                if (!empty($r->details)) {
                    $d = json_decode($r->details, true);
                    if (is_array($d) && isset($d['priority'])) {
                        $prio = (int) $d['priority'];
                    }
                }
                $prioRows[] = [
                    'prio'   => $prio,
                    't'      => (int) strtotime($r->created_at),
                    'by'     => $r->user_id,
                    'action' => $r->action,
                ];
            }
        }

        // Backfill: kung may Prio na ang sale pero wala pang naka-log na priority event
        // (mga na-tag bago ang logging — hal. 2026-10-08), gamitin ang priority_set_at.
        if (empty($prioRows)) {
            $ps = DB::table('prototype_sales')->where('id', $saleId)->first(['priority', 'priority_set_at']);
            if ($ps && !empty($ps->priority) && !empty($ps->priority_set_at)) {
                $prioRows[] = [
                    'prio'   => (int) $ps->priority,
                    't'      => (int) strtotime($ps->priority_set_at),
                    'by'     => null,
                    'action' => 'priority_tagged',
                ];
            }
        }

        if (empty($entries) && empty($prioRows)) {
            return [];
        }

        usort($entries, fn ($a, $b) => $a['t'] <=> $b['t']);

        // Dedupe magkasunod na parehong stage (panatilihin ang pinakauna) — tulad ng
        // ProductionStageTimingService para pare-pareho ang bilang.
        $uniq = []; $lastStage = null;
        foreach ($entries as $e) {
            if ($e['stage'] === $lastStage && !empty($uniq)) continue;
            $uniq[] = $e; $lastStage = $e['stage'];
        }
        $entries = $uniq;
        $n = count($entries);

        $sale = DB::table('prototype_sales')->where('id', $saleId)->first(['production_stage']);
        $currentStage = $sale->production_stage ?? null;
        $now = time();
        $names = DB::table('users')->pluck('name', 'id')->all();

        $rows = [];
        for ($i = 0; $i < $n; $i++) {
            $e = $entries[$i];
            $stage = $e['stage'];
            $entry = $e['t'];

            // move-on = pinakamaagang: (a) entry ng IBANG stage, o (b) 'completed' ng SAME stage.
            $exit = null;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($entries[$j]['stage'] !== $stage) { $exit = $entries[$j]['t']; break; }
            }
            if (!empty($exits[$stage])) {
                foreach (array_reverse($exits[$stage]) as $ct) {
                    if ($ct >= $entry && ($exit === null || $ct < $exit)) $exit = $ct;
                }
            }

            $ongoing = ($exit === null);
            $seconds = $ongoing ? max(0, $now - $entry) : max(0, $exit - $entry);
            $hours = $seconds / 3600.0;

            $rows[] = [
                'stage'     => $stage,
                'label'     => ProductionStageTimingService::STAGE_LABELS[$stage] ?? $stage,
                'tagged_at' => date('M d, Y g:i A', $entry),
                'tagged_by' => $e['by'] ? ($names[$e['by']] ?? ('User #' . $e['by'])) : '—',
                'ga'        => $e['ga'] ? ($names[$e['ga']] ?? ('User #' . $e['ga'])) : null,
                'hours'     => $hours,
                'duration'  => ProductionStageTimingService::human($hours),
                'ongoing'   => $ongoing,
                'current'   => $ongoing && $currentStage === $stage,
                'is_prio'   => false,
                't'         => $entry,
            ];
        }

        // Priority rows (hiwalay sa stage rows — walang tagal, may ⭐ badge).
        foreach ($prioRows as $pr) {
            $rows[] = [
                'stage'     => 'PRIO:' . ($pr['prio'] ?? 'X'),
                'label'     => $pr['prio'] ? ('⭐ Prio ' . $pr['prio']) : '⭐ Prio —',
                'tagged_at' => date('M d, Y g:i A', $pr['t']),
                'tagged_by' => $pr['by'] ? ($names[$pr['by']] ?? ('User #' . $pr['by'])) : '—',
                'ga'        => null,
                'hours'     => null,
                'duration'  => '—',
                'ongoing'   => false,
                'current'   => false,
                'is_prio'   => true,
                't'         => $pr['t'],
            ];
        }

        // Pagsamahin at ayusin ayon sa oras (stage + priority events).
        usort($rows, fn ($a, $b) => ($a['t'] ?? 0) <=> ($b['t'] ?? 0));

        return $rows;
    }
}
