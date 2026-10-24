<template>
  <div class="page">
    <a-skeleton v-if="loading" active :paragraph="{ rows: 6 }" />

    <!-- ============================ failed ============================ -->
    <a-result v-else-if="kind === 'failed'" status="error" :title="tf('Payment_Failed', 'Payment Failed')" :sub-title="tf('Payment_failed_desc', 'We could not process your payment. You can try again or choose a different method.')">
      <template #extra>
        <a-space wrap>
          <a-button v-if="payment" type="primary" @click="$router.push({ path: `/subscription/checkout/${payment.plan_id}`, query: { cycle: payment.billing_cycle } })">{{ tf('Try_Again', 'Try Again') }}</a-button>
          <a-button @click="$router.push('/subscription/change-plan')">{{ tf('View_Plans', 'View Plans') }}</a-button>
        </a-space>
      </template>
      <a-descriptions v-if="payment" size="small" :column="1" bordered style="max-width: 480px; margin: 0 auto 16px">
        <a-descriptions-item :label="tf('Plan', 'Plan')">{{ payment.plan_name }}</a-descriptions-item>
        <a-descriptions-item :label="$t('Amount')">{{ money(payment.amount, payment.currency) }}</a-descriptions-item>
        <a-descriptions-item :label="tf('Gateway', 'Gateway')">{{ payment.gateway_label }}</a-descriptions-item>
        <a-descriptions-item :label="$t('Status')"><a-tag color="error">{{ tf('Failed', 'Failed') }}</a-tag></a-descriptions-item>
      </a-descriptions>
      <a-alert type="info" style="max-width: 480px; margin: 0 auto" :message="tf('Common_reasons', 'Common reasons for payment failure:')">
        <template #description>
          <ul style="margin: 0; padding-left: 18px">
            <li>{{ tf('Insufficient_funds', 'Insufficient funds in your account') }}</li>
            <li>{{ tf('Card_declined', 'Card was declined by your bank') }}</li>
            <li>{{ tf('Payment_cancelled', 'Payment was cancelled before completing') }}</li>
            <li>{{ tf('Gateway_issue', 'Temporary gateway issue — please retry') }}</li>
          </ul>
        </template>
      </a-alert>
    </a-result>

    <!-- ============================ success: capturing / pending ============================ -->
    <a-result v-else-if="capturing" :title="tf('Processing_Payment', 'Processing Payment…')" :sub-title="tf('Confirming_with_paypal', 'We are confirming your payment with PayPal. Please wait a moment.')">
      <template #icon><a-spin size="large" /></template>
    </a-result>
    <a-result v-else-if="payment && payment.status === 'pending'" :title="tf('Payment_Processing', 'Payment Processing')" :sub-title="tf('Payment_being_verified', 'Your payment is being verified. This page will update automatically.')">
      <template #icon><a-spin size="large" /></template>
      <template #extra><a-button @click="$router.push('/subscription/current-plan')">{{ tf('Back_to_plan', 'Back to Current Plan') }}</a-button></template>
    </a-result>

    <!-- ============================ success: paid ============================ -->
    <a-result v-else-if="payment && payment.status === 'paid'" status="success" :title="tf('Payment_Successful', 'Payment Successful!')" :sub-title="tf('Subscription_activated', 'Your subscription has been activated. You are all set!')">
      <template #extra>
        <a-space wrap>
          <a-button type="primary" @click="$router.push('/dashboard')">{{ tf('Go_to_Dashboard', 'Go to Dashboard') }}</a-button>
          <a-button @click="$router.push('/subscription/history')">{{ tf('Billing_History', 'Billing History') }}</a-button>
        </a-space>
      </template>
      <a-descriptions size="small" :column="1" bordered style="max-width: 480px; margin: 0 auto">
        <a-descriptions-item :label="tf('Invoice', 'Invoice')">{{ payment.invoice_number }}</a-descriptions-item>
        <a-descriptions-item :label="tf('Plan', 'Plan')">{{ payment.plan_name }}</a-descriptions-item>
        <a-descriptions-item :label="tf('Billing_Cycle', 'Billing')">{{ capitalize(payment.billing_cycle) }}</a-descriptions-item>
        <a-descriptions-item :label="$t('Amount')">{{ money(payment.amount, payment.currency) }}</a-descriptions-item>
        <a-descriptions-item :label="tf('Gateway', 'Gateway')">{{ payment.gateway_label }}</a-descriptions-item>
        <a-descriptions-item :label="$t('Status')"><a-tag color="success">{{ tf('Paid', 'Paid') }}</a-tag></a-descriptions-item>
        <a-descriptions-item v-if="subscription && subscription.ends_at" :label="tf('Valid_Until', 'Valid Until')">{{ fmtDate(subscription.ends_at) }}</a-descriptions-item>
      </a-descriptions>
    </a-result>

    <!-- fallback -->
    <a-result v-else status="success" :title="tf('Thank_You', 'Thank You!')" :sub-title="tf('Payment_submitted', 'Your payment has been submitted.')">
      <template #extra><a-button type="primary" @click="$router.push('/subscription/current-plan')">{{ tf('View_Plan', 'View Current Plan') }}</a-button></template>
    </a-result>
  </div>
</template>

<script setup>
/**
 * Billing payment result — one component for legacy billing/payment_success.vue
 * (route meta.kind = 'success') and payment_failed.vue (meta.kind = 'failed').
 * Gateways redirect to the LEGACY urls /app/billing/success?payment_id=…[&token=…]
 * and /app/billing/failed?payment_id=…; the router's /app/… redirect
 * (migratedRoutes.json) forwards them to /subscription/success|failed with
 * the query intact.
 *   GET  billing/payment/{id} → {payment, subscription, currency_symbol}
 *   POST billing/paypal/capture {payment_id, token}
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import http from '../../lib/http';
import { t as tf } from '../../i18n';
import { useBillingFormat } from './billingFormat';

const route = useRoute();
const kind = route.meta?.kind === 'failed' ? 'failed' : 'success';
const { currencySymbol, fmtDate, capitalize, money } = useBillingFormat();

const loading = ref(true);
const capturing = ref(false);
const payment = ref(null);
const subscription = ref(null);
let pollTimer = null;
let pollCount = 0;

async function fetchPayment() {
  const id = route.query.payment_id;
  if (!id) return;
  try {
    const data = await http.get(`billing/payment/${id}`);
    payment.value = data.payment;
    subscription.value = data.subscription;
    currencySymbol.value = data.currency_symbol || '$';
  } catch (e) { /* silent, like legacy */ }
}
function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }
function startPolling() {
  pollCount = 0;
  pollTimer = setInterval(async () => {
    pollCount++;
    await fetchPayment();
    if (!payment.value || payment.value.status !== 'pending' || pollCount >= 20) stopPolling();
  }, 3000);
}

onMounted(async () => {
  const paymentId = route.query.payment_id;
  const token = route.query.token; // PayPal order id appended by PayPal
  if (!paymentId) { loading.value = false; return; }
  if (kind === 'success' && token) {
    loading.value = false;
    capturing.value = true;
    try { await http.post('billing/paypal/capture', { payment_id: parseInt(paymentId, 10), token }); } catch (e) { /* captured in callback already */ }
    capturing.value = false;
  }
  await fetchPayment();
  loading.value = false;
  if (kind === 'success' && payment.value && payment.value.status === 'pending') startPolling();
});
onBeforeUnmount(stopPolling);
</script>
