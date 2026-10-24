<template>
  <div class="page">
    <PageHeader :title="tf('Delivery_Logs', 'Delivery Logs')" :breadcrumb="[$t('Settings'), $t('Webhooks'), tf('Delivery_Logs', 'Delivery Logs')]">
      <template #actions>
        <a-select v-model:value="statusFilter" style="width: 160px" :options="statusOptions" @change="crud.fetchRows()" />
        <a-button @click="$router.push('/settings/webhooks')">
          <template #icon><ArrowLeftOutlined /></template>
          {{ $t('Webhooks') }}
        </a-button>
      </template>
    </PageHeader>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'webhook'">{{ record.webhook ? record.webhook.name : '—' }}</template>
        <template v-else-if="column.key === 'status'">
          <a-tag :color="statusColor(record.status)">{{ record.status }}</a-tag>
        </template>
        <template v-else-if="column.key === 'response_code'">
          <a-tag v-if="record.response_code" :color="httpColor(record.response_code)">{{ record.response_code }}</a-tag>
          <span v-else>—</span>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-tooltip :title="tf('View', 'View')">
            <a-button type="text" size="small" @click="active = record">
              <template #icon><EyeOutlined style="color: #1677ff" /></template>
            </a-button>
          </a-tooltip>
        </template>
      </template>
    </DataTable>

    <a-modal :open="!!active" :title="tf('Delivery_Details', 'Delivery Details')" :footer="null" width="760px" @cancel="active = null">
      <template v-if="active">
        <a-descriptions size="small" :column="2" bordered style="margin-bottom: 12px">
          <a-descriptions-item :label="tf('Event', 'Event')">{{ active.event }}</a-descriptions-item>
          <a-descriptions-item :label="tf('Webhook', 'Webhook')">{{ active.webhook && active.webhook.name }}</a-descriptions-item>
          <a-descriptions-item label="URL" :span="2">{{ active.webhook && active.webhook.url }}</a-descriptions-item>
          <a-descriptions-item :label="$t('Status')"><a-tag :color="statusColor(active.status)">{{ active.status }}</a-tag></a-descriptions-item>
          <a-descriptions-item :label="tf('Response_code', 'Response code')">{{ active.response_code || '—' }}</a-descriptions-item>
          <a-descriptions-item :label="tf('Attempt', 'Attempt')">{{ active.attempt }}</a-descriptions-item>
          <a-descriptions-item :label="tf('Duration', 'Duration')">{{ active.duration_ms }} ms</a-descriptions-item>
          <a-descriptions-item v-if="active.error_message" :label="tf('Error', 'Error')" :span="2">{{ active.error_message }}</a-descriptions-item>
        </a-descriptions>
        <h4>{{ tf('Payload', 'Payload') }}</h4>
        <pre class="log-pre">{{ formatJson(active.payload) }}</pre>
        <h4>{{ tf('Response_body', 'Response body') }}</h4>
        <pre class="log-pre">{{ active.response_body || '—' }}</pre>
      </template>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Port of legacy settings/webhooks/Delivery_Logs.vue.
 *   GET webhooks/deliveries (page/SortField/SortType/search/limit + status) → {deliveries, totalRows}
 */
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ArrowLeftOutlined, EyeOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const statusFilter = ref('');
const statusOptions = [
  { value: '', label: tf('All_statuses', 'All statuses') },
  { value: 'pending', label: tf('Pending', 'Pending') },
  { value: 'retrying', label: tf('Retrying', 'Retrying') },
  { value: 'success', label: tf('Success', 'Success') },
  { value: 'failed', label: tf('Failed', 'Failed') },
];
const active = ref(null);

const crud = useCrudTable('webhooks/deliveries', {
  rowsKey: 'deliveries',
  params: () => ({ status: statusFilter.value || undefined }),
});
crud.fetchRows();

const columns = computed(() => [
  { title: 'ID', dataIndex: 'id', key: 'id', width: 80, sorter: true },
  { title: tf('Event', 'Event'), dataIndex: 'event', key: 'event', sorter: true },
  { title: tf('Webhook', 'Webhook'), key: 'webhook' },
  { title: t('Status'), key: 'status', width: 110, align: 'center', sorter: true },
  { title: 'HTTP', key: 'response_code', width: 90, align: 'center' },
  { title: tf('Attempt', 'Attempt'), dataIndex: 'attempt', key: 'attempt', width: 90, align: 'center' },
  { title: tf('Created_At', 'Created'), dataIndex: 'created_at', key: 'created_at', width: 170, sorter: true },
  { title: t('Action'), key: 'actions', width: 80, align: 'center' },
]);

function statusColor(s) {
  return { success: 'success', failed: 'error', pending: 'default', retrying: 'warning' }[s] || 'default';
}
function httpColor(code) {
  if (code >= 200 && code < 300) return 'success';
  if (code >= 400 && code < 500) return 'warning';
  if (code >= 500) return 'error';
  return 'default';
}
function formatJson(value) {
  if (!value) return '—';
  try { return JSON.stringify(typeof value === 'string' ? JSON.parse(value) : value, null, 2); } catch (e) { return String(value); }
}
</script>

<style scoped>
.log-pre {
  max-height: 260px;
  overflow: auto;
  background: rgba(128, 128, 128, 0.08);
  padding: 8px;
  border-radius: 6px;
  font-size: 12px;
}
</style>
