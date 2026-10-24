<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\BackendUrlRequest;
use App\Services\Setup\Exceptions\EnvironmentWriteException;
use App\Services\Setup\SetupManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * First-run installer.
 *
 * Holds no logic of its own: it validates via {@see BackendUrlRequest},
 * delegates to {@see SetupManager} and shapes the result for the browser. Both
 * write endpoints answer JSON so the wizard can show progress without a reload.
 *
 * Reachability is enforced by RedirectIfBackendApiConfigured on the route group,
 * never re-checked here — one gate, one place.
 */
class SetupController extends Controller
{
    public function __construct(
        protected SetupManager $setup
    ) {}

    public function show(): View
    {
        return view('setup.index', [
            'configured' => $this->setup->isConfigured(),
            'currentUrl' => config('saas.api.configured') ? config('saas.api.base_url') : '',
        ]);
    }

    /**
     * Step 2 — probe the URL without touching .env.
     */
    public function test(BackendUrlRequest $request): JsonResponse
    {
        $result = $this->setup->test($request->backendUrl());

        return response()->json(
            $result->toArray(),
            $result->ok ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Step 3 — verify once more, then persist and refresh config.
     */
    public function save(BackendUrlRequest $request): JsonResponse
    {
        try {
            $result = $this->setup->save($request->backendUrl());
        } catch (EnvironmentWriteException $e) {
            // The URL was good but the file could not be written — a host
            // permission problem the operator can fix, so say exactly that
            // rather than reporting a connection failure they would chase.
            return response()->json([
                'ok' => false,
                'message' => 'Could not save the configuration.',
                'hint' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (! $result->ok) {
            return response()->json($result->toArray(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($result->toArray() + [
            'redirect' => route('login'),
        ]);
    }
}
