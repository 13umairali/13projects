<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ErrorLog::class);

        $perPage = (int) $request->input('per_page', $request->input('limit', 10));
        $page = max(1, (int) $request->input('page', 1));

        $sortField = (string) $request->input('SortField', 'occurred_at');
        if (! in_array($sortField, ['id', 'context', 'message', 'occurred_at'], true)) {
            $sortField = 'occurred_at';
        }
        $sortType = strtolower((string) $request->input('SortType', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Filters sent by the Error Logs report page.
        $base = ErrorLog::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->to))
            ->when($request->filled('context'), fn ($q) => $q->where('context', $request->context))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('context', 'LIKE', "%{$s}%")
                        ->orWhere('message', 'LIKE', "%{$s}%")
                        ->orWhere('details', 'LIKE', "%{$s}%");
                });
            });

        $total = (clone $base)->count();

        $rows = (clone $base)->orderBy($sortField, $sortType)->orderBy('id', 'desc');
        if ($perPage === -1) {
            $logs = $rows->get();
        } else {
            $perPage = max(1, $perPage);
            $logs = $rows->offset(($page - 1) * $perPage)->limit($perPage)->get();
        }

        // Whole-filtered-set health summary for the banner.
        $summaryRow = (clone $base)
            ->selectRaw('COUNT(*) as total, SUM(occurred_at >= ?) as last_24h, SUM(occurred_at >= ?) as last_7d', [now()->subDay(), now()->subDays(7)])
            ->first();

        $byContext = (clone $base)
            ->select(DB::raw("COALESCE(NULLIF(context, ''), '---') as name, COUNT(*) as count"))
            ->groupBy('context')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => (int) $r->count])
            ->values();

        // Errors per day (most recent 90 days of the filtered set).
        $timeseries = (clone $base)
            ->select(DB::raw('DATE(occurred_at) as d, COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->orderByDesc('d')
            ->limit(90)
            ->get()
            ->map(fn ($r) => ['d' => $r->d, 'count' => (int) $r->count])
            ->reverse()
            ->values();

        // Filter options: every context ever logged (not just the filtered set).
        $contexts = ErrorLog::query()
            ->whereNotNull('context')
            ->where('context', '!=', '')
            ->distinct()
            ->orderBy('context')
            ->pluck('context');

        return response()->json([
            'logs' => $logs,
            'total' => $total,
            'summary' => [
                'total' => (int) ($summaryRow->total ?? 0),
                'last_24h' => (int) ($summaryRow->last_24h ?? 0),
                'last_7d' => (int) ($summaryRow->last_7d ?? 0),
                'top_context' => $byContext->first()['name'] ?? null,
            ],
            'contexts' => $contexts,
            'timeseries' => $timeseries,
            'by_context' => $byContext,
        ]);
    }
}
