<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Finds out WHY API requests are slow. Adds a Server-Timing header (see it with: curl -i ...) and writes one line to
 * storage/logs/laravel.log for every API request slower than 300 ms:
 *   total = whole request incl. PHP / framework start-up,  handler = controller work,  db = time inside SQL,  q = number of queries.
 * If total is much bigger than handler, the server itself is slow (PHP start-up, OPcache off, CPU / memory pressure).
 * If db is big, the queries are slow. If q is big, the code runs too many queries.
 */
class LogSlowRequests
{
    public function handle(Request $request, Closure $next)
    {
        $queries = 0;
        $dbMs = 0.0;
        DB::listen(function ($q) use (&$queries, &$dbMs) { $queries++; $dbMs += $q->time; });
        $t0 = microtime(true);
        $response = $next($request);
        $now = microtime(true);
        $handlerMs = ($now - $t0) * 1000;
        $totalMs = ($now - (defined('LARAVEL_START') ? LARAVEL_START : $t0)) * 1000;
        try {
            $response->headers->set('Server-Timing', sprintf('total;dur=%.0f, handler;dur=%.0f, db;dur=%.0f, q;desc="%d queries"', $totalMs, $handlerMs, $dbMs, $queries));
            if ($totalMs > 300) {
                Log::warning('slow api request', ['path' => $request->path(), 'method' => $request->method(), 'status' => $response->getStatusCode(),
                    'total_ms' => round($totalMs), 'handler_ms' => round($handlerMs), 'db_ms' => round($dbMs), 'queries' => $queries, 'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 1)]);
            }
        } catch (\Throwable $e) {}
        return $response;
    }
}
