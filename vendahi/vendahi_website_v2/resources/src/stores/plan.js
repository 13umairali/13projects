import { defineStore } from 'pinia';

/**
 * Tenant subscription plan, as injected by layouts/next.blade.php:
 *
 *   window.__planSummary = { has_plan, features: { <key>: { enabled, ... } }, ... }
 *
 * (TenantLimitsService::getPlanSummary(), the same object the legacy
 * master.blade.php exposed and Sidebar.vue read at line ~2940.)
 *
 * SaaS-only — the stocky reference has no plans. Two consumers:
 *   - the sidebars gate every menu entry that carries a `feature` key
 *     (config/menu.js, generated from the legacy sidebar's planFeature() calls);
 *   - pages that want to hide an action the plan does not include.
 *
 * Legacy rule kept verbatim: no plan data loaded → everything allowed. The
 * server still enforces limits (403 {status: 'limit_reached'}, see lib/http.js).
 */
function readSummary() {
    const ps = typeof window !== 'undefined' ? window.__planSummary : null;
    return ps && typeof ps === 'object' ? ps : null;
}

export const usePlanStore = defineStore('plan', {
    state: () => ({
        summary: readSummary(),
    }),
    getters: {
        hasPlan: s => !!(s.summary && s.summary.has_plan),
        features: s => (s.summary && s.summary.has_plan && s.summary.features) || {},
        /** planFeature(key) — legacy Sidebar.vue semantics. */
        featureEnabled: s => key => {
            const features = (s.summary && s.summary.has_plan && s.summary.features) || {};
            const f = features[key];
            return f ? !!f.enabled : true;
        },
    },
    actions: {
        // Re-read after a plan change made in-app (billing pages) without a reload.
        refresh(summary) {
            this.summary = summary && typeof summary === 'object' ? summary : readSummary();
        },
    },
});
