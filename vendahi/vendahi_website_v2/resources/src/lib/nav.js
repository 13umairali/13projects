import router from '../router';
import { resolveMigratedPath } from '../config/migrated';

/**
 * Navigate to a page by its LEGACY path during the migration.
 *
 * Pages copied from the stocky reference push new-app paths ('/products') that
 * may not exist here yet, and SaaS pages link to modules still in legacy
 * ('/app/billing/change-plan'). goTo() takes a legacy '/app/…' path and sends
 * it where it lives today: the Vue 3 route when MIGRATED_ROUTES has it,
 * otherwise a full navigation into the legacy SPA. Non-legacy paths are pushed
 * as-is. When everything is migrated this collapses to router.push.
 */
export function goTo(path) {
    if (!path) return;
    if (/^https?:\/\//.test(path)) { window.open(path, '_blank', 'noopener'); return; }
    if (path.startsWith('/app/') || path === '/app') {
        const next = resolveMigratedPath(path);
        // Every legacy path is mapped; anything unmapped hits the router's
        // /app/... redirect and lands on the not-found page.
        router.push(next || path);
        return;
    }
    router.push(path);
}

export default goTo;
