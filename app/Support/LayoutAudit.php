<?php

namespace App\Support;

use App\Models\LayoutJobAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Central writer para sa layout job / payout audit trail (Andrew 2026-10-21).
 *
 * Layunin: HINDI na kailangang mag-log ng bawat controller method — automatic
 * itong nakakakuha ng lahat ng pagbabago sa model (observer), at ng semantic
 * events (bayad, verified, rejected, natanggap) kapag tinawag nang explicit.
 *
 * Ang changes array ay may display-ready values na (pangalan ng user, format na
 * petsa) — snapshot ito, kaya kahit magpalit ng pangalan o ma-delete ang user ay
 * tama pa rin ang lumalabas sa history.
 */
class LayoutAudit
{
    /** Field → label sa UI */
    public const LABELS = [
        'job_no'                 => 'Job No',
        'type'                   => 'Type',
        'status'                 => 'Status',
        'sale_id'                => 'Sale link',
        'ga_user_id'             => 'Layout Doer',
        'customer_name'          => 'Customer',
        'notes'                  => 'Notes',
        'amount'                 => 'Amount',
        'amount_set_by'          => 'Amount set by',
        'amount_set_at'          => 'Amount set at',
        'created_by'             => 'Ginawa ni',
        'assigned_by'            => 'Assigned by',
        'assigned_at'            => 'Assigned at',
        'done_by'                => 'Done by',
        'done_at'                => 'Done at',
        'retagged_by'            => 'Retagged by',
        'retagged_at'            => 'Retagged at',
        'retag_count'            => 'Retag count',
        'retag_locked_until'     => 'Retag lock hanggang',
        'cancelled_by'           => 'Kinansela ni',
        'cancelled_at'           => 'Kinansela at',
        'cancel_reason'          => 'Dahilan ng cancel',
        'payment_account_id'     => 'Payment account',
        'payment_reference'      => 'Payment reference',
        'payment_proof_path'     => 'Payment proof',
        'payment_status'         => 'Payment status',
        'payment_verified_by'    => 'Payment verified by',
        'payment_verified_at'    => 'Payment verified at',
        'payment_reject_reason'  => 'Reject reason',
        'last_reject_reason'     => 'Huling reject reason',
        'last_reject_by'         => 'Huling reject ni',
        'last_reject_at'         => 'Huling reject at',
        'resubmitted_by'         => 'Re-upload ni',
        'resubmitted_at'         => 'Re-upload at',
        'payout_id'              => 'Payout',
        // payouts
        'requested_by'           => 'Requested by',
        'requested_at'           => 'Requested at',
        'request_notes'          => 'Request notes',
        'account_name'           => 'Account name',
        'account_number'         => 'Account number',
        'account_proof_path'     => 'Account proof',
        'payment_method'         => 'Payment method',
        'paid_by'                => 'Bayad ni',
        'paid_at'                => 'Bayad at',
        'verified_by'            => 'Verified by',
        'verified_at'            => 'Verified at',
        'received_by'            => 'Natanggap ni',
        'received_at'            => 'Natanggap at',
        'reject_reason'          => 'Reject reason',
    ];

    /** Fields na hindi na kailangang i-log (ingay lang) */
    private const IGNORED = ['updated_at', 'created_at', 'remember_token'];

    /** Idempotent guard para hindi mag-doble sa isang request */
    private static array $logged = [];

    /**
     * Automatic na pag-log ng model changes (tinatawag ng observers).
     */
    public static function fromModel(Model $model, string $event): void
    {
        if ($event === 'created') {
            $changes = self::snapshot($model);
        } elseif ($event === 'updated') {
            $changes = self::diff($model->getChanges(), $model);
        } else {
            $changes = [];
        }

        if ($event === 'deleted') {
            $changes = [['field' => 'deleted', 'label' => 'Record', 'old' => self::subjectLabel($model), 'new' => 'binura']];
        }

        if (!$changes) {
            return;
        }

        self::write($model, $event, $changes);
    }

    /**
     * Explicit na event (hal. 'paid', 'verified', 'rejected', 'confirmed')
     * — para madaling basahin sa history kahit walang field change.
     */
    public static function event(Model $model, string $event, array $changes = [], array $meta = []): void
    {
        self::write($model, $event, $changes ?: [[
            'field' => 'status', 'label' => 'Status',
            'old' => null, 'new' => $event,
        ]], $meta);
    }

    /* ------------------------------------------------------------------ */

    private static function write(Model $model, string $event, array $changes, array $meta = []): void
    {
        $user = auth()->user();
        $type = $model instanceof \App\Models\LayoutJobPayout ? 'payout' : 'job';

        // Anti-duplicate: same subject + event + changes sa isang request.
        $key = $type . ':' . $model->getKey() . ':' . $event . ':' . md5(json_encode($changes));
        if (isset(self::$logged[$key])) {
            return;
        }
        self::$logged[$key] = true;

        LayoutJobAudit::create([
            'subject_type' => $type,
            'subject_id'   => $model->getKey(),
            'job_no'       => $type === 'job' ? ($model->job_no ?? null) : null,
            'event'        => $event,
            'changes'      => $changes,
            'meta'         => $meta ?: null,
            'user_id'      => $user?->id,
            'user_name'    => $user?->name ?? 'system',
            'ip'           => request()?->ip(),
            'created_at'   => now(),
        ]);
    }

    /** Buong snapshot para sa 'created' */
    private static function snapshot(Model $model): array
    {
        $keys = $model instanceof \App\Models\LayoutJobPayout
            ? ['ga_user_id', 'amount', 'status', 'account_name', 'account_number', 'request_notes']
            : ['job_no', 'type', 'status', 'ga_user_id', 'amount', 'sale_id'];

        $out = [];
        foreach ($keys as $k) {
            $val = self::display($k, $model->{$k} ?? null, $model);
            if ($val === '—') {
                continue;
            }
            $out[] = ['field' => $k, 'label' => self::LABELS[$k] ?? $k, 'old' => null, 'new' => $val];
        }

        return $out;
    }

    /** Diff ng mga nagbago, display-ready ang values */
    private static function diff(array $dirty, Model $model): array
    {
        $out = [];
        foreach ($dirty as $field => $new) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }
            $out[] = [
                'field' => $field,
                'label' => self::LABELS[$field] ?? $field,
                'old'   => self::display($field, $model->getOriginal($field), $model),
                'new'   => self::display($field, $new, $model),
            ];
        }

        return $out;
    }

    /** Value → readable string (pangalan ng user, petsa, pera) */
    private static function display(string $field, $value, Model $model): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        // User references → pangalan
        if (preg_match('/_by$/', $field) || in_array($field, ['ga_user_id'], true)) {
            $name = User::whereKey($value)->value('name');

            return $name ?: ('user #' . $value);
        }
        if ($field === 'payment_account_id') {
            $name = \App\Models\PaymentAccount::whereKey($value)->value('name');

            return $name ?: ('account #' . $value);
        }
        if ($field === 'sale_id') {
            $no = \App\Models\Sale::whereKey($value)->value('sale_no');

            return $no ? ('Sale #' . $no) : ('sale #' . $value);
        }
        if ($field === 'payout_id') {
            return 'Payout #' . $value;
        }
        // Petsa (may _at suffix) → Manila time
        if (preg_match('/_at$/', $field) || $field === 'retag_locked_until') {
            try {
                return \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Manila')->format('M d, Y g:i A');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        }
        if (in_array($field, ['amount'], true)) {
            return '₱' . number_format((float) $value, 2);
        }
        if (is_bool($value)) {
            return $value ? 'oo' : 'hindi';
        }

        return (string) $value;
    }

    private static function subjectLabel(Model $model): string
    {
        return $model instanceof \App\Models\LayoutJobPayout
            ? ('payout #' . $model->getKey())
            : ('job ' . ($model->job_no ?? ('#' . $model->getKey())));
    }
}
