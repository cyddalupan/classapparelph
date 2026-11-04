<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintingSetting extends Model
{
    protected $fillable = [
        'print_type',
        'bulk_requires_large_size',
        'bulk_gate_sizes',
    ];

    protected $casts = [
        'bulk_requires_large_size' => 'boolean',
    ];

    /**
     * Get (or lazily create) the settings row for a print type.
     */
    public static function forType(string $printType): self
    {
        return static::firstOrCreate(
            ['print_type' => $printType],
            ['bulk_requires_large_size' => false, 'bulk_gate_sizes' => 'A3,A4']
        );
    }

    /**
     * Size names that satisfy the bulk gate.
     */
    public function gateSizeNames(): array
    {
        return collect(explode(',', (string) $this->bulk_gate_sizes))
            ->map(fn ($s) => strtoupper(trim($s)))
            ->filter()
            ->values()
            ->all();
    }
}
