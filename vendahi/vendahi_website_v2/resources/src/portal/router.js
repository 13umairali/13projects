import { createRouter, createWebHistory } from 'vue-router';
import { moduleEnabled } from './modules';

/**
 * Vue 3 port of the legacy portal/router.js (vue-router 3 -> 4).
 * Same routes, same /portal base, same guest/auth structure. The dynamic
 * imports are inlined by the standalone Vite build (single bundle).
 */
const router = createRouter({
  history: createWebHistory('/portal'),
  routes: [
    { path: '/login', name: 'PortalLogin', component: () => import('./views/Login.vue'), meta: { guest: true } },
    { path: '/set-password', name: 'PortalSetPassword', component: () => import('./views/SetPassword.vue'), meta: { guest: true } },
    {
      path: '/',
      component: () => import('./components/AuthGate.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          component: () => import('./layouts/PortalLayout.vue'),
          redirect: '/dashboard',
          children: [
            { path: 'dashboard', name: 'PortalDashboard', component: () => import('./views/Dashboard.vue') },
            { path: 'invoices', name: 'PortalInvoices', component: () => import('./views/Invoices.vue') },
            { path: 'invoices/:id', name: 'PortalInvoiceDetail', component: () => import('./views/InvoiceDetail.vue') },
            { path: 'payments', name: 'PortalPayments', component: () => import('./views/Payments.vue') },
            { path: 'statement', name: 'PortalStatement', component: () => import('./views/Statement.vue') },
            { path: 'quotations', name: 'PortalQuotations', component: () => import('./views/Quotations.vue'), meta: { module: 'quotations' } },
            { path: 'quotations/new', name: 'PortalQuotationRequest', component: () => import('./views/QuotationRequest.vue'), meta: { module: 'quotations' } },
            { path: 'quotations/:id', name: 'PortalQuotationDetail', component: () => import('./views/QuotationDetail.vue'), meta: { module: 'quotations' } },
            { path: 'appointments', name: 'PortalAppointments', component: () => import('./views/Appointments.vue'), meta: { module: 'appointments' } },
            { path: 'appointments/new', name: 'PortalAppointmentBook', component: () => import('./views/AppointmentBook.vue'), meta: { module: 'appointments' } },
            { path: 'appointments/:id', name: 'PortalAppointmentDetail', component: () => import('./views/AppointmentDetail.vue'), meta: { module: 'appointments' } },
            { path: 'contracts', name: 'PortalContracts', component: () => import('./views/Contracts.vue'), meta: { module: 'contracts' } },
            { path: 'contracts/:id', name: 'PortalContractDetail', component: () => import('./views/ContractDetail.vue'), meta: { module: 'contracts' } },
            { path: 'help', name: 'PortalKnowledgeBase', component: () => import('./views/KnowledgeBase.vue'), meta: { module: 'help' } },
            { path: 'help/:slug', name: 'PortalArticle', component: () => import('./views/Article.vue'), meta: { module: 'help' } },
            { path: 'requests', name: 'PortalRequests', component: () => import('./views/Requests.vue'), meta: { module: 'requests' } },
            { path: 'requests/new', name: 'PortalRequestNew', component: () => import('./views/RequestNew.vue'), meta: { module: 'requests' } },
            { path: 'requests/:id', name: 'PortalRequestDetail', component: () => import('./views/RequestDetail.vue'), meta: { module: 'requests' } },
            { path: 'profile', name: 'PortalProfile', component: () => import('./views/Profile.vue') },
          ],
        },
      ],
    },
  ],
});

// Pages whose API this backend does not have (src/portal/modules.js) bounce
// to the dashboard instead of rendering a screen that can only 404.
router.beforeEach((to, from, next) => {
  const mod = to.matched.map(r => r.meta && r.meta.module).find(Boolean);
  if (mod && !moduleEnabled(mod)) return next('/dashboard');
  next();
});

export default router;
