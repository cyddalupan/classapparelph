<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Production Stage Timing — gaano katagal ang bawat production stage.
 *
 * Dalawang source (pinagsama):
 *   1. `ga_assignment_logs`  — GA stage actions (assigned/completed) para sa FOR SAMPLE / FOR FORMAT / PRINTING.
 *   2. `production_stage_logs` — bawat pagbabago ng production_stage (SEWING, QA, at iba pa) mula 2026-09-18.
 *
 * Metric (faithful sa model ni Andrew):
 *   duration(stage) = time(move-on) − time(first entry ng stage)
 *   move-on = pinakamaagang: (a) entry ng IBANG stage, o (b) 'completed' ng SAME stage.
 *   Kung wala pang move-on → "ongoing", bibilangin lang kung ito pa ang kasalukuyang
 *   production_stage ng sale.
 *
 * Read-only — walang binabago sa existing features.
 */
class ProductionStageTimingService
{
    /** Display order ng tracked stages. */
    public const STAGE_ORDER = ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING', 'CUTTING', 'SEWING', 'QA'];

    public const STAGE_LABELS = [
        'FOR SAMPLE'   => 'For Sample',
        'FOR APPROVAL' => 'For Approval',
        'FOR FORMAT'   => 'For Format',
        'PRINTING'     => 'Printing (For Print)',
        'CUTTING'      => 'Cutting',
        'SEWING'       => 'Sewing',
        'QA'           => 'QA (Quality Check)',
        'PRESSING'     => 'Pressing',
        'DISPATCH'     => 'Dispatch',
    ];

    /**
     * @param int|null $deptId  I-scope sa department (Class = 4), null = lahat.
     * @return array{stages: array, finishedAvgHours: float, slowestStage: ?string, ongoing: array, logFrom: ?string, logTo: ?string}
     */
    public static function stageAverages(?int $deptId = null): array
    {
        // Sale meta (para sa dept scope + kasalukuyang stage).
        $saleMeta = [];
        $sq = DB::table('prototype_sales')->select('id', 'production_stage', 'department_id');
        if ($deptId) {
            $sq->where('department_id', $deptId);
        }
        foreach ($sq->get() as $s) {
            $saleMeta[$s->id] = $s;
        }
        if (empty($saleMeta)) {
            return self::emptyResult();
        }
        $saleIds = array_keys($saleMeta);

        // Entries + exits per sale.
        $entries = []; $exits = [];

        // (1) GA assignment logs.
        $ga = DB::table('ga_assignment_logs')
            ->whereIn('prototype_sale_id', $saleIds)
            ->select('prototype_sale_id', 'stage', 'action', 'created_at')
            ->orderBy('created_at')->orderBy('id')
            ->get();
        foreach ($ga as $r) {
            $ts = strtotime($r->created_at);
            if ($r->action === 'assigned') {
                $entries[$r->prototype_sale_id][] = ['stage' => $r->stage, 't' => $ts];
            } elseif ($r->action === 'completed') {
                $exits[$r->prototype_sale_id][$r->stage][] = $ts;
            }
        }

        // (2) Production stage change logs (bago pa lang — SEWING/QA etc.).
        if (\Illuminate\Support\Facades\Schema::hasTable('production_stage_logs')) {
            $sl = DB::table('production_stage_logs')
                ->whereIn('prototype_sale_id', $saleIds)
                ->select('prototype_sale_id', 'to_stage', 'created_at')
                ->orderBy('created_at')->orderBy('id')
                ->get();
            foreach ($sl as $r) {
                if ($r->to_stage === null || $r->to_stage === '') continue;
                $entries[$r->prototype_sale_id][] = ['stage' => $r->to_stage, 't' => (int) strtotime($r->created_at)];
            }
        }

        $now = time();
        $finished = [];   // stage => [hours]
        $ongoing = [];    // ['sale','stage','hours']
        $logFrom = null; $logTo = null;

        foreach ($saleMeta as $saleId => $meta) {
            $ev = $entries[$saleId] ?? [];
            if (empty($ev)) continue;
            usort($ev, fn ($a, $b) => $a['t'] <=> $b['t']);
            $currentStage = $meta->production_stage;
            $n = count($ev);
            // Dedupe consecutive same-stage entries (keep earliest).
            $uniq = [];
            $lastStage = null;
            foreach ($ev as $e) {
                if ($e['stage'] === $lastStage && !empty($uniq)) continue;
                $uniq[] = $e; $lastStage = $e['stage'];
            }
            $ev = $uniq; $n = count($ev);

            for ($i = 0; $i < $n; $i++) {
                $e = $ev[$i];
                $stage = $e['stage']; $entry = $e['t'];
                if ($logFrom === null || $entry < $logFrom) $logFrom = $entry;
                if ($logTo === null || $entry > $logTo) $logTo = $entry;

                // move-on = earliest: next different-stage entry OR a same-stage 'completed'.
                $exit = null;
                for ($j = $i + 1; $j < $n; $j++) {
                    if ($ev[$j]['stage'] !== $stage) { $exit = $ev[$j]['t']; break; }
                }
                if (!empty($exits[$saleId][$stage])) {
                    foreach (array_reverse($exits[$saleId][$stage]) as $ct) {
                        if ($ct >= $entry && ($exit === null || $ct < $exit)) { $exit = $ct; }
                    }
                }

                if ($exit === null) {
                    if ($currentStage === $stage) {
                        $hrs = max(0, ($now - $entry) / 3600.0);
                        $ongoing[] = ['sale' => $saleId, 'stage' => $stage, 'hours' => $hrs];
                    }
                    continue;
                }
                $finished[$stage][] = max(0, ($exit - $entry) / 3600.0);
            }
        }

        // Aggregates.
        $present = array_unique(array_merge(array_keys($finished), array_column($ongoing, 'stage')));
        $orderList = self::STAGE_ORDER;
        foreach ($present as $p) {
            if (!in_array($p, $orderList, true)) $orderList[] = $p;
        }

        $stages = [];
        $allFinished = [];
        $slowest = null; $slowestAvg = -1;
        foreach ($orderList as $st) {
            $arr = $finished[$st] ?? [];
            sort($arr);
            $cnt = count($arr);
            $avg = $cnt ? array_sum($arr) / $cnt : null;
            $med = $cnt ? $arr[intdiv($cnt, 2)] : null;
            $max = $cnt ? max($arr) : null;
            $ongoingForStage = array_values(array_filter($ongoing, fn ($o) => $o['stage'] === $st));
            $longestOngoing = !empty($ongoingForStage) ? max(array_column($ongoingForStage, 'hours')) : null;

            $stages[$st] = [
                'label'  => self::STAGE_LABELS[$st] ?? $st,
                'count'  => $cnt,
                'avg_hours' => $avg,
                'median_hours' => $med,
                'max_hours' => $max,
                'ongoing' => count($ongoingForStage),
                'longest_ongoing_hours' => $longestOngoing,
            ];
            foreach ($arr as $v) $allFinished[] = $v;

            if ($avg !== null && $avg > $slowestAvg) { $slowestAvg = $avg; $slowest = $st; }
        }

        usort($ongoing, fn ($a, $b) => $b['hours'] <=> $a['hours']);

        return [
            'stages'           => $stages,
            'finishedAvgHours' => $allFinished ? array_sum($allFinished) / count($allFinished) : 0.0,
            'slowestStage'     => $slowest,
            'ongoing'          => $ongoing,
            'logFrom'          => $logFrom ? date('Y-m-d H:i:s', $logFrom) : null,
            'logTo'            => $logTo ? date('Y-m-d H:i:s', $logTo) : null,
        ];
    }

    private static function emptyResult(): array
    {
        $empty = [];
        foreach (self::STAGE_ORDER as $st) {
            $empty[$st] = ['label' => self::STAGE_LABELS[$st], 'count' => 0, 'avg_hours' => null, 'median_hours' => null, 'max_hours' => null, 'ongoing' => 0, 'longest_ongoing_hours' => null];
        }
        return ['stages' => $empty, 'finishedAvgHours' => 0.0, 'slowestStage' => null, 'ongoing' => [], 'logFrom' => null, 'logTo' => null];
    }

    /** Human-friendly duration: "0 sec", "3h 12m", "2d 4h". */
    public static function human(?float $hours): string
    {
        if ($hours === null) return '—';
        if ($hours < 1 / 60) return '0 sec';
        $totalMin = (int) round($hours * 60);
        if ($totalMin < 90) return $totalMin . 'm';
        $h = $hours;
        if ($h < 48) {
            $hh = (int) floor($h);
            $mm = (int) round(($h - $hh) * 60);
            return $hh . 'h' . ($mm ? ' ' . $mm . 'm' : '');
        }
        $d = (int) floor($h / 24);
        $rh = (int) floor($h - $d * 24);
        return $d . 'd' . ($rh ? ' ' . $rh . 'h' : '');
    }
}
