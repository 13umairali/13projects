<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\PaymentGatewayService;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Super-admin configuration of SaaS payment gateways (SAASSetting rows of type
 * payment_config). Credentials, mode and status are edited here and stored on
 * the backend. Secrets are never persisted locally.
 */
class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gateways,
    ) {}

    public function index(): View
    {
        $response = $this->gateways->list();

        return view('admin.payments.index', [
            'gateways' => $this->rows($response->data([])),
            'reachable' => $response->successful(),
        ]);
    }

    public function edit(string $id): View|RedirectResponse
    {
        $gateway = $this->rows($this->gateways->list()->data([]))
            ->first(fn ($row) => (string) ($row['id'] ?? '') === (string) $id);

        if (! $gateway) {
            return redirect()
                ->route('admin.payments.index')
                ->with('error', 'Payment gateway not found.');
        }

        return view('admin.payments.edit', [
            'gateway' => $gateway,
            'id' => $id,
            'fields' => $this->paymentInfo($gateway),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'payment_info' => ['nullable', 'array'],
            'payment_info.*' => ['nullable', 'string', 'max:1000'],
            'mode' => ['required', 'in:test,live'],
            'status' => ['required', 'in:0,1'],
        ]);

        $response = $this->gateways->update($id, [
            'payment_info' => $validated['payment_info'] ?? [],
            'mode' => $validated['mode'],
            'status' => $validated['status'],
        ]);

        if ($response->successful()) {
            return redirect()
                ->route('admin.payments.index')
                ->with('success', $response->message ?: 'Payment gateway updated.');
        }

        return back()
            ->withInput()
            ->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'Update failed. Please try again.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $response = $this->gateways->toggleStatus($id);

        return back()->with(
            $response->successful() ? 'success' : 'error',
            $response->successful()
                ? ($response->message ?: 'Gateway status updated.')
                : ($response->message ?: 'Status update failed.')
        );
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    protected function rows(mixed $data): Collection
    {
        return collect(ResourceRegistry::normalizeList($data));
    }

    /** Decode payment_info (may arrive as an array or a JSON string) to assoc. */
    protected function paymentInfo(array $gateway): array
    {
        $info = $gateway['payment_info'] ?? [];

        if (is_string($info)) {
            $decoded = json_decode($info, true);
            $info = is_array($decoded) ? $decoded : [];
        }

        return is_array($info) ? $info : [];
    }
}
