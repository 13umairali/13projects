<template>
  <div class="page">
    <PageHeader :title="tf('Message_Logs', 'Message Logs')" :breadcrumb="['WhatsApp', tf('Message_Logs', 'Message Logs')]">
      <template #actions>
        <a-select
          v-model:value="statusFilter" style="width: 160px" :options="statusOptions"
          @change="crud.fetchRows()"
        />
        <a-button @click="crud.fetchRows()">
          <template #icon><ReloadOutlined /></template>
          {{ tf('Refresh', 'Refresh') }}
        </a-button>
      </template>
    </PageHeader>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'status'">
          <a-tag :color="statusColor(record.status)">{{ record.status }}</a-tag>
        </template>
        <template v-else-if="column.key === 'error'">
          <a-tooltip v-if="record.error" :title="record.error">
            <span style="color: #ff4d4f">{{ truncate(record.error, 40) }}</span>
          </a-tooltip>
          <span v-else>—</span>
        </template>
        <template v-else-if="column.key === 'body'">
          <a-tooltip :title="record.body"><span>{{ truncate(record.body, 60) }}</span></a-tooltip>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-tooltip :title="$t('Del')">
            <a-button type="text" size="small" danger @click="crud.remove(record)">
              <template #icon><DeleteOutlined /></template>
            </a-button>
          </a-tooltip>
        </template>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
/**
 * Port of legacy whatsapp/Logs.vue.
 *   GET whatsapp/logs?page&per_page&search&status → {logs, total}
 *   DELETE whatsapp/logs/{id}
 * The endpoint paginates with per_page (not limit), hence the mapped params
 * and manual select — same shape as the error-logs report.
 */
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ReloadOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const statusFilter = ref('');
const statusOptions = [
  { value: '', label: tf('All_statuses', 'All statuses') },
  { value: 'pending', label: tf('Pending', 'Pending') },
  { value: 'sent', label: tf('Sent', 'Sent') },
  { value: 'delivered', label: tf('Delivered', 'Delivered') },
  { value: 'read', label: tf('Read', 'Read') },
  { value: 'failed', label: tf('Failed', 'Failed') },
];

let crud;
crud = useCrudTable('whatsapp/logs', {
  sortField: 'id',
  sortType: 'desc',
  params: () => ({ per_page: crud ? crud.limit.value : 15, status: statusFilter.value || undefined }),
  select: p => ({ rows: p?.logs || [], total: Number(p?.total) || 0 }),
});
crud.fetchRows();

const columns = computed(() => [
  { title: tf('Recipient', 'Recipient'), dataIndex: 'recipient', key: 'recipient' },
  { title: tf('Type', 'Type'), dataIndex: 'message_type', key: 'message_type', width: 110 },
  { title: tf('Template', 'Template'), dataIndex: 'template_key', key: 'template_key', width: 150 },
  { title: tf('Message', 'Message'), key: 'body' },
  { title: t('Status'), key: 'status', width: 110, align: 'center' },
  { title: tf('Error', 'Error'), key: 'error', width: 160 },
  { title: tf('Sent_At', 'Sent At'), dataIndex: 'sent_at', key: 'sent_at', width: 160 },
  { title: t('Action'), key: 'actions', width: 80, align: 'center' },
]);

function statusColor(s) {
  return { sent: 'blue', delivered: 'geekblue', read: 'success', failed: 'error', pending: 'default' }[s] || 'default';
}
function truncate(s, n) {
  s = String(s || '');
  return s.length > n ? s.slice(0, n) + '…' : s;
}
</script>
