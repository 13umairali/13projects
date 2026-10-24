<template>
  <div class="page">
    <PageHeader
      :title="isInvoices ? tf('Invoices', 'Invoices') : tf('Billing_History', 'Billing History')"
      :breadcrumb="[tf('Billing', 'Billing'), isInvoices ? tf('Invoices', 'Invoices') : tf('Billing_History', 'Billing History')]"
    >
      <template #actions>
        <a-button @click="$router.push('/subscription/current-plan')"><template #icon><ArrowLeftOutlined /></template>{{ tf('Current_Plan', 'Current Plan') }}</a-button>
      </template>
    </PageHeader>

    <a-card size="small">
      <template #title>{{ isInvoices ? tf('Invoice_List', 'Invoice List') : tf('Payment_History', 'Payment History') }}</template>
      <template #extra><span class="muted">{{ total }} {{ isInvoices ? tf('invoices', 'invoice(s)') : tf('transactions', 'transaction(s)') }}</span></template>
      <a-table
        size="small" :columns="columns" :data-source="rows" row-key="id" :loading="loading"
        :pagination="{ current: page, pageSize: 15, total, showSizeChanger: false }"
        :scroll="{ x: 800 }"
        @change="p => load(p.current)"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'invoice_number'"><strong>{{ record.invoice_number || '—' }}</strong></template>
          <template v-else-if="column.key === 'plan'">{{ record.plan_name }} <span v-if="!isInvoices" class="muted">({{ capitalize(record.billing_cycle) }})</span></template>
          <template v-else-if="column.key === 'amount'"><strong>{{ money(isInvoices ? record.total : record.amount, record.currency) }}</strong></template>
          <template v-else-if="column.key === 'status'"><a-tag :color="statusColor(record.status)">{{ capitalize(record.status) }}</a-tag></template>
          <template v-else-if="column.key === 'cycle'">{{ capitalize(record.billing_cycle) }}</template>
          <template v-else-if="column.key === 'date'">{{ fmtDate(isInvoices ? record.paid_at : record.created_at) }}</template>
          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button v-if="!isInvoices && record.status === 'failed'" size="small" :loading="busy === 'retry' + record.id" @click="retry(record)">{{ tf('Retry', 'Retry') }}</a-button>
              <a-button v-if="isInvoices || record.status === 'paid'" size="small" :loading="busy === 'pdf' + record.id" @click="downloadPdf(record)"><template #icon><DownloadOutlined /></template>PDF</a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script setup>
/**
 * Billing history + invoices — one component for legacy billing_history.vue and
 * billing_invoices.vue (route meta.kind = 'invoices').
 *   GET  billing/history?page&per_page  → {payments, current_page, last_page, total, currency_symbol}
 *   GET  billing/invoices?page&per_page → {invoices, …}
 *   GET  billing/invoices/{id}/download (PDF)   POST billing/retry/{id} → {plan_id, billing_cycle}
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { ArrowLeftOutlined, DownloadOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';
import { useBillingFormat } from './billingFormat';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const isInvoices = route.meta?.kind === 'invoices';
const { currencySymbol, fmtDate, capitalize, money, statusColor } = useBillingFormat();

const loading = ref(true);
const rows = ref([]);
const page = ref(1);
const total = ref(0);
const busy = ref('');

const columns = computed(() => isInvoices
  ? [
    { title: tf('Invoice', 'Invoice #'), key: 'invoice_number' },
    { title: tf('Plan', 'Plan'), key: 'plan' },
    { title: t('Amount'), key: 'amount' },
    { title: tf('Gateway', 'Gateway'), dataIndex: 'gateway_label', key: 'gateway_label' },
    { title: tf('Billing_Cycle', 'Cycle'), key: 'cycle' },
    { title: tf('Paid_Date', 'Paid Date'), key: 'date' },
    { title: '', key: 'actions', width: 110 },
  ]
  : [
    { title: tf('Invoice', 'Invoice'), key: 'invoice_number' },
    { title: tf('Plan', 'Plan'), key: 'plan' },
    { title: t('Amount'), key: 'amount' },
    { title: tf('Gateway', 'Gateway'), dataIndex: 'gateway_label', key: 'gateway_label' },
    { title: t('Status'), key: 'status' },
    { title: t('date'), key: 'date' },
    { title: '', key: 'actions', width: 170 },
  ]);

async function load(p) {
  loading.value = true;
  try {
    const data = await http.get(isInvoices ? 'billing/invoices' : 'billing/history', { page: p || 1, per_page: 15 });
    rows.value = (isInvoices ? data.invoices : data.payments) || [];
    currencySymbol.value = data.currency_symbol || '$';
    page.value = data.current_page || 1;
    total.value = data.total || 0;
  } catch (e) {
    message.error(e?.data?.message || (isInvoices ? 'Failed to load invoices.' : 'Failed to load history.'));
  } finally {
    loading.value = false;
  }
}
async function downloadPdf(record) {
  busy.value = 'pdf' + record.id;
  try {
    await http.download(`billing/invoices/${record.id}/download`, `Invoice-${record.invoice_number || record.id}.pdf`);
  } catch (e) {
    message.error(tf('Download_failed', 'Failed to download invoice.'));
  } finally {
    busy.value = '';
  }
}
async function retry(record) {
  busy.value = 'retry' + record.id;
  try {
    const data = await http.post(`billing/retry/${record.id}`, {});
    router.push({ path: `/subscription/checkout/${data.plan_id}`, query: { cycle: data.billing_cycle } });
  } catch (e) {
    message.error(e?.data?.message || 'Cannot retry this payment.');
  } finally {
    busy.value = '';
  }
}
onMounted(() => load(1));
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
</style>
