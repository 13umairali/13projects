<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\AdminResourceService;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * One controller drives CRUD for every config-driven CMS module. The {resource}
 * route segment selects the definition from ResourceRegistry, which supplies the
 * backend endpoint, validation rules and table/form schema.
 */
class ResourceController extends Controller
{
    public function __construct(
        protected AdminResourceService $api,
    ) {}

    public function index(string $resource): View
    {
        $def = $this->definition($resource);

        $response = $this->api->list($def['endpoint']);

        return view('admin.resource.index', [
            'resource' => $resource,
            'def' => $def,
            'columns' => ResourceRegistry::listColumns($def),
            'records' => ResourceRegistry::normalizeList($response->data([])),
            'reachable' => $response->successful(),
        ]);
    }

    public function create(string $resource): View
    {
        $def = $this->definition($resource);

        return view('admin.resource.form', [
            'resource' => $resource,
            'def' => $def,
            'mode' => 'create',
            'record' => [],
            'id' => null,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $def = $this->definition($resource);

        $data = $this->payload($request, $def);

        $response = ResourceRegistry::imageFields($def)
            ? $this->api->storeWithFiles($def['endpoint'], $data)
            : $this->api->store($def['endpoint'], $data);

        return $this->finish($response, $resource, $def['singular'].' created.');
    }

    public function edit(string $resource, string $id): View|RedirectResponse
    {
        $def = $this->definition($resource);

        // Resolve from the list endpoint (guaranteed to exist) rather than a
        // per-model show route, which some backend resources may not expose.
        $record = $this->records($def)->first(
            fn ($row) => (string) ($row['id'] ?? '') === (string) $id
        );

        if (! $record) {
            return redirect()
                ->route('admin.content.index', $resource)
                ->with('error', $def['singular'].' not found.');
        }

        return view('admin.resource.form', [
            'resource' => $resource,
            'def' => $def,
            'mode' => 'edit',
            'record' => $record,
            'id' => $id,
        ]);
    }

    public function update(Request $request, string $resource, string $id): RedirectResponse
    {
        $def = $this->definition($resource);

        $data = $this->payload($request, $def);

        $response = ResourceRegistry::imageFields($def)
            ? $this->api->updateWithFiles($def['endpoint'], $id, $data)
            : $this->api->update($def['endpoint'], $id, $data);

        return $this->finish($response, $resource, $def['singular'].' updated.');
    }

    public function destroy(string $resource, string $id): RedirectResponse
    {
        $def = $this->definition($resource);

        $response = $this->api->destroy($def['endpoint'], $id);

        return redirect()
            ->route('admin.content.index', $resource)
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful() ? $def['singular'].' deleted.' : ($response->message ?: 'Delete failed.')
            );
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    /**
     * Validated payload, with file fields resolved.
     *
     * An image field the admin left untouched is REMOVED from the payload rather
     * than sent as null — the backend's fileUploader() treats a null upload as
     * "keep the old file", but an explicit null would blank the column.
     */
    protected function payload(Request $request, array $def): array
    {
        $data = $request->validate(ResourceRegistry::rules($def));

        foreach (ResourceRegistry::imageFields($def) as $name) {
            if ($request->hasFile($name)) {
                $data[$name] = $request->file($name);
            } else {
                unset($data[$name]);
            }
        }

        return $data;
    }

    /** Resolve the definition or 404 on an unknown resource key. */
    protected function definition(string $resource): array
    {
        return ResourceRegistry::find($resource)
            ?? abort(Response::HTTP_NOT_FOUND);
    }

    protected function records(array $def): Collection
    {
        $response = $this->api->list($def['endpoint']);

        return collect(ResourceRegistry::normalizeList($response->data([])));
    }

    /**
     * On success redirect to the list; on failure bounce back with the API
     * message + any validation errors.
     *
     * No cache flush here: every successful write already invalidates the whole
     * API cache in BaseApiService, so the list — and the marketing site — pick
     * the change up on the next read.
     */
    protected function finish($response, string $resource, string $success): RedirectResponse
    {
        if ($response->successful()) {
            return redirect()
                ->route('admin.content.index', $resource)
                ->with('success', $success);
        }

        return back()
            ->withInput()
            ->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'Something went wrong. Please try again.');
    }
}
