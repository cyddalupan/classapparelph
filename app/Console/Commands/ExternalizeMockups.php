<?php

namespace App\Console\Commands;

use App\Support\MockupStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Externalize base64 mockup images out of the database into files.
 *
 * Idempotent: re-running only touches rows that still contain `data:image`
 * blobs. Always run with --dry-run first.
 *
 *   php artisan mockups:externalize --dry-run
 *   php artisan mockups:externalize --dry-run --sale=90
 *   php artisan mockups:externalize
 */
class ExternalizeMockups extends Command
{
    protected $signature = 'mockups:externalize
        {--dry-run : Report what would change without writing anything}
        {--sale= : Limit to a single prototype_sales id}
        {--chunk=200 : Rows per chunk}
        {--log= : Path for the JSON mapping log}';

    protected $description = 'Move base64 mockup images from the DB into storage/app/public and store URLs instead';

    /** Table => [columns..., folder-id column|null]. */
    private array $targets = [
        'prototype_sales'           => ['columns' => ['services', 'mockup_images'], 'sale_id' => 'id'],
        'prototype_sale_changes'    => ['columns' => ['services_before', 'services_after'], 'sale_id' => 'sale_id'],
        'prototype_sale_audit_logs' => ['columns' => ['details', 'description'], 'sale_id' => 'sale_id'],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        MockupStorage::$dryRun = $dry;
        $saleOnly = $this->option('sale');
        $chunk = max(1, (int) $this->option('chunk'));
        $logPath = $this->option('log')
            ?: storage_path('logs/mockup-externalize-' . date('Ymd-His') . ($dry ? '-dryrun' : '') . '.json');

        $this->info(($dry ? '[DRY RUN] ' : '[LIVE] ') . 'Externalizing base64 mockups → ' . MockupStorage::PREFIX);
        $mode = $dry ? 'DRY' : 'LIVE';
        $this->line('Log: ' . $logPath);
        $this->newLine();

        $summary = [];
        $log = [
            'mode'      => $mode,
            'started_at' => now()->toDateTimeString(),
            'targets'   => [],
        ];
        $grandBefore = 0;
        $grandAfter = 0;
        $grandFiles = 0;

        foreach ($this->targets as $table => $cfg) {
            $columns = $cfg['columns'];
            $folderCol = $cfg['sale_id'];

            // Only scan rows that actually still carry a base64 image blob.
            $select = array_merge(['id'], $columns);
            if ($folderCol !== 'id' && !in_array($folderCol, $select, true)) {
                $select[] = $folderCol;
            }
            $query = DB::table($table)->select($select);
            $blobLike = implode(' OR ', array_map(fn ($c) => "`$c` LIKE '%data:image%'", $columns));
            $query->whereRaw('(' . $blobLike . ')');
            if ($saleOnly && $folderCol === 'id') {
                $query->where('id', $saleOnly);
            } elseif ($saleOnly && $folderCol === 'sale_id') {
                $query->where('sale_id', $saleOnly);
            }

            $total = (clone $query)->count();
            if ($total === 0) {
                continue;
            }

            $this->info(sprintf('%s: %d row(s) with base64', $table, $total));
            $tableBefore = 0;
            $tableAfter = 0;
            $tableFiles = 0;
            $rowsTouched = 0;
            $entries = [];

            $query->orderBy('id')->chunkById($chunk, function ($rows) use (
                $table, $columns, $folderCol, $dry, &$tableBefore, &$tableAfter, &$tableFiles, &$rowsTouched, &$entries
            ) {
                foreach ($rows as $row) {
                    $update = [];
                    $rowEntries = [];

                    foreach ($columns as $col) {
                        $value = $row->$col ?? null;
                        if (!is_string($value) || !str_contains($value, 'data:image')) {
                            continue;
                        }

                        // Child tables reference the parent sale; file under that id.
                        $saleId = (int) ($row->$folderCol ?? $row->id);

                        [$newJson, $changed, $map] = MockupStorage::externalizeJson($value, $saleId, $col);
                        if (!$changed) {
                            continue;
                        }

                        $update[$col] = $newJson;
                        $tableBefore += strlen($value);
                        $tableAfter += strlen((string) $newJson);
                        $tableFiles += count($map);
                        $rowEntries = array_merge($rowEntries, $map);
                    }

                    if (empty($update)) {
                        continue;
                    }

                    $rowsTouched++;
                    if (!$dry) {
                        DB::table($table)->where('id', $row->id)->update($update);
                    }

                    $entries[] = [
                        'id'     => $row->id,
                        'fields' => array_keys($update),
                        'map'    => $rowEntries,
                    ];
                }
            });

            if ($entries === []) {
                continue;
            }

            $grandBefore += $tableBefore;
            $grandAfter  += $tableAfter;
            $grandFiles  += $tableFiles;

            $summary[] = [$table, $rowsTouched, $this->human($tableBefore), $this->human($tableAfter), $tableFiles];
            $log['targets'][$table] = [
                'rows_touched'  => $rowsTouched,
                'bytes_before'  => $tableBefore,
                'bytes_after'   => $tableAfter,
                'files_written' => $tableFiles,
                'rows'          => $entries,
            ];

            $this->line(sprintf(
                '  → %d row(s) %s | %s → %s | %d file(s)',
                $rowsTouched,
                $dry ? 'would change' : 'updated',
                $this->human($tableBefore),
                $this->human($tableAfter),
                $tableFiles
            ));
        }

        $this->newLine();
        if ($summary === []) {
            $this->info('Nothing to do — database is already clean of base64 mockups.');

            return self::SUCCESS;
        }

        $this->table(['Table', 'Rows', 'Before', 'After', 'Files'], $summary);
        $this->line(sprintf(
            'TOTAL: %s → %s (%d file(s)) — saved ~%s',
            $this->human($grandBefore),
            $this->human($grandAfter),
            $grandFiles,
            $this->human(max($grandBefore - $grandAfter, 0))
        ));

        $log['totals'] = [
            'bytes_before'  => $grandBefore,
            'bytes_after'   => $grandAfter,
            'files_written' => $grandFiles,
            'finished_at'   => now()->toDateTimeString(),
        ];
        @file_put_contents($logPath, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($dry) {
            $this->newLine();
            $this->warn('DRY RUN — nothing was written. Re-run without --dry-run to apply.');
        } else {
            $this->newLine();
            $this->info('Done. Mapping log: ' . $logPath);
        }

        return self::SUCCESS;
    }

    private function human(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
