import { defineStore } from 'pinia';
import http from '../lib/http';
import { useAuthStore } from './auth';

/**
 * Everything awaiting staff action, from GET /pending-work.
 *
 * One fetch feeds two surfaces: the topbar bell (the list) and the sidebar
 * (the same numbers as badges). Counts are derived server-side from live rows,
 * so an entry disappears on the next refresh once the work is done — there is
 * nothing to mark as read.
 */
export const usePendingWorkStore = defineStore('pendingWork', {
    state: () => ({
        items: [],
        total: 0,
        loading: false,
        loadedAt: 0,
    }),

    getters: {
        /** { product_reviews: 3, messages: 1, … } for the sidebar badges. */
        byKey: (state) => Object.fromEntries(state.items.map(i => [i.key, i.count])),
    },

    actions: {
        /**
         * `force` skips the 60s throttle — used after an action that resolves
         * work, so the badge drops immediately instead of on the next poll.
         */
        async fetch(force = false) {
            if (this.loading) return;
            if (!force && Date.now() - this.loadedAt < 60000) return;

            this.loading = true;
            try {
                const data = await http.get('pending-work');
                this.items = Array.isArray(data?.items) ? data.items : [];
                this.total = Number(data?.total) || 0;
                this.loadedAt = Date.now();
            } catch (e) {
                // This API has no /pending-work (a stocky-only endpoint). Fall
                // back to the legacy bell: the quantity-alert count that
                // get_user_auth ships as `notifs`, gated like legacy did.
                const auth = useAuthStore();
                if (auth.notifs > 0 && auth.can('Reports_quantity_alerts')) {
                    this.items = [{
                        key: 'quantity_alerts',
                        count: auth.notifs,
                        label_key: 'ProductQuantityAlerts',
                        severity: 'warning',
                        route: '/app/reports/quantity_alerts',
                    }];
                    this.total = auth.notifs;
                } else {
                    this.items = [];
                    this.total = 0;
                }
                this.loadedAt = Date.now();
            } finally {
                this.loading = false;
            }
        },

        reset() {
            this.items = [];
            this.total = 0;
            this.loadedAt = 0;
        },
    },
});
