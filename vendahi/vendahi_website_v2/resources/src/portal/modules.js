/**
 * Client Portal modules available on THIS backend.
 *
 * stocky's portal (which this one is a verbatim port of) has API routes for
 * quotations, appointments, contracts, a help centre and self-service account
 * deletion. This SaaS backend's routes/portal.php declares only auth,
 * dashboard, invoices (+PDF), payments, statement and profile/password — the
 * legacy Vue 2 portal still showed those pages and they 404ed. The backend is
 * off-limits for the front-end migration, so the pages are kept verbatim but
 * switched off here: nav entries hidden (layouts/PortalLayout.vue), their
 * routes redirect to the dashboard (router.js), the danger card is hidden
 * (views/Profile.vue). Flip a flag to true the day its API exists.
 */
export const PORTAL_MODULES = {
  quotations: false,   // GET/POST portal/quotations, GET portal/quotations/{id}
  appointments: false, // GET/POST portal/appointments, GET portal/appointments/{id}
  contracts: false,    // GET portal/contracts, GET portal/contracts/{id}
  help: false,         // GET portal/knowledge-base, GET portal/knowledge-base/{slug}
  deleteAccount: false, // DELETE portal/profile
  requests: true,      // GET/POST portal/requests, GET portal/requests/{id}, POST portal/requests/{id}/reply
};

export function moduleEnabled(key) {
  return !key || PORTAL_MODULES[key] !== false;
}
