<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

/**
 * Rebuilds a Laravel LengthAwarePaginator from a backend paginator envelope so
 * server-side paginated API lists can use the framework's `->links()` UI. The
 * backend answers `{ data: { current_page, data: [...], per_page, total } }`;
 * we hydrate a paginator whose page links preserve the current query string.
 */
class ApiPaginator
{
    /**
     * @param  mixed  $payload  The unwrapped `data` node of the API response.
     */
    public static function fromApi(mixed $payload, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $payload = is_array($payload) ? $payload : (array) $payload;

        $rows = Arr::get($payload, 'data', []);
        $items = is_array($rows)
            ? array_map(fn ($row) => (array) $row, array_values($rows))
            : [];

        $perPage = (int) (Arr::get($payload, 'per_page') ?: 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        return new LengthAwarePaginator(
            $items,
            (int) (Arr::get($payload, 'total') ?? count($items)),
            $perPage,
            (int) (Arr::get($payload, 'current_page') ?: 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ]
        );
    }
}
