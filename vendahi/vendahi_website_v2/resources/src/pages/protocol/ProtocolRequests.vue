<template>
  <div class="page">
    <PageHeader :title="tf('Pt_Requests', 'Protocol & Requests')" :breadcrumb="[tf('Pt_Protocol', 'Protocol'), tf('Pt_Requests', 'Requests')]">
      <template #actions>
        <a-space wrap>
          <a-button v-if="auth.can('protocol_manage')" @click="$router.push({ name: 'protocol-desks' })">
            <template #icon><ApartmentOutlined /></template>
            {{ tf('Pt_Desks', 'Desks') }}
          </a-button>
          <a-button v-if="auth.can('protocol_add') || auth.can('protocol_manage')" type="primary" @click="openCreate">
            <template #icon><PlusOutlined /></template>
            {{ tf('Pt_New_Request', 'New request') }}
          </a-button>
        </a-space>
      </template>
    </PageHeader>

    <!-- KPI tiles -->
    <a-row :gutter="[16, 16]" style="margin-bottom: 16px">
      <a-col v-for="k in kpiTiles" :key="k.key" :xs="12" :sm="12" :md="6">
        <a-card size="small" class="kpi-card" hoverable @click="applyKpi(k.key)">
          <div class="kpi-inner">
            <div class="kpi-icon" :style="{ background: k.tint, color: k.color }"><component :is="k.icon" /></div>
            <div class="kpi-text">
              <div class="kpi-label">{{ k.label }}</div>
              <div class="kpi-value">{{ k.value }}</div>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <!-- Filters -->
    <a-card size="small" style="margin-bottom: 16px">
      <a-space wrap :size="12">
        <a-segmented v-model:value="scope" :options="scopeOptions" @change="crud.reload()" />
        <a-select v-model:value="status" style="width: 170px" allow-clear :placeholder="$t('Status')" :options="statusOptions" @change="crud.reload()" />
        <a-select v-model:value="type" style="width: 190px" allow-clear :placeholder="tf('Pt_Type', 'Type')" :options="typeOptions" @change="crud.reload()" />
        <a-select v-model:value="priority" style="width: 140px" allow-clear :placeholder="tf('Pt_Priority', 'Priority')" :options="priorityOptions" @change="crud.reload()" />
        <a-select v-model:value="deskId" style="width: 180px" allow-clear show-search option-filter-prop="label" :placeholder="tf('Pt_Desk', 'Desk')" :options="deskOptions" @change="crud.reload()" />
      </a-space>
    </a-card>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'Ref'">
          <a style="font-weight: 600" @click="open(record)">{{ record.Ref }}</a>
        </template>
        <template v-else-if="column.key === 'subject'">
          <a @click="open(record)">{{ record.subject }}</a>
          <div class="muted small">{{ record.submitter }}<span v-if="record.channel === 'portal'"> · {{ tf('Pt_Portal', 'Portal') }}</span></div>
        </template>
        <template v-else-if="column.key === 'type'"><a-tag :color="TYPE_COLOR[record.type]">{{ label(record.type) }}</a-tag></template>
        <template v-else-if="column.key === 'priority'"><a-tag :color="PRIORITY_COLOR[record.priority]">{{ label(record.priority) }}</a-tag></template>
        <template v-else-if="column.key === 'status'">
          <a-tag :color="STATUS_COLOR[record.status]">{{ label(record.status) }}</a-tag>
          <a-tag v-if="record.overdue" color="red">{{ tf('Pt_Overdue', 'Overdue') }}</a-tag>
        </template>
        <template v-else-if="column.key === 'current_desk'">
          {{ record.current_desk || '—' }}
          <div v-if="record.assigned_to" class="muted small">{{ record.assigned_to }}</div>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space>
            <a-tooltip :title="$t('View')">
              <a-button type="text" size="small" @click="open(record)"><template #icon><EyeOutlined /></template></a-button>
            </a-tooltip>
            <a-tooltip v-if="auth.can('protocol_manage')" :title="$t('Del')">
              <a-button type="text" size="small" danger @click="crud.remove(record, { label: record.Ref, deleteEndpoint: `protocol/requests/${record.id}` })">
                <template #icon><DeleteOutlined /></template>
              </a-button>
            </a-tooltip>
          </a-space>
        </template>
      </template>
    </DataTable>

    <!-- Create -->
    <a-modal v-model:open="modalOpen" :title="tf('Pt_New_Request', 'New request')" :confirm-loading="submitting" :ok-text="$t('submit')" :cancel-text="$t('Delete_cancelButtonText')" width="720px" @ok="submit">
      <a-form ref="formRef" :model="form" :rules="rules" layout="vertical" style="margin-top: 8px">
        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item :label="tf('Pt_Type', 'Type') + ' *'" name="type">
              <a-select v-model:value="form.type" :options="typeOptions" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="12">
            <a-form-item :label="tf('Pt_Priority', 'Priority')" name="priority">
              <a-select v-model:value="form.priority" :options="priorityOptions" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="tf('Pt_Subject', 'Subject') + ' *'" name="subject">
          <a-input v-model:value="form.subject" :maxlength="255" />
        </a-form-item>
        <a-form-item :label="tf('Pt_Details', 'Details')" name="body">
          <a-textarea v-model:value="form.body" :rows="5" />
        </a-form-item>
        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item :label="tf('Pt_Send_To_Desk', 'Send to desk')" :extra="tf('Pt_Send_To_Desk_Help', 'Empty = the first desk in the chain')">
              <a-select v-model:value="form.desk_id" allow-clear :options="deskOptions" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="12">
            <a-form-item :label="tf('Pt_Due_Date', 'Due date')">
              <a-date-picker v-model:value="form.due_date" style="width: 100%" value-format="YYYY-MM-DD" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-divider orientation="left" style="font-size: 13px">{{ tf('Pt_On_Behalf_Of', 'On behalf of a customer (optional)') }}</a-divider>
        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item :label="$t('Customer')">
              <a-select
                v-model:value="form.client_id" allow-clear show-search :filter-option="false" :options="clientOptions"
                :placeholder="$t('Search')" @search="searchClients"
              />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="6">
            <a-form-item :label="tf('Pt_Submitter_Name', 'Submitter name')">
              <a-input v-model:value="form.submitter_name" :maxlength="150" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="6">
            <a-form-item :label="$t('Email')">
              <a-input v-model:value="form.submitter_email" :maxlength="150" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('Attachments')">
          <a-upload :file-list="files" :before-upload="() => false" multiple @change="({ fileList }) => (files = fileList)">
            <a-button><template #icon><UploadOutlined /></template>{{ $t('Choose_a_file') }}</a-button>
          </a-upload>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Protocol & Requests list — GET protocol/requests {requests, totalRows} with
 * scope (inbox = at my desks / assigned to me, mine = submitted by me, all),
 * status/type/priority/desk filters; POST protocol/requests (multipart).
 */
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { message } from 'ant-design-vue';
import {
  PlusOutlined, EyeOutlined, DeleteOutlined, UploadOutlined, ApartmentOutlined,
  InboxOutlined, ClockCircleOutlined, WarningOutlined, CheckCircleOutlined,
} from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { useAuthStore } from '../../stores/auth';
import http from '../../lib/http';
import { TYPES, PRIORITIES, STATUSES, STATUS_COLOR, PRIORITY_COLOR, TYPE_COLOR, makeLabel } from './protocolShared';

const { t } = useI18n();
const tf = (key, fallback) => { const out = t(key); return out === key ? fallback : out; };
const label = makeLabel(t);
const router = useRouter();
const auth = useAuthStore();

const scope = ref('inbox');
const status = ref(undefined);
const type = ref(undefined);
const priority = ref(undefined);
const deskId = ref(undefined);
const meta = ref({ desks: [], users: [], me: {} });
const kpis = ref({});

const scopeOptions = computed(() => [
  { value: 'inbox', label: tf('Pt_Inbox', 'My desk') },
  { value: 'all', label: t('All') },
  { value: 'mine', label: tf('Pt_Submitted_By_Me', 'Submitted by me') },
]);
const statusOptions = computed(() => [{ value: 'active', label: label('active') }, ...STATUSES.map(s => ({ value: s, label: label(s) }))]);
const typeOptions = computed(() => TYPES.map(x => ({ value: x, label: label(x) })));
const priorityOptions = computed(() => PRIORITIES.map(x => ({ value: x, label: label(x) })));
const deskOptions = computed(() => (meta.value.desks || []).filter(d => d.is_active).map(d => ({ value: d.id, label: d.name })));

const filterParams = () => ({
  scope: scope.value,
  status: status.value || '',
  type: type.value || '',
  priority: priority.value || '',
  desk_id: deskId.value || '',
});

const crud = useCrudTable('protocol/requests', { rowsKey: 'requests', sortField: 'last_activity_at', sortType: 'desc', params: filterParams });

const columns = computed(() => [
  { title: 'Ref', dataIndex: 'Ref', key: 'Ref', sorter: true, width: 110 },
  { title: tf('Pt_Subject', 'Subject'), dataIndex: 'subject', key: 'subject', sorter: true },
  { title: tf('Pt_Type', 'Type'), dataIndex: 'type', key: 'type', sorter: true, width: 150 },
  { title: tf('Pt_Priority', 'Priority'), dataIndex: 'priority', key: 'priority', sorter: true, width: 110 },
  { title: t('Status'), dataIndex: 'status', key: 'status', sorter: true, width: 160 },
  { title: tf('Pt_Desk', 'Desk'), dataIndex: 'current_desk', key: 'current_desk', width: 170 },
  { title: tf('Pt_Due_Date', 'Due date'), dataIndex: 'due_date', key: 'due_date', sorter: true, width: 110 },
  { title: tf('Pt_Last_Activity', 'Last activity'), dataIndex: 'last_activity_at', key: 'last_activity_at', sorter: true, width: 160 },
  { title: t('Action'), key: 'actions', width: 100, align: 'center' },
]);

const kpiTiles = computed(() => [
  { key: 'inbox', label: tf('Pt_Inbox', 'My desk'), value: kpis.value.inbox ?? 0, icon: InboxOutlined, color: '#1677ff', tint: 'rgba(22, 119, 255, 0.12)' },
  { key: 'awaiting', label: tf('Pt_Open', 'Awaiting receipt'), value: kpis.value.awaiting_receipt ?? 0, icon: ClockCircleOutlined, color: '#6d28d9', tint: 'rgba(109, 40, 217, 0.12)' },
  { key: 'overdue', label: tf('Pt_Overdue', 'Overdue'), value: kpis.value.overdue ?? 0, icon: WarningOutlined, color: '#ef4444', tint: 'rgba(239, 68, 68, 0.12)' },
  { key: 'closed', label: tf('Pt_Closed_This_Month', 'Closed this month'), value: kpis.value.closed_this_month ?? 0, icon: CheckCircleOutlined, color: '#10b981', tint: 'rgba(16, 185, 129, 0.12)' },
]);

function applyKpi(key) {
  status.value = key === 'awaiting' ? 'open' : key === 'closed' ? 'closed' : key === 'overdue' ? 'active' : undefined;
  scope.value = key === 'inbox' ? 'inbox' : 'all';
  crud.reload();
}

async function loadMeta() {
  try {
    const [m, k] = await Promise.all([http.get('protocol/meta'), http.get('protocol/dashboard')]);
    meta.value = m || meta.value;
    kpis.value = k || {};
  } catch (e) { /* the list still works without the tiles */ }
}

function open(record) {
  router.push({ name: 'protocol-request-details', params: { id: record.id } });
}

/* ------------------------------------------------------------- create */
const modalOpen = ref(false);
const submitting = ref(false);
const formRef = ref();
const files = ref([]);
const clientOptions = ref([]);
const blank = () => ({ type: 'request', subject: '', body: '', priority: 'normal', desk_id: undefined, due_date: null, client_id: undefined, submitter_name: '', submitter_email: '' });
const form = ref(blank());
const rules = computed(() => ({
  type: [{ required: true, message: t('Field_is_required') }],
  subject: [{ required: true, message: t('Field_is_required') }],
}));

function openCreate() {
  form.value = blank();
  files.value = [];
  modalOpen.value = true;
}

let searchTimer = null;
function searchClients(q) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(async () => {
    try {
      const data = await http.get('protocol/clients', { search: q || '' });
      clientOptions.value = (data.clients || []).map(c => ({ value: c.id, label: c.name + (c.email ? ` (${c.email})` : '') }));
    } catch (e) { clientOptions.value = []; }
  }, 250);
}

async function submit() {
  try { await formRef.value.validate(); } catch (e) { message.error(t('Please_fill_the_form_correctly')); return; }
  submitting.value = true;
  try {
    const fd = new FormData();
    Object.entries(form.value).forEach(([k, v]) => { if (v !== null && v !== undefined && v !== '') fd.append(k, v); });
    files.value.forEach(f => { if (f.originFileObj) fd.append('attachments[]', f.originFileObj); });
    const res = await http.postForm('protocol/requests', fd);
    message.success(t('Created_in_successfully'));
    modalOpen.value = false;
    await Promise.all([crud.reload(), loadMeta()]);
    if (res?.id) router.push({ name: 'protocol-request-details', params: { id: res.id } });
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  await loadMeta();
  // Users on no desk have an empty inbox — start them on "All".
  if (!meta.value.me?.manage && !(meta.value.me?.desk_ids || []).length) scope.value = 'all';
  crud.fetchRows();
  searchClients('');
});
</script>

<style scoped>
.muted { color: rgba(var(--ink-rgb), 0.45); }
.small { font-size: 12px; }
.kpi-card { border-radius: 10px; cursor: pointer; }
.kpi-inner { display: flex; align-items: center; gap: 12px; }
.kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex: 0 0 auto; }
.kpi-text { min-width: 0; flex: 1 1 auto; }
.kpi-label { font-size: 12px; color: rgba(var(--ink-rgb), 0.45); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kpi-value { font-size: 20px; font-weight: 700; }
</style>
