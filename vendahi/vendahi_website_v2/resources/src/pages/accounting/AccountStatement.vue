<template>
  <div class="page">
    <PageHeader
      :title="`${tf('Account_Statement', 'Account statement')}${account.account_name ? ' — ' + account.account_name : ''}`"
      :breadcrumb="[$t('Accounting'), $t('List_accounts'), tf('Account_Statement', 'Account statement')]"
    >
      <template #actions>
        <a-space wrap>
          <!-- Multi-Currency: view amounts converted (display-only) -->
          <ViewCurrencySelect />
          <a-button :loading="exporting === 'pdf'" @click="exportList('pdf')">
            <template #icon><FilePdfOutlined /></template>
            {{ $t('PDF') }}
          </a-button>
          <a-button :loading="exporting === 'xlsx'" @click="exportList('xlsx')">
            <template #icon><FileExcelOutlined /></template>
            {{ $t('EXCEL') }}
          </a-button>
          <a-button @click="doPrint">
            <template #icon><PrinterOutlined /></template>
            {{ $t('print') }}
          </a-button>
          <a-button @click="$router.push({ name: 'accounts' })">
            <template #icon><ArrowLeftOutlined /></template>
            {{ $t('List_accounts') }}
          </a-button>
        </a-space>
      </template>
    </PageHeader>

    <!-- Filters: the statement is all-time by default; any custom range narrows it. -->
    <a-card size="small" style="margin-bottom: 16px">
      <a-row :gutter="[16, 8]" align="bottom">
        <a-col :xs="24" :md="10" :xl="7">
          <div class="filter-label">{{ $t('date') }}</div>
          <DateRangePicker
            v-model:value="range"
            style="width: 100%"
            :allow-clear="false"
            allow-all-time
            :placeholder="[tf('All_Time', 'All time'), tf('All_Time', 'All time')]"
            @change="crud.reload()"
          />
        </a-col>
        <a-col :xs="24" :md="8" :xl="6">
          <div class="filter-label">{{ $t('account_num') }}</div>
          <a-input :value="account.account_num" readonly />
        </a-col>
        <a-col :xs="24" :md="6" :xl="5">
          <div class="filter-label">{{ tf('Order', 'Order') }}</div>
          <a-segmented v-model:value="order" :options="orderOptions" @change="onOrderChange" />
        </a-col>
      </a-row>
    </a-card>

    <!-- KPI tiles: opening + movement + closing, so the balance is explained -->
    <a-row :gutter="[16, 16]" style="margin-bottom: 16px">
      <a-col v-for="k in kpiTiles" :key="k.label" :xs="12" :sm="12" :md="6">
        <a-card size="small" class="kpi-card">
          <div class="kpi-inner">
            <div class="kpi-icon" :style="{ background: k.tint, color: k.color }">
              <component :is="k.icon" />
            </div>
            <div class="kpi-text">
              <div class="kpi-label">{{ k.label }}</div>
              <div class="kpi-value" :style="k.style">
                <a-spin v-if="crud.loading.value" size="small" />
                <template v-else>{{ k.value }}</template>
              </div>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <!-- Reconciliation notice: the ledger explains the stored balance, or it doesn't. -->
    <a-alert
      v-if="!crud.loading.value && Math.abs(totals.unexplained_difference) >= 0.005"
      type="warning" show-icon style="margin-bottom: 16px"
      :message="tf('Balance_Not_Reconciled', 'The stored balance differs from the transaction history')"
      :description="`${tf('Computed_Balance', 'Computed balance')}: ${money(totals.computed_balance)} · ${tf('Stored_Balance', 'Stored balance')}: ${money(account.balance)} · ${tf('Difference', 'Difference')}: ${money(totals.unexplained_difference)}`"
    />
    <a-alert
      v-else-if="!crud.loading.value && account.id"
      type="success" show-icon style="margin-bottom: 16px"
      :message="tf('Balance_Reconciled', 'The current balance is fully explained by the transactions below')"
      :description="`${$t('initial_balance')}: ${money(account.initial_balance)} + ${tf('Total_Credit', 'Total Credit')} ${money(totals.all_time_credit)} − ${tf('Total_Debit', 'Total Debit')} ${money(totals.all_time_debit)} = ${money(account.balance)}`"
    />

    <!-- Statement table: Date | Type | Reference | Description | Debit | Credit | Balance -->
    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'date'">{{ record.date }}</template>
        <template v-else-if="column.key === 'type'">
          <a-tag :color="typeColor(record.type)">{{ typeLabel(record.type) }}</a-tag>
        </template>
        <template v-else-if="column.key === 'debit'">
          <span v-if="Number(record.debit)" class="amount-out">{{ money(record.debit) }}</span>
          <span v-else class="muted">—</span>
        </template>
        <template v-else-if="column.key === 'credit'">
          <span v-if="Number(record.credit)" class="amount-in">{{ money(record.credit) }}</span>
          <span v-else class="muted">—</span>
        </template>
        <template v-else-if="column.key === 'balance'">
          <strong :class="Number(record.balance) < 0 ? 'amount-out' : ''">{{ money(record.balance) }}</strong>
        </template>
      </template>
      <template #summary>
        <a-table-summary-row>
          <a-table-summary-cell :index="0" :col-span="4">
            <b>{{ tf('Opening_Balance', 'Opening balance') }}: {{ money(totals.opening_balance) }}</b>
          </a-table-summary-cell>
          <a-table-summary-cell :index="4" align="right">
            <div class="sum-label">{{ tf('Total_Debit', 'Total Debit') }}</div>
            <b class="amount-out">{{ money(totals.debit) }}</b>
          </a-table-summary-cell>
          <a-table-summary-cell :index="5" align="right">
            <div class="sum-label">{{ tf('Total_Credit', 'Total Credit') }}</div>
            <b class="amount-in">{{ money(totals.credit) }}</b>
          </a-table-summary-cell>
          <a-table-summary-cell :index="6" align="right">
            <div class="sum-label">{{ tf('Current_Balance', 'Current balance') }}</div>
            <b>{{ money(totals.closing_balance) }}</b>
          </a-table-summary-cell>
        </a-table-summary-row>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
/**
 * Account statement — GET accounts/{id}/statement → {account, rows,
 * totalRows, totals}. Every movement that touched the account (payments,
 * expenses, deposits, transfers, payroll, …) as a bank-style ledger with a
 * running balance, plus opening/closing figures so the current balance can
 * be reconciled against the history. Debit = money out, Credit = money in.
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  FilePdfOutlined, FileExcelOutlined, PrinterOutlined, ArrowLeftOutlined,
  WalletOutlined, ArrowDownOutlined, ArrowUpOutlined, BankOutlined,
} from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import DateRangePicker from '../../components/DateRangePicker.vue';
import ViewCurrencySelect from '../../components/ViewCurrencySelect.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { useFormat } from '../../composables/useFormat';
import { exportExcel, exportPdf, printRows } from '../../lib/exporters';
import http from '../../lib/http';

const { t } = useI18n();
const tf = (key, fallback) => {
  const out = t(key);
  return out === key ? fallback : out;
};
const { money } = useFormat();
const route = useRoute();
const accountId = computed(() => route.params.id);

// All time by default — the point of the statement is the full history.
const range = ref([null, null]);
const order = ref('desc');
const orderOptions = computed(() => [
  { value: 'desc', label: tf('Newest_First', 'Newest first') },
  { value: 'asc', label: tf('Oldest_First', 'Oldest first') },
]);

const filterParams = () => ({
  from: range.value?.[0]?.format?.('YYYY-MM-DD') || '',
  to: range.value?.[1]?.format?.('YYYY-MM-DD') || '',
});

const crud = useCrudTable(() => `accounts/${accountId.value}/statement`, {
  rowsKey: 'rows',
  limit: 25,
  sortField: 'date',
  sortType: 'desc',
  params: filterParams,
});

function onOrderChange() {
  crud.sortType.value = order.value;
  crud.reload();
}

const account = computed(() => crud.payload.value?.account || {});
const totals = computed(() => {
  const x = crud.payload.value?.totals || {};
  const n = k => Number(x[k]) || 0;
  return {
    opening_balance: n('opening_balance'), debit: n('debit'), credit: n('credit'),
    closing_balance: n('closing_balance'), all_time_debit: n('all_time_debit'),
    all_time_credit: n('all_time_credit'), computed_balance: n('computed_balance'),
    unexplained_difference: n('unexplained_difference'),
  };
});

/* ---------------------------------------------------------------- KPIs */
const kpiTiles = computed(() => [
  { label: tf('Opening_Balance', 'Opening balance'), value: money(totals.value.opening_balance), icon: BankOutlined, color: '#6d28d9', tint: 'rgba(109, 40, 217, 0.12)' },
  { label: tf('Total_Credit', 'Total Credit'), value: money(totals.value.credit), icon: ArrowDownOutlined, color: '#10b981', tint: 'rgba(16, 185, 129, 0.12)', style: { color: '#10b981' } },
  { label: tf('Total_Debit', 'Total Debit'), value: money(totals.value.debit), icon: ArrowUpOutlined, color: '#ef4444', tint: 'rgba(239, 68, 68, 0.12)', style: { color: '#ef4444' } },
  { label: tf('Current_Balance', 'Current balance'), value: money(totals.value.closing_balance), icon: WalletOutlined, color: '#1677ff', tint: 'rgba(22, 119, 255, 0.12)' },
]);

/* --------------------------------------------------------------- types */
const TYPE_LABELS = {
  sale_payment: ['Sale_Payment', 'Sale payment'],
  sale_return_payment: ['Sale_Return_Refund', 'Sale return refund'],
  purchase_payment: ['Purchase_Payment', 'Purchase payment'],
  purchase_return_payment: ['Purchase_Return_Refund', 'Purchase return refund'],
  expense: ['Expense', 'Expense'],
  deposit: ['Deposit', 'Deposit'],
  transfer_in: ['Transfer_In', 'Transfer in'],
  transfer_out: ['Transfer_Out', 'Transfer out'],
  payroll: ['Payroll', 'Payroll'],
  service_job_payment: ['Service_Job_Payment', 'Service job payment'],
  client_opening_balance: ['Customer_Opening_Balance', 'Customer opening balance'],
  provider_opening_balance: ['Supplier_Opening_Balance', 'Supplier opening balance'],
};
const typeLabel = type => {
  const [key, fallback] = TYPE_LABELS[type] || [type, type];
  return tf(key, fallback);
};
const IN_TYPES = ['sale_payment', 'purchase_return_payment', 'deposit', 'transfer_in', 'service_job_payment', 'client_opening_balance'];
const typeColor = type => (IN_TYPES.includes(type) ? 'green' : 'volcano');

/* --------------------------------------------------------------- table */
const columns = computed(() => [
  { title: t('date'), dataIndex: 'date', key: 'date', width: 110 },
  { title: tf('Type', 'Type'), dataIndex: 'type', key: 'type', width: 170, exportValue: r => typeLabel(r.type) },
  { title: t('Reference'), dataIndex: 'reference', key: 'reference', width: 140 },
  { title: t('Description'), dataIndex: 'description', key: 'description', ellipsis: true },
  { title: t('Debit'), dataIndex: 'debit', key: 'debit', align: 'right', width: 140, exportValue: r => (Number(r.debit) ? money(r.debit) : '') },
  { title: t('Credit'), dataIndex: 'credit', key: 'credit', align: 'right', width: 140, exportValue: r => (Number(r.credit) ? money(r.credit) : '') },
  { title: t('Balance'), dataIndex: 'balance', key: 'balance', align: 'right', width: 150, exportValue: r => money(r.balance) },
]);

/* ------------------------------------------------------------- exports */
const exporting = ref(null);

async function allRows() {
  const data = await http.get(`accounts/${accountId.value}/statement`, {
    ...filterParams(), page: 1, limit: -1, search: crud.search.value, SortType: order.value,
  });
  return data.rows || [];
}

// One totals row: Total Debit | Total Credit | Current balance (the opening
// balance rides in the title so exports stay self-explanatory).
const footRow = () => [t('Total'), '', '', '', money(totals.value.debit), money(totals.value.credit), money(totals.value.closing_balance)];

const title = () => `${tf('Account_Statement', 'Account statement')} — ${account.value.account_name || ''} (${tf('Opening_Balance', 'Opening balance')}: ${money(totals.value.opening_balance)})`;

async function exportList(kind) {
  exporting.value = kind;
  try {
    const rows = await allRows();
    if (kind === 'xlsx') await exportExcel('Account_statement', columns.value, rows, { foot: footRow() });
    else await exportPdf(title(), columns.value, rows, { foot: footRow() });
  } finally {
    exporting.value = null;
  }
}

async function doPrint() {
  // Open the window synchronously so the popup isn't blocked.
  const win = window.open('', '_blank');
  const rows = await allRows();
  printRows(title(), columns.value, rows, { win, foot: footRow() });
}

onMounted(crud.fetchRows);
</script>

<style scoped>
.filter-label {
  margin-bottom: 4px;
  color: rgba(var(--ink-rgb), 0.55);
  font-size: 13px;
}

/* KPI tiles — shared design with the report pages. */
.kpi-card { border-radius: 10px; }
.kpi-inner {
  display: flex;
  align-items: center;
  gap: 12px;
}
.kpi-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex: 0 0 auto;
}
.kpi-text { min-width: 0; flex: 1 1 auto; }
.kpi-label {
  font-size: 12px;
  color: rgba(var(--ink-rgb), 0.45);
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.kpi-value {
  font-size: 20px;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.amount-in { color: #10b981; font-weight: 600; }
.amount-out { color: #ef4444; font-weight: 600; }
.muted { color: rgba(var(--ink-rgb), 0.25); }
.sum-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: rgba(var(--ink-rgb), 0.45);
}

@media (max-width: 575px) {
  .kpi-value { font-size: 16px; }
}
</style>
