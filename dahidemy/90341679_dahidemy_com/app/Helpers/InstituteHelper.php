<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;

class InstituteHelper
{
    /**
     * If a domain looks like a bare subdomain prefix with no dot at all
     * (e.g. "abc" instead of "abc.dahidemy.com"), append this platform's
     * suffix automatically. This is the actual root cause behind several
     * institutes ending up with a truncated domain: the institute
     * creation/onboarding forms don't append it themselves before saving.
     * Anything that already looks like a real domain (contains a dot) is
     * left completely untouched.
     */
    public static function normalizeDomain(?string $domain): ?string
    {
        if (! $domain) {
            return $domain;
        }

        $domain = trim(strtolower($domain));
        $domain = str_replace(['http://', 'https://'], '', $domain);
        $domain = rtrim($domain, '/');

        if ($domain === '' || str_contains($domain, '.')) {
            return $domain;
        }

        return $domain.'.dahidemy.com';
    }

    /**
     * Finds the institute that owns this exact domain, regardless of
     * whether it's active, inactive, or soft-deleted. Used specifically to
     * tell apart "this domain has never belonged to any institute" (which
     * should fall through to the generic fallback institute) from "this
     * domain belongs to a real institute that is currently deactivated"
     * (which should show a dedicated unavailable page, not silently
     * display a *different* institute's content instead).
     */
    public static function findInstituteByExactDomainIncludingInactive(Request $request): ?Institute
    {
        if (! Schema::hasTable('institutes')) {
            return null;
        }

        $host = strtolower((string) $request->getHost());
        $host = trim($host, ". \t\n\r\0\x0B");
        $host = str_replace(['http://', 'https://'], '', $host);

        if ($host === '') {
            return null;
        }

        $normalizedHost = str_starts_with($host, 'www.') ? substr($host, 4) : $host;
        $hostParts = explode('.', $normalizedHost);
        $subdomainPrefix = $hostParts[0] ?? null;

        return Institute::withTrashed()
            ->where(function ($query) use ($host, $normalizedHost, $subdomainPrefix): void {
                $query->where('domain', $host)
                    ->orWhere('domain', $normalizedHost)
                    ->orWhere('domain', 'www.'.$normalizedHost);

                if ($subdomainPrefix && $subdomainPrefix !== $normalizedHost && self::isPlatformSubdomain($normalizedHost)) {
                    $query->orWhere('domain', $subdomainPrefix);
                }
            })
            ->first();
    }

    /**
     * Resolve the active institute from the current request host.
     */
    public static function resolveInstituteFromRequest(Request $request): ?Institute
    {
        if (! Schema::hasTable('institutes')) {
            return null;
        }

        $host = strtolower((string) $request->getHost());
        $host = trim($host, ". \t\n\r\0\x0B");
        $host = str_replace(['http://', 'https://'], '', $host);

        if ($host === '') {
            return Institute::where('status', 1)->first();
        }

        $normalizedHost = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        // Several institutes in this system were saved with only their
        // subdomain prefix (e.g. "abc") rather than the full domain (e.g.
        // "abc.dahidemy.com") - a pattern seen across multiple institutes,
        // not a one-off typo. Rather than requiring every one of them to be
        // corrected by hand (and every future one too), also match on that
        // prefix. This is purely additive: exact full-domain matches are
        // tried first and behave exactly as before.
        $hostParts = explode('.', $normalizedHost);
        $subdomainPrefix = $hostParts[0] ?? null;

        return Institute::query()
            ->where('status', 1)
            ->where(function ($query) use ($host, $normalizedHost, $subdomainPrefix): void {
                $query->where('domain', $host)
                    ->orWhere('domain', $normalizedHost)
                    ->orWhere('domain', 'www.'.$normalizedHost);

                if ($subdomainPrefix && $subdomainPrefix !== $normalizedHost && self::isPlatformSubdomain($normalizedHost)) {
                    $query->orWhere('domain', $subdomainPrefix);
                }
            })
            ->first() ?? (self::strictHosts() && ! self::isPlatformHost($normalizedHost)
                ? null
                : Institute::where('status', 1)->first());
    }

    /**
     * Resolve the primary branch for the active institute from the request context.
     */
    public static function resolveBranchIdFromRequest(Request $request): ?int
    {
        if (! Schema::hasTable('branches')) {
            return null;
        }

        $institute = self::resolveInstituteFromRequest($request);

        if (! $institute) {
            return null;
        }

        return Branch::query()
            ->where('institute_id', $institute->id)
            ->where('status', 1)
            ->value('id');
    }

    /**
     * Get institute ID based on the domain.
     *
     * @return mixed
     */
    public static function getInstituteIdByDomain(string $domain)
    {
        // Check if domain is provided
        if (empty($domain)) {
            return [
                'error' => 'Domain is required.',
                'status' => false,
            ];
        }

        // Check if domain format is valid (e.g., example.com)
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'error' => 'Invalid domain format.',
                'status' => false,
            ];
        }

        // Fetch the institute based on the domain
        $institute = DB::table('institutes')
            ->where('domain', $domain)
            ->select('id')
            ->first();

        // If no matching institute is found
        if (! $institute) {
            return [
                'error' => 'Institute not found for the provided domain.',
                'status' => false,
            ];
        }

        // Return the institute ID
        return [
            'institute_id' => $institute->id,
            'status' => true,
        ];
    }

    /**
     * The platform's root domain, e.g. "dahidemy.com". Institutes may live at <label>.<root>.
     */
    public static function rootDomain(): string
    {
        return strtolower(trim((string) config('app.root_domain', 'dahidemy.com'), ". \t\n\r\0\x0B"));
    }

    /**
     * Only hosts under the platform root domain may be matched by their first label alone.
     * Previously "pegasus.anything-at-all.com" resolved to the institute whose domain is "pegasus".
     */
    public static function isPlatformSubdomain(string $host): bool
    {
        $root = self::rootDomain();

        return $root !== '' && str_ends_with($host, '.'.$root);
    }

    public static function strictHosts(): bool
    {
        return (bool) config('app.tenant_strict_hosts', false);
    }

    /**
     * Hosts that may fall back to the default (first active) institute even in strict mode.
     */
    public static function isPlatformHost(string $host): bool
    {
        $allowed = array_filter(array_map('trim', explode(',', strtolower((string) config('app.tenant_default_hosts', '')))));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $root = self::rootDomain();

        return in_array($host, $allowed, true)
            || ($appHost !== '' && $host === $appHost)
            || ($root !== '' && ($host === $root || $host === 'www.'.$root));
    }

    /**
     * Strict host -> institute lookup used for authentication decisions. Exact domain match, or
     * (only for hosts under the platform root domain) a match on the first label. Never falls back.
     */
    public static function findInstituteByStrictHost(Request $request): ?Institute
    {
        if (! Schema::hasTable('institutes')) {
            return null;
        }

        $host = strtolower(trim((string) $request->getHost(), ". \t\n\r\0\x0B"));
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        if ($host === '') {
            return null;
        }

        $candidates = [$host, 'www.'.$host];

        if (self::isPlatformSubdomain($host)) {
            $label = substr($host, 0, -strlen('.'.self::rootDomain()));
            if ($label !== '' && ! str_contains($label, '.')) {
                $candidates[] = $label;
            }
        }

        return Institute::query()
            ->whereIn('domain', $candidates)
            ->orderByRaw('CASE WHEN domain = ? THEN 0 ELSE 1 END', [$host])
            ->first();
    }

    /**
     * May this authenticated user use the site/API at the requested host?
     *  - SAAS Admins: anywhere.
     *  - Host belongs to an institute: only that institute's users.
     *  - Host is not bound to any institute (platform / API host): allowed (data is still scoped
     *    to the user's own institute).
     */
    public static function userMayUseHost($user, Request $request): bool
    {
        if (! $user) {
            return false;
        }

        if (($user->user_type ?? null) === 'SAAS Admin') {
            return true;
        }

        $institute = self::findInstituteByStrictHost($request);

        if (! $institute) {
            return true;
        }

        return (int) $user->institute_id === (int) $institute->id;
    }
}