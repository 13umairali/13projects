import { ref } from 'vue';

/**
 * Global "plan limit reached" state (SaaS-only).
 *
 * The API answers a blocked action with 403 {status: 'limit_reached', message}.
 * lib/http.js calls showLimitReached(message); App.vue renders
 * <LimitReachedModal> bound to this state — pages never special-case it
 * (migration rule 10). Mirrors legacy main.js:284 + App.vue's overlay.
 */
export const limitReachedVisible = ref(false);
export const limitReachedMessage = ref('');
/** 'limit' (quota hit, 403 limit_reached) or 'feature' (403 feature_unavailable). */
export const limitReachedKind = ref('limit');

const DEFAULT_MESSAGE = 'You have reached a plan limit. Please upgrade to continue.';

export function showLimitReached(message, kind = 'limit') {
    limitReachedKind.value = kind === 'feature' ? 'feature' : 'limit';
    limitReachedMessage.value = message || DEFAULT_MESSAGE;
    limitReachedVisible.value = true;
}

export function hideLimitReached() {
    limitReachedVisible.value = false;
}
