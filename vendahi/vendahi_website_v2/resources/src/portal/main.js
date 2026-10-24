/**
 * Client Portal — Vue 3 port of the legacy portal.js entry.
 * Mounts the portal SPA into #portal-app. Auth/CSRF/redirect behaviour lives in
 * lib/http (the axios-shaped fetch wrapper).
 *
 * i18n: messages come from resources/lang/{locale}/portal.php via
 * GET /api/portal/translations/{locale}; the starting locale is resolved by the
 * server and injected as window.__PORTAL_LOCALE__ (see ./i18n.js). The locale
 * is loaded BEFORE mounting so the first paint is already translated.
 */
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { i18n, loadLocale, initialLocale, enumLabel } from './i18n';

async function boot() {
  await loadLocale(initialLocale());

  const app = createApp(App);
  app.use(router);
  app.use(i18n);

  // Shared helpers for API enum values (statuses, statement types, job types):
  //   {{ statusLabel(inv.payment_status) }}   -> $t('status_paid') or the raw value
  //   {{ enumLabel('type_', e.type) }}        -> $t('type_invoice') or the raw value
  app.mixin({
    methods: {
      enumLabel(prefix, value) {
        return enumLabel(prefix, value);
      },
      statusLabel(value) {
        return enumLabel('status_', value);
      },
    },
  });

  app.mount('#portal-app');
}

boot();
