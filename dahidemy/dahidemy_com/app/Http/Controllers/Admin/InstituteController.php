<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\InstituteService;
use App\Services\Api\SettingsService;
use App\Support\ApiPaginator;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin management of institutes (tenants). Every read/write is proxied to
 * the backend through InstituteService — this controller holds no business logic
 * and never touches a database.
 */
class InstituteController extends Controller
{
    /** Columns the backend allows ordering by (guards against arbitrary input). */
    private const SORTABLE = ['id', 'name', 'email', 'created_at', 'status'];

    public function __construct(
        protected InstituteService $institutes,
        protected SettingsService $settings,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'per_page' => $this->perPage($request),
            'order_by' => in_array($request->query('order_by'), self::SORTABLE, true) ? $request->query('order_by') : 'id',
            'order' => $request->query('order') === 'asc' ? 'asc' : 'desc',
            // '' = every institute, '1' = active only, '0' = inactive only (which on
            // the backend means "anything that is not 1" — see App\Support\Status).
            'status' => in_array($request->query('status'), ['0', '1'], true) ? $request->query('status') : '',
        ];

        $response = $this->institutes->paginate(array_filter([
            'search' => $filters['search'],
            'status' => $filters['status'],
            'perPage' => $filters['per_page'],
            'orderBy' => $filters['order_by'],
            'order' => $filters['order'],
            'page' => (int) $request->query('page', 1) ?: 1,
        ], fn ($v) => $v !== '' && $v !== null));

        return view('admin.institutes.index', [
            'institutes' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $saasSettings = (array) $this->settings->all()->data([]);

        return view('admin.institutes.create', [
            'typeOptions' => $this->typeOptions(),
            'templateOptions' => $this->templateOptions(),
            'defaultTemplateId' => $saasSettings['default_template_institute_id'] ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institute_name' => ['required', 'string', 'max:100'],
            'institute_email' => ['required', 'email', 'max:100'],
            'institute_phone' => ['required', 'string', 'max:20'],
            'institute_type' => ['required', 'string', 'max:100'],
            'institute_domain' => ['required', 'string', 'max:100'],
            'institute_address' => ['nullable', 'string', 'max:255'],
            'institute_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'user_name' => ['required', 'string', 'max:100'],
            'user_email' => ['required', 'email', 'max:100'],
            'user_phone' => ['required', 'string', 'max:20'],
            'user_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'template_institute_id' => ['nullable', 'integer'],
        ]);

        // The backend also enforces `confirmed`, so forward the confirmation.
        $data['password_confirmation'] = $request->input('password_confirmation');

        if (blank($data['template_institute_id'] ?? null)) {
            unset($data['template_institute_id']);
        }

        return $this->finish(
            $this->institutes->create($data),
            'Institute created successfully.'
        );
    }

    public function show(string $id): View|RedirectResponse
    {
        $response = $this->institutes->find($id);

        if (! $response->successful() || ! $response->data()) {
            return redirect()
                ->route('admin.institutes.index')
                ->with('error', $response->message ?: 'Institute not found.');
        }

        return view('admin.institutes.show', [
            'institute' => (array) $response->data(),
        ]);
    }

    public function edit(string $id): View|RedirectResponse
    {
        $response = $this->institutes->find($id);

        if (! $response->successful() || ! $response->data()) {
            return redirect()
                ->route('admin.institutes.index')
                ->with('error', $response->message ?: 'Institute not found.');
        }

        return view('admin.institutes.edit', [
            'id' => $id,
            'institute' => (array) $response->data(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['required', 'string', 'max:15'],
            'domain' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        return $this->finish(
            $this->institutes->update($id, $data),
            'Institute updated successfully.',
            fn () => back()->withInput()
        );
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->institutes->destroy($id);

        return redirect()
            ->route('admin.institutes.index')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful() ? 'Institute deleted successfully.' : ($response->message ?: 'Delete failed.')
            );
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $response = $this->institutes->toggleStatus($id);

        return back()->with(
            $response->successful() ? 'success' : 'error',
            $response->successful() ? ($response->message ?: 'Status updated.') : ($response->message ?: 'Status update failed.')
        );
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /** @return array<int|string, string> */
    private function templateOptions(): array
    {
        $types = ResourceRegistry::normalizeList($this->institutes->paginate([
            'perPage' => 200,
            'orderBy' => 'name',
            'order' => 'asc',
            'status' => '1',
        ])->data([]));

        return collect($types)
            ->mapWithKeys(function ($institute) {
                $id = $institute['id'] ?? null;
                $name = $institute['name'] ?? ('School #'.$id);

                return $id ? [(string) $id => $name] : [];
            })
            ->all();
    }

    /** [name => name] map for the type <select> (institute_type is stored as a name). */
    private function typeOptions(): array
    {
        $types = ResourceRegistry::normalizeList($this->institutes->types()->data([]));

        return collect($types)
            ->pluck('name', 'name')
            ->filter()
            ->all();
    }

    /**
     * On success redirect to the list with a flash message; on failure bounce
     * back with the API message + validation errors. $onFail lets a caller
     * (e.g. edit) preserve its own input.
     */
    private function finish($response, string $success, ?callable $onFail = null): RedirectResponse
    {
        if ($response->successful()) {
            return redirect()
                ->route('admin.institutes.index')
                ->with('success', $success);
        }

        $redirect = $onFail ? $onFail() : back()->withInput();

        return $redirect
            ->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'Something went wrong. Please try again.');
    }
}
