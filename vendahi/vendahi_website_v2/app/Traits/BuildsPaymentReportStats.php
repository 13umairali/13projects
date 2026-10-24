<?php

namespace App\Traits;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Whole-filtered-set aggregates for the payment report pages
 * (PaymentsReport.vue): KPI tiles, the "payments over time" chart and the
 * payment-method donut. Shared by the four payment list controllers.
 */
trait BuildsPaymentReportStats
{
    /**
     * @param  Builder  $filtered  The fully filtered (but not yet paginated) query.
     * @return array{stats: array, timeseries: array, method_breakdown: array}
     */
    protected function paymentReportStats(Builder $filtered): array
    {
        $table = $filtered->getModel()->getTable();

        // Work on the underlying query builder: whereHas() constraints are
        // already compiled into it, and dropping eager loads / ordering keeps
        // the aggregates cheap and valid under ONLY_FULL_GROUP_BY.
        $base = (clone $filtered)->getQuery();
        $base->orders = null;

        $totals = (clone $base)
            ->select(DB::raw("COUNT(*) as c, COALESCE(SUM({$table}.montant), 0) as s"))
            ->first();
        $count = (int) ($totals->c ?? 0);
        $total = round((float) ($totals->s ?? 0), 2);

        $byMethod = (clone $base)
            ->select(DB::raw("{$table}.payment_method_id as pm, COALESCE(SUM({$table}.montant), 0) as amount, COUNT(*) as cnt"))
            ->groupBy("{$table}.payment_method_id")
            ->orderByDesc('amount')
            ->get();

        $names = PaymentMethod::whereIn('id', $byMethod->pluck('pm')->filter()->all())
            ->pluck('name', 'id');

        $methodBreakdown = $byMethod->map(fn ($r) => [
            'name' => $r->pm ? ($names[$r->pm] ?? '---') : '---',
            'amount' => round((float) $r->amount, 2),
            'count' => (int) $r->cnt,
        ])->values()->all();

        $timeseries = (clone $base)
            ->select(DB::raw("DATE({$table}.date) as d, COALESCE(SUM({$table}.montant), 0) as amount"))
            ->groupBy(DB::raw("DATE({$table}.date)"))
            ->orderByRaw('d')
            ->get()
            ->map(fn ($r) => ['d' => $r->d, 'amount' => round((float) $r->amount, 2)])
            ->values()
            ->all();

        return [
            'stats' => [
                'count' => $count,
                'total' => $total,
                'average' => $count > 0 ? round($total / $count, 2) : 0,
                'top_method' => $methodBreakdown[0]['name'] ?? null,
            ],
            'timeseries' => $timeseries,
            'method_breakdown' => $methodBreakdown,
        ];
    }
}
