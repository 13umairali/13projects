<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;

/**
 * POST api/institutes/{id}/impersonate  (SAAS Admin only)
 *
 * Mints a short-lived, single-use link that signs the caller's browser in as the institute's admin
 * user ON THAT INSTITUTE'S OWN DOMAIN. The SaaS panel redirects the operator to the returned `url`.
 * The token is stored hashed (impersonation_tokens) and is consumed by
 * App\Http\Controllers\WEB\ImpersonationController@consume.
 */
class ImpersonationController extends Controller
{
    public const TTL_SECONDS = 60;

    public function start(Request $request, string $id): JsonResponse
    {
        $actor = $request->user('api') ?? $request->user();

        // The route already has the saas.admin middleware; this is the second, independent check.
        if (! $actor || ($actor->user_type ?? null) !== 'SAAS Admin' || ! empty($actor->institute_id)) {
            return $this->responseError([], 'Only platform administrators can impersonate an institute.', 403);
        }

        if (! ctype_digit($id)) {
            return $this->responseError([], 'Institute not found.', 404);
        }

        if (! Schema::hasTable('impersonation_tokens')) {
            return $this->responseError([], 'Impersonation is not set up yet. Run "php artisan migrate" on the backend.', 500);
        }

        $institute = Institute::find((int) $id);

        if (! $institute) {
            return $this->responseError([], 'Institute not found.', 404);
        }

        if (! $institute->status) {
            return $this->responseError([], 'This institute is inactive. Activate it before logging in as its admin.', 422);
        }

        $domain = InstituteHelper::normalizeDomain($institute->domain);

        if (! $domain) {
            return $this->responseError([], 'This institute has no domain configured.', 422);
        }

        $target = User::query()
            ->where('institute_id', $institute->id)
            ->where('user_type', 'Admin')
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        if (! $target) {
            return $this->responseError([], 'This institute has no active admin user to log in as.', 422);
        }

        $plain = Str::random(64);
        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        DB::table('impersonation_tokens')->insert([
            'token_hash' => hash('sha256', $plain),
            'institute_id' => $institute->id,
            'target_user_id' => $target->id,
            'impersonator_id' => $actor->id,
            'return_url' => $returnUrl,
            'expires_at' => now()->addSeconds(self::TTL_SECONDS),
            'created_ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('[impersonation] link minted', [
            'impersonator_id' => $actor->id,
            'institute_id' => $institute->id,
            'target_user_id' => $target->id,
            'ip' => $request->ip(),
        ]);

        $scheme = ($request->isSecure() || str_starts_with((string) config('app.url'), 'https')) ? 'https' : 'http';

        return $this->responseSuccess([
            'url' => "{$scheme}://{$domain}/impersonate/{$plain}",
            'expires_in' => self::TTL_SECONDS,
            'institute' => ['id' => $institute->id, 'name' => $institute->name],
            'user' => ['id' => $target->id, 'name' => $target->name],
        ], 'Impersonation link created.');
    }

    /**
     * "Back to SaaS Admin" target. Only http(s) URLs on the platform root domain (or hosts listed in
     * IMPERSONATION_RETURN_HOSTS) are accepted; anything else is dropped so it can never become an
     * open redirect.
     */
    private function safeReturnUrl(?string $url): ?string
    {
        if (! $url || strlen($url) > 500) {
            return null;
        }

        $parts = parse_url($url);

        if (! $parts || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host']);
        $root = InstituteHelper::rootDomain();
        $extra = array_filter(array_map('trim', explode(',', strtolower((string) config('app.impersonation_return_hosts', '')))));

        if (($root !== '' && ($host === $root || str_ends_with($host, '.'.$root))) || in_array($host, $extra, true)) {
            return $url;
        }

        return null;
    }
}
