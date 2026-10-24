<template>
  <div class="page">
    <PageHeader :title="tf('Incoming_Logs', 'Incoming Logs')" :breadcrumb="[$t('Settings'), $t('Webhooks'), tf('Incoming_Logs', 'Incoming Logs')]">
      <template #actions>
        <a-input v-model:value="sourceFilter" :placeholder="tf('Source', 'Source')" style="width: 150px" allow-clear @change="crud.fetchRows()" />
        <a-select v-model:value="statusFilter" style="width: 160px" :options="statusOptions" @change="crud.fetchRows()" />
        <a-button @click="$router.push('/settings/webhooks')">
          <template #icon><ArrowLeftOutlined /></template>
          {{ $t('Webhooks') }}
        </a-button>
      </template>
    </PageHeader>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'status'">
          <a-tag :color="statusColor(record.status)">{{ record.status }}</a-tag>
        </template>
        <template v-else-if="column.key === 'signature_valid'">
          <a-tag v-if="record.signature_valid === true || record.signature_valid === 1" color="success">{{ tf('Valid', 'Valid') }}</a-tag>
          <a-tag v-else-if="record.signature_valid === false || record.signature_valid === 0" color="error">{{ tf('Invalid', 'Invalid') }}</a-tag>
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

    <a-modal :open="!!active" :title="tf('Incoming_Details', 'Incoming Webhook')" :footer="null" width="760px" @cancel="active = null">
      <template v-if="active">
        <a-descriptions size="small" :column="2" bordered style="margin-bottom: 12px">
          <a-descriptions-item :label="tf('Source', 'Source')">{{ active.source }}</a-descriptions-item>
          <a-descriptions-item :label="tf('Event', 'Event')">{{ active.event }}</a-descriptions-item>
          <a-descriptions-item :label="$t('Status')"><a-tag :color="statusColor(active.status)">{{ active.status }}</a-tag></a-descriptions-item>
          <a-descriptions-item label="IP">{{ active.ip || '—' }}</a-descriptions-item>
          <a-descriptions-item v-if="active.error_message" :label="tf('Error', 'Error')" :span="2">{{ active.error_message }}</a-descriptions-item>
        </a-descriptions>
        <h4>{{ tf('Headers', 'Headers') }}</h4>
        <pre class="log-pre">{{ formatJson(active.headers) }}</pre>
        <h4>{{ tf('Payload', 'Payload') }}</h4>
        <pre class="log-pre">{{ formatJson(active.payload) }}</pre>
      </template>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Port of legacy settings/webhooks/Incoming_Logs.vue.
 *   GET webhooks/incoming-logs (page/SortField/SortType/search/limit + source, status) → {logs, totalRows}
 */
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ArrowLeftOutlined, EyeOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const sourceFilter = ref('');
const statusFilter = ref('');
const statusOptions = [
  { value: '', label: tf('All_statuses', 'All statuses') },
  { value: 'received', label: tf('Received', 'Received') },
  { value: 'processed', label: tf('Processed', 'Processed') },
  { value: 'failed', label: tf('Failed', 'Failed') },
  { value: 'ignored', label: tf('Ignored', 'Ignored') },
];
const active = ref(null);

const crud = useCrudTable('webhooks/incoming-logs', {
  rowsKey: 'logs',
  params: () => ({ source: sourceFilter.value || undefined, status: statusFilter.value || undefined }),
});
crud.fetchRows();

const columns = computed(() => [
  { title: 'ID', dataIndex: 'id', key: 'id', width: 80, sorter: true },
  { title: tf('Source', 'Source'), dataIndex: 'source', key: 'source', sorter: true },
  { title: tf('Event', 'Event'), dataIndex: 'event', key: 'event', sorter: true },
  { title: t('Status'), key: 'status', width: 110, align: 'center', sorter: true },
  { title: tf('Signature', 'Signature'), key: 'signature_valid', width: 110, align: 'center' },
  { title: 'IP', dataIndex: 'ip', key: 'ip', width: 140 },
  { title: tf('Created_At', 'Created'), dataIndex: 'created_at', key: 'created_at', width: 170, sorter: true },
  { title: t('Action'), key: 'actions', width: 80, align: 'center' },
]);

function statusColor(s) {
  return { processed: 'success', received: 'blue', failed: 'error', ignored: 'default' }[s] || 'default';
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
