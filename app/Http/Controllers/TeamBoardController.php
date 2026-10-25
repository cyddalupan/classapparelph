<?php

namespace App\Http\Controllers;

use App\Models\PrototypeSale;
use App\Models\SalesAnnouncement;
use App\Models\SalesBoardMemberGroup;
use App\Models\SalesBoardSetting;
use App\Models\SalesQuota;
use App\Models\SalesTeamQuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * "Team Board" — live, motivational dashboard para sa Sales Agents (Andrew 2026-10-15).
 *
 * Read-only para sa agents: kita nila ang sariling quota + progress, ang quota ng iba,
 * leaderboard, at announcements. Si admin lang ang nagse-set ng quota at nagpo-post ng
 * announcements (Control Panel).
 *
 * ADDITIVE lang ito: bagong tables/models/routes/views. Walang binabago sa existing
 * sales queries — ang revenue ay kinukuha mula sa prototype_sales (total_amount per agent
 * sa loob ng period). Kung may problema, isang linya lang (sidebar link) ang aalisin.
 */
class TeamBoardController extends Controller
{
    /** Roles na pinapayagang makita ang Team Board. */
    private function canViewBoard(User $user): bool
    {
        if ($user->isSalesAgent()
            || $user->isSalesRepresentative()
            || $user->isAdmin()
            || $user->isCoo()) {
            return true;
        }

        // Lahat ng nagbebenta dapat makapasok — kahit anong role (hal. QA/Sales Agent).
        // Andrew 2026-10-22.
        if (stripos((string) $user->position, 'sales') !== false) {
            return true;
        }

        return PrototypeSale::query()
            ->where('sales_agent_id', $user->id)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    /** Admin-only: control panel + announcements. */
    private function canManage(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Start/end ng kasalukuyang buwan (Asia/Manila app tz). */
    private function currentPeriod(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return [$start->toDateString(), $end->toDateString()];
    }

    /**
     * Listahan ng mga agent para sa quota grid + leaderboard base.
     * Kasama ang (a) roles na sales_agent/sales_representative, at (b) KAHIT SINONG
     * may sale — anong role man (QA, HR/Accountant, CEO/COO/CPO/CMO, admin, atbp.)
     * — Andrew 2026-10-15. Pipiliin ni admin sa grouping kung ipapakita o hindi.
     */
    private function agentUsers()
    {
        $roleIds = User::query()
            ->whereIn('role', ['sales_agent', 'sales_representative'])
            ->pluck('id');

        $sellerIds = PrototypeSale::query()
            ->whereNotNull('sales_agent_id')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->distinct()
            ->pluck('sales_agent_id');

        $ids = $roleIds->merge($sellerIds)->unique()->filter()->values()->all();

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'position', 'avatar']);
    }

    /**
     * Revenue per agent for a period: sum ng total_amount ng sales na ginawa sa loob
     * ng period. Hindi kasama ang cancelled / archived / deleted.
     *
     * @return array<int,float>  [user_id => revenue]
     */
    private function revenueByAgent(string $start, string $end): array
    {
        $rows = PrototypeSale::query()
            ->whereNotNull('sales_agent_id')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('sales_agent_id')
            ->selectRaw('sales_agent_id, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->sales_agent_id] = [
                'revenue' => (float) $r->revenue,
                'orders' => (int) $r->orders,
            ];
        }

        return $out;
    }

    /** Revenue per agent sa isang date range: [user_id => revenue]. */
    private function revenueByAgentRange(string $start, string $end): array
    {
        $rows = PrototypeSale::query()
            ->whereNotNull('sales_agent_id')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('sales_agent_id')
            ->selectRaw('sales_agent_id, SUM(total_amount) as revenue')
            ->pluck('revenue', 'sales_agent_id');

        $out = [];
        foreach ($rows as $uid => $rev) {
            $out[(int) $uid] = (float) $rev;
        }

        return $out;
    }

    /**
     * Streak (buwan-buwan) na na-hit ang quota — kasama ang kasalukuyang buwan.
     * Batch queries lang para hindi bumagal.
     *
     * @param  array<int>  $userIds
     * @return array<int,int>  [user_id => streak months]
     */
    private function quotaStreaks(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $cursor = Carbon::now()->startOfMonth();
        $starts = [];
        for ($i = 0; $i < 12; $i++) {
            $starts[] = $cursor->copy()->subMonths($i)->toDateString();
        }
        $earliest = end($starts);

        $qmap = [];
        foreach (SalesQuota::query()
            ->whereIn('user_id', $userIds)
            ->where('period_type', 'monthly')
            ->where('active', true)
            ->whereDate('period_start', '>=', $earliest)
            ->get(['user_id', 'period_start', 'quota_amount']) as $q) {
            $qmap[(int) $q->user_id][Carbon::parse($q->period_start)->toDateString()] = (float) $q->quota_amount;
        }

        $rmap = [];
        foreach (PrototypeSale::query()
            ->whereIn('sales_agent_id', $userIds)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $earliest)
            ->selectRaw("sales_agent_id, DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total_amount) as revenue")
            ->groupBy('sales_agent_id', 'ym')
            ->get() as $r) {
            $rmap[(int) $r->sales_agent_id][$r->ym] = (float) $r->revenue;
        }

        $out = [];
        foreach ($userIds as $uid) {
            $streak = 0;
            foreach ($starts as $s) {
                $q = $qmap[$uid][$s] ?? 0;
                if ($q <= 0) {
                    break;
                }
                $ym = Carbon::parse($s)->format('Y-m');
                if (($rmap[$uid][$ym] ?? 0) >= $q) {
                    $streak++;
                } else {
                    break;
                }
            }
            $out[$uid] = $streak;
        }

        return $out;
    }

    /**
     * Ilang sunod-sunod na araw nang #1 ang champion (Andrew 2026-10-22).
     * Basis: cumulative monthly revenue at the end of each day — sino ang leader.
     */
    private function topStreakDays(array $visibleRows, ?int $championId): int
    {
        if (!$championId || empty($visibleRows)) {
            return 0;
        }

        $ids = array_map(fn ($r) => (int) $r['user_id'], $visibleRows);
        [$start] = $this->currentPeriod();
        $today = Carbon::now()->startOfDay();

        $daily = [];
        foreach (PrototypeSale::query()
            ->whereIn('sales_agent_id', $ids)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $today->toDateString())
            ->selectRaw('sales_agent_id, DATE(created_at) as d, SUM(total_amount) as rev')
            ->groupBy('sales_agent_id', 'd')
            ->get() as $r) {
            $daily[(int) $r->sales_agent_id][(string) $r->d] = (float) $r->rev;
        }

        $cum = array_fill_keys($ids, 0.0);
        $leaderByDay = [];
        for ($d = Carbon::parse($start)->startOfDay(); $d->lte($today); $d->addDay()) {
            $key = $d->toDateString();
            foreach ($ids as $id) {
                $cum[$id] += $daily[$id][$key] ?? 0;
            }
            $best = null;
            $bestRev = -1.0;
            foreach ($ids as $id) {
                if ($cum[$id] > $bestRev) {
                    $bestRev = $cum[$id];
                    $best = $id;
                }
            }
            if ($bestRev > 0) {
                $leaderByDay[$key] = $best;
            }
        }

        $streak = 0;
        for ($d = $today->copy(); $d->gte(Carbon::parse($start)); $d->subDay()) {
            if (($leaderByDay[$d->toDateString()] ?? null) !== $championId) {
                break;
            }
            $streak++;
        }

        return $streak;
    }

    /** Lider ng kasalukuyang linggo (Mon–Sun) — fresh start bawat Lunes. */
    private function weekLeader(array $visibleRows): ?array
    {
        $ids = array_map(fn ($r) => (int) $r['user_id'], $visibleRows);
        if (empty($ids)) {
            return null;
        }

        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = Carbon::now();

        $row = PrototypeSale::query()
            ->whereIn('sales_agent_id', $ids)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->whereDate('created_at', '<=', $end->toDateString())
            ->groupBy('sales_agent_id')
            ->selectRaw('sales_agent_id, SUM(total_amount) as rev')
            ->orderByDesc('rev')
            ->first();

        if (!$row || (float) $row->rev <= 0) {
            return null;
        }

        $hit = collect($visibleRows)->firstWhere('user_id', (int) $row->sales_agent_id);
        if (!$hit) {
            return null;
        }

        return [
            'name' => $hit['name'] ?? '—',
            'initials' => $hit['initials'] ?? null,
            'avatar' => $hit['avatar'] ?? null,
            'amount' => (float) $row->rev,
            'range' => $start->format('M j') . ' – ' . $end->format('M j'),
            'ends_in' => max(0, (int) Carbon::now()->startOfDay()
                ->diffInDays($start->copy()->addDays(7)->startOfDay())),
        ];
    }

    /** Champion ng nakaraang buwan (Hall of Fame). */
    private function lastMonthChampion(): ?array
    {
        $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $row = PrototypeSale::query()
            ->whereNotNull('sales_agent_id')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->whereDate('created_at', '<=', $end->toDateString())
            ->groupBy('sales_agent_id')
            ->selectRaw('sales_agent_id, SUM(total_amount) as revenue')
            ->orderByDesc('revenue')
            ->first();

        if (!$row || (float) $row->revenue <= 0) {
            return null;
        }

        $user = User::find($row->sales_agent_id);

        return [
            'name' => $user->name ?? '—',
            'amount' => (float) $row->revenue,
            'label' => $start->format('F Y'),
        ];
    }

    /** Buuin ang buong board payload (ginagamit ng view at ng live JSON). */
    private function buildPayload(): array
    {
        $settings = SalesBoardSetting::current();
        [$start, $end] = $this->currentPeriod();
        $agents = $this->agentUsers();
        $rev = $this->revenueByAgent($start, $end);

        // Quotas for this period keyed by user_id.
        $quotas = SalesQuota::query()
            ->where('period_type', 'monthly')
            ->whereDate('period_start', $start)
            ->where('active', true)
            ->get()
            ->keyBy('user_id');

        $rows = [];
        $assignments = SalesBoardMemberGroup::query()->get()->keyBy('user_id');

        // Weekly momentum (para sa Top Mover badge).
        $thisWeek = $this->revenueByAgentRange(
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString()
        );
        $lastWeek = $this->revenueByAgentRange(
            Carbon::now()->subWeek()->startOfWeek()->toDateString(),
            Carbon::now()->subWeek()->endOfWeek()->toDateString()
        );
        $streaks = $this->quotaStreaks($agents->pluck('id')->all());

        foreach ($agents as $a) {
            $revenue = (float) ($rev[$a->id]['revenue'] ?? 0);
            $orders = (int) ($rev[$a->id]['orders'] ?? 0);
            $quota = $quotas->has($a->id) ? (float) $quotas[$a->id]->quota_amount : 0.0;
            $pct = $quota > 0 ? min(100, round(($revenue / $quota) * 100, 1)) : 0.0;

            $assigned = $assignments->get($a->id);
            $groupName = $assigned && $assigned->group_name !== null && $assigned->group_name !== ''
                ? $assigned->group_name
                : SalesBoardMemberGroup::suggestGroup($a->position);
            $visible = $assigned ? (bool) $assigned->visible : true;

            $tw = (float) ($thisWeek[$a->id] ?? 0);
            $lw = (float) ($lastWeek[$a->id] ?? 0);

            $rows[] = [
                'user_id' => $a->id,
                'name' => $a->name,
                'position' => $a->position,
                'group' => $groupName,
                'visible' => $visible,
                'revenue' => $revenue,
                'orders' => $orders,
                'quota' => $quota,
                'pct' => $pct,
                'remaining' => max($quota - $revenue, 0),
                'has_quota' => $quota > 0,
                'initials' => $this->initials($a->name),
                'avatar' => $a->avatar_url,
                'week_revenue' => $tw,
                'week_delta' => $tw - $lw,
                'streak' => (int) ($streaks[$a->id] ?? 0),
            ];
        }

        // Rank by revenue desc — VISIBLE agents LANG (Andrew 2026-10-15).
        // Kapag tinanggal / na-uncheck sa grouping (visible=0), hindi na kasama sa
        // ranking, champion, at podium — ang matotop lang ay yung may check.
        $visibleRows = array_values(array_filter($rows, fn ($r) => !empty($r['visible'])));
        usort($visibleRows, fn ($x, $y) => $y['revenue'] <=> $x['revenue']);
        $rank = 0;
        foreach ($visibleRows as &$r) {
            $rank++;
            $r['rank'] = $rank;
        }
        unset($r);

        // I-map ang rank sa lahat ng rows (null kapag hidden) para sa 'me' lookup.
        $rankByUser = [];
        $gapByUser = [];
        $overtakeByUser = [];
        $prevRanked = null;
        foreach ($visibleRows as $r) {
            $rankByUser[$r['user_id']] = $r['rank'];
            $gapByUser[$r['user_id']] = $prevRanked
                ? max(0.0, round((float) $prevRanked['revenue'] - (float) $r['revenue'], 2))
                : 0.0;
            $overtakeByUser[$r['user_id']] = $prevRanked['name'] ?? null;
            $prevRanked = $r;
        }
        foreach ($rows as &$r) {
            $r['rank'] = $rankByUser[$r['user_id']] ?? null;
            $r['gap_to_next'] = $gapByUser[$r['user_id']] ?? 0.0;
            $r['overtake_name'] = $overtakeByUser[$r['user_id']] ?? null;
        }
        unset($r);

        // Champion (top revenue ngayong buwan) + Top Mover — mula sa visible lang.
        $champion = $visibleRows[0] ?? null;
        $topMover = null;
        foreach ($visibleRows as $r) {
            if ($r['week_delta'] > 0 && (!$topMover || $r['week_delta'] > $topMover['week_delta'])) {
                $topMover = $r;
            }
        }

        // I-sort ang buong rows ayon sa rank (visible ranked muna, hidden sa dulo).
        usort($rows, function ($x, $y) {
            return ($x['rank'] ?? PHP_INT_MAX) <=> ($y['rank'] ?? PHP_INT_MAX);
        });

        // I-build ang grouped structure para sa board sections (visible lang).
        $groups = [];
        foreach ($visibleRows as $r) {
            $groups[$r['group']][] = $r;
        }
        $groupRows = [];
        foreach ($groups as $name => $members) {
            $groupRows[] = ['name' => $name, 'agents' => $members];
        }
        // Ayusin: groups na may laman muna, alphabetical (Ungrouped sa dulo).
        usort($groupRows, function ($x, $y) {
            if ($x['name'] === 'Ungrouped') return 1;
            if ($y['name'] === 'Ungrouped') return -1;
            return strcasecmp($x['name'], $y['name']);
        });

        // Team total (buong buwan): kita ng lahat ng visible agents vs team quota.
        $teamRevenue = 0.0;
        $teamPersonalQuota = 0.0;
        foreach ($rows as $r) {
            if (!$r['visible']) {
                continue;
            }
            $teamRevenue += $r['revenue'];
            $teamPersonalQuota += $r['quota'];
        }
        $teamQuotaRow = SalesTeamQuota::forPeriod($start);
        $teamQuota = (float) ($teamQuotaRow->quota_amount ?? 0);
        $teamPct = $teamQuota > 0 ? round(($teamRevenue / $teamQuota) * 100, 1) : 0.0;
        $teamOver = $teamQuota > 0 && $teamRevenue > $teamQuota;
        $team = [
            'revenue' => $teamRevenue,
            'quota' => $teamQuota,
            'personal_quota' => $teamPersonalQuota,
            'pct' => $teamPct,
            'over' => $teamOver,
            'over_amount' => $teamOver ? $teamRevenue - $teamQuota : 0.0,
            'remaining' => max($teamQuota - $teamRevenue, 0),
            'has_quota' => $teamQuota > 0,
        ];

        $announcements = [];
        if ($settings->show_announcements) {
            $now = Carbon::now();
            $announcements = SalesAnnouncement::query()
                ->where('active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                })
                ->orderByDesc('pinned')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'title', 'body', 'images', 'type', 'pinned', 'created_at'])
                ->map(fn ($x) => [
                    'id' => $x->id,
                    'title' => $x->title,
                    'body' => $x->body,
                    'images' => collect($x->images ?? [])->map(fn ($p) => \Illuminate\Support\Facades\Storage::url($p))->values()->all(),
                    'type' => $x->type,
                    'pinned' => (bool) $x->pinned,
                    'when' => optional($x->created_at)->diffForHumans(),
                ])->values()->all();
        }

        return [
            'period' => [
                'start' => $start,
                'end' => $end,
                'label' => Carbon::parse($start)->format('F Y'),
            ],
            'settings' => [
                'board_title' => $settings->board_title,
                'tagline' => $settings->tagline,
                'show_team_quotas' => (bool) $settings->show_team_quotas,
                'show_leaderboard' => (bool) $settings->show_leaderboard,
                'show_announcements' => (bool) $settings->show_announcements,
                'show_amounts' => (bool) $settings->show_amounts,
                'show_own_amount' => (bool) $settings->show_own_amount,
                'show_rank' => (bool) $settings->show_rank,
                'show_percent' => (bool) $settings->show_percent,
                'show_own_card' => (bool) $settings->show_own_card,
                'show_podium' => (bool) $settings->show_podium,
                'show_badges' => (bool) $settings->show_badges,
                'show_champion' => (bool) $settings->show_champion,
                'show_confetti' => (bool) $settings->show_confetti,
                'show_team_total' => (bool) $settings->show_team_total,
            ],
            'agents' => $rows,
            'groups' => $groupRows,
            'team' => $team,
            'champion_id' => $champion['user_id'] ?? null,
            'champion_name' => $champion['name'] ?? null,
            'champion_amount' => $champion['revenue'] ?? 0,
            'champion_initials' => $champion['initials'] ?? null,
            'champion_avatar' => $champion['avatar'] ?? null,
            'champion_days_on_top' => $champion ? $this->topStreakDays($visibleRows, (int) $champion['user_id']) : 0,
            'days_left' => (int) Carbon::now()->startOfDay()->diffInDays(Carbon::now()->endOfMonth()->startOfDay()) + 1,
            'last_champion' => $this->lastMonthChampion(),
            'week_leader' => $this->weekLeader($visibleRows),
            'top_mover_id' => $topMover['user_id'] ?? null,
            'top_mover_name' => $topMover['name'] ?? null,
            'top_mover_delta' => $topMover['week_delta'] ?? 0,
            'announcements' => $announcements,
            'generated_at' => Carbon::now()->format('M d, Y g:i A'),
        ];
    }

    /** Initials para sa avatar circle (max 2 letters). */
    private function initials(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';    
        }
        // Alisin ang mga bracket/role suffix gaya ng "(Agent)" para malinis ang initials.
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') {
            return '?';
        }
        $parts = explode(' ', $name);
        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first . $last);
    }

    /** Inspirational line base sa progress %. */
    public static function motivate(float $pct, bool $hasQuota): string
    {
        if (!$hasQuota) {
            return 'Set na ang quota mo — simulan nang umarangkada! 🚀';
        }
        if ($pct >= 100) {
            return 'SOBRANG GALING! Na-hit mo na ang quota mo — legendary! 🏆🎉';
        }
        if ($pct >= 80) {
            return 'Ang lapit mo na! Konti na lang, i-push mo pa! 💪';
        }
        if ($pct >= 50) {
            return 'Kalahati na! Tuloy lang ang momentum — kaya mo \'to! 🔥';
        }
        if ($pct >= 25) {
            return 'Magandang simula! Bawat order, malaking hakbang. Keep going! ✨';
        }
        return 'Simula pa lang — ang bawat benta mo ay progreso. Laban! 🌱';
    }

    /**
     * Puwede bang pumasok ang user sa Team Board?
     * Admin = laging pwede. Ang agents = kapag binuksan ni admin (agents_can_view).
     */
    private function canAccessBoard(User $user): bool
    {
        if ($this->canManage($user)) {
            return true;
        }

        return $this->canViewBoard($user) && SalesBoardSetting::current()->agents_can_view;
    }

    public function board(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessBoard($user), 403, 'Hindi pa bukas ang Team Board. Hintayin ang go signal ni admin.');

        $payload = $this->buildPayload();
        $payload['me_id'] = $user->id;
        $payload['is_admin'] = $this->canManage($user);

        return view('sales.team.board', $payload);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessBoard($user), 403, 'Hindi pa bukas ang Team Board.');

        $payload = $this->buildPayload();
        $payload['me_id'] = $user->id;
        $payload['is_admin'] = $this->canManage($user);

        return response()->json($payload);
    }

    public function settings(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403, 'Admin only.');

        $settings = SalesBoardSetting::current();
        $agents = $this->agentUsers();
        [$start, $end] = $this->currentPeriod();

        $quotas = SalesQuota::query()
            ->where('period_type', 'monthly')
            ->whereDate('period_start', $start)
            ->get()
            ->keyBy('user_id');

        $announcements = SalesAnnouncement::query()
            ->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $memberGroups = SalesBoardMemberGroup::query()->get()->keyBy('user_id');
        $teamQuota = SalesTeamQuota::forPeriod($start);

        return view('sales.team.settings', [
            'settings' => $settings,
            'agents' => $agents,
            'quotas' => $quotas,
            'announcements' => $announcements,
            'memberGroups' => $memberGroups,
            'teamQuota' => $teamQuota,
            'period' => ['start' => $start, 'end' => $end, 'label' => Carbon::parse($start)->format('F Y')],
        ]);
    }

    /** I-save ang team total (buong-buwan) quota. */
    public function saveTeamQuota(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403, 'Admin only.');

        [$start, $end] = $this->currentPeriod();
        $amount = (float) str_replace([',', '₱', ' '], '', (string) $request->input('team_quota', '0'));

        SalesTeamQuota::updateOrCreate(
            ['period_type' => 'monthly', 'period_start' => $start],
            ['period_end' => $end, 'quota_amount' => $amount, 'updated_by' => $user->id]
        );

        return back()->with('ok', 'Na-save ang team total quota para sa ' . Carbon::parse($start)->format('F Y') . '.');
    }

    /** I-save ang per-agent grouping (kung sino ang magkakasama sa board). */
    public function saveGroups(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403, 'Admin only.');

        $groups = $request->input('groups', []);  // [user_id => group_name]
        $visible = $request->input('visible', []); // [user_id => 1]
        $count = 0;

        foreach ($groups as $uid => $name) {
            $uid = (int) $uid;
            if ($uid <= 0) {
                continue;
            }
            $name = trim((string) $name);
            SalesBoardMemberGroup::updateOrCreate(
                ['user_id' => $uid],
                [
                    'group_name' => $name !== '' ? $name : null,
                    'visible' => !empty($visible[$uid]),
                ]
            );
            $count++;
        }

        SalesBoardSetting::forgetMemo();

        return back()->with('ok', "Na-save ang grouping ng {$count} agent(s).");
    }

    public function saveSettings(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403);

        $settings = SalesBoardSetting::current();
        $settings->board_title = $request->input('board_title', $settings->board_title) ?: 'Team Board';
        $settings->tagline = $request->input('tagline');
        $settings->agents_can_view = $request->boolean('agents_can_view');
        foreach ([
            'show_team_quotas', 'show_leaderboard', 'show_announcements',
            'show_amounts', 'show_own_amount', 'show_rank', 'show_percent', 'show_own_card',
            'show_podium', 'show_badges', 'show_champion', 'show_confetti', 'show_team_total',
        ] as $flag) {
            $settings->$flag = $request->boolean($flag);
        }
        $settings->updated_by = $user->id;
        $settings->save();
        SalesBoardSetting::forgetMemo();

        return back()->with('ok', 'Na-save ang Team Board settings.');
    }

    public function saveQuotas(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403);

        [$start, $end] = $this->currentPeriod();
        $quotas = $request->input('quotas', []); // [user_id => amount]

        $count = 0;
        foreach ($quotas as $uid => $amount) {
            $uid = (int) $uid;
            if ($uid <= 0) {
                continue;
            }
            $amount = (float) str_replace([',', '₱', ' '], '', (string) $amount);
            SalesQuota::updateOrCreate(
                ['user_id' => $uid, 'period_type' => 'monthly', 'period_start' => $start],
                ['period_end' => $end, 'quota_amount' => $amount, 'active' => true, 'updated_by' => $user->id, 'created_by' => $user->id]
            );
            $count++;
        }

        return back()->with('ok', "Na-save ang quota ng {$count} agent(s) para sa " . Carbon::parse($start)->format('F Y') . '.');
    }

    public function postAnnouncement(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403, 'Admin only.');

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'nullable|string|max:2000',
            'type' => 'nullable|in:news,motivation,alert',
            'pinned' => 'nullable|boolean',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        // I-upload ang mga larawan (public disk -> /storage/announcements/...).
        $paths = [];
        foreach ($request->file('images', []) as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('announcements', 'public');
            }
        }

        SalesAnnouncement::create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'images' => $paths ?: null,
            'type' => $data['type'] ?? 'news',
            'pinned' => !empty($data['pinned']),
            'active' => true,
            'publish_at' => Carbon::now(),
            'created_by' => $user->id,
        ]);

        return back()->with('ok', 'Na-post ang announcement.');
    }

    /**
     * PRIVILEGE ng Top 1 (champion): makapag-post ng inspiring message na
     * makikita ng lahat sa Team Board. Ito lang ang non-admin na pwedeng mag-post.
     * Andrew 2026-10-22.
     */
    public function postChampionMessage(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $payload = $this->buildPayload();
        abort_unless(
            !empty($payload['champion_id']) && (int) $payload['champion_id'] === (int) $user->id,
            403,
            'Top 1 lang ang may privilege na mag-post ng message.'
        );

        $data = $request->validate([
            'message' => 'required|string|max:600',
        ]);

        SalesAnnouncement::create([
            'title' => 'Message from the Champion — ' . $user->name,
            'body' => $data['message'],
            'images' => null,
            'type' => 'champion',
            'pinned' => true,
            'active' => true,
            'publish_at' => Carbon::now(),
            'created_by' => $user->id,
        ]);

        return back()->with('ok', 'Na-post ang champion message mo! 🏆');
    }

    public function toggleAnnouncement(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403);

        $a = SalesAnnouncement::findOrFail($id);
        $a->active = !$a->active;
        $a->save();

        return back()->with('ok', $a->active ? 'Na-activate ang announcement.' : 'Na-hide ang announcement.');
    }

    public function togglePin(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403);

        $a = SalesAnnouncement::findOrFail($id);
        $a->pinned = !$a->pinned;
        $a->save();

        return back()->with('ok', $a->pinned ? 'Na-pin ang announcement.' : 'Na-unpin ang announcement.');
    }

    public function deleteAnnouncement(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user && $this->canManage($user), 403);

        SalesAnnouncement::where('id', $id)->delete();

        return back()->with('ok', 'Na-delete ang announcement.');
    }
}
