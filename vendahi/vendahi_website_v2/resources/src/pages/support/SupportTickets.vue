<template>
  <div class="page">
    <PageHeader :title="$t('my_tickets')" :breadcrumb="[$t('support_center'), $t('my_tickets')]">
      <template #actions>
        <a-button type="primary" @click="$router.push('/support/tickets/create')"><template #icon><PlusOutlined /></template>{{ $t('new_ticket') }}</a-button>
      </template>
    </PageHeader>

    <a-row :gutter="[12, 12]" style="margin-bottom: 16px">
      <a-col v-for="card in statCards" :key="card.key" :xs="12" :xl="6">
        <a-card size="small" hoverable class="stat" :class="{ active: status === card.status }" @click="setFilter(card.status)">
          <div class="stat-icon" :style="{ color: card.color, background: card.color + '1f' }"><component :is="card.icon" /></div>
          <div><div class="stat-value">{{ stats[card.key] ?? 0 }}</div><div class="muted">{{ $t(card.labelKey) }}</div></div>
        </a-card>
      </a-col>
    </a-row>

    <div style="margin-bottom: 12px">
      <a-radio-group :value="status" button-style="solid" @change="e => setFilter(e.target.value)">
        <a-radio-button value="">{{ $t('all') }}</a-radio-button>
        <a-radio-button v-for="f in statusFilters" :key="f.value" :value="f.value">{{ $t(f.labelKey) }}</a-radio-button>
      </a-radio-group>
    </div>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'ticket_number'">
          <a @click="$router.push(`/support/tickets/${record.id}`)"><strong>{{ record.ticket_number }}</strong></a>
          <a-tag v-if="record.tenant_unread" color="error" style="margin-left: 6px">{{ $t('new_reply') }}</a-tag>
        </template>
        <template v-else-if="column.key === 'subject'"><a @click="$router.push(`/support/tickets/${record.id}`)">{{ record.subject }}</a></template>
        <template v-else-if="column.key === 'category'">{{ $t('cat_' + record.category) }}</template>
        <template v-else-if="column.key === 'priority'"><a-tag :color="priorityColor(record.priority)">{{ $t('pri_' + record.priority) }}</a-tag></template>
        <template v-else-if="column.key === 'status'"><a-tag :color="statusColor(record.status)">{{ $t('status_' + record.status) }}</a-tag></template>
        <template v-else-if="column.key === 'last_reply_at'">{{ fmtDateTime(record.last_reply_at || record.created_at) }}</template>
        <template v-else-if="column.key === 'actions'">
          <a-tooltip :title="$t('view')">
            <a-button type="text" size="small" @click="$router.push(`/support/tickets/${record.id}`)"><template #icon><EyeOutlined style="color: #1677ff" /></template></a-button>
          </a-tooltip>
        </template>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
/**
 * SaaS support centre — port of legacy support/TicketsList.vue.
 *   GET support/tickets?page&status&search&limit → {tickets, totalRows, stats{open,pending,resolved,closed}}
 * (Tickets are stored centrally, scoped by tenant.) Strings come from the
 * bundled i18n/support.js when the tenant table lacks them.
 */
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { PlusOutlined, EyeOutlined, InboxOutlined, ClockCircleOutlined, CheckCircleOutlined, FolderOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { statusColor, priorityColor, fmtDateTime } from './supportFormat';

const { t } = useI18n();
const status = ref('');
const stats = ref({ open: 0, pending: 0, resolved: 0, closed: 0 });

const crud = useCrudTable('support/tickets', {
  params: () => ({ status: status.value || '' }),
  select: p => { stats.value = p?.stats || stats.value; return { rows: p?.tickets || [], total: Number(p?.totalRows) || 0 }; },
});
crud.fetchRows();

function setFilter(s) { if (status.value === s) return; status.value = s; crud.fetchRows(); }

const statCards = [
  { key: 'open', status: 'open', labelKey: 'stat_open', icon: InboxOutlined, color: '#5e72e4' },
  { key: 'pending', status: 'pending', labelKey: 'stat_pending', icon: ClockCircleOutlined, color: '#fb6340' },
  { key: 'resolved', status: 'resolved', labelKey: 'stat_resolved', icon: CheckCircleOutlined, color: '#2dce89' },
  { key: 'closed', status: 'closed', labelKey: 'stat_closed', icon: FolderOutlined, color: '#8898aa' },
];
const statusFilters = [
  { value: 'open', labelKey: 'status_open' }, { value: 'pending', labelKey: 'status_pending' },
  { value: 'resolved', labelKey: 'status_resolved' }, { value: 'closed', labelKey: 'status_closed' },
];
const columns = computed(() => [
  { title: t('ticket_number'), key: 'ticket_number', width: 170 },
  { title: t('subject'), key: 'subject' },
  { title: t('category'), key: 'category', width: 140 },
  { title: t('priority'), key: 'priority', width: 110 },
  { title: t('status'), key: 'status', width: 110 },
  { title: t('last_activity'), key: 'last_reply_at', width: 160 },
  { title: t('Action'), key: 'actions', width: 80, align: 'center' },
]);
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.stat :deep(.ant-card-body) { display: flex; align-items: center; gap: 12px; }
.stat.active { border-color: #1677ff; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex: none; }
.stat-value { font-size: 22px; font-weight: 700; line-height: 1.1; }
</style>
