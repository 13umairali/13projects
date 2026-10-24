<template>
  <div class="page">
    <PageHeader :title="req.Ref ? `${req.Ref} — ${req.subject}` : tf('Pt_Request', 'Request')" :breadcrumb="[tf('Pt_Protocol', 'Protocol'), tf('Pt_Requests', 'Requests'), req.Ref || '']">
      <template #actions>
        <a-space wrap>
          <a-button @click="$router.push({ name: 'protocol-requests' })">
            <template #icon><ArrowLeftOutlined /></template>
            {{ tf('Pt_Requests', 'Requests') }}
          </a-button>
          <a-button v-if="req.can_manage || req.submitted_by_user_id === meId" @click="openEdit">
            <template #icon><EditOutlined /></template>
            {{ $t('Edit') }}
          </a-button>
        </a-space>
      </template>
    </PageHeader>

    <a-spin :spinning="loading">
      <a-row :gutter="[16, 16]">
        <!-- Left: request + history -->
        <a-col :xs="24" :xl="16">
          <a-card size="small" style="margin-bottom: 16px">
            <div class="head">
              <div>
                <a-space wrap size="small">
                  <a-tag :color="STATUS_COLOR[req.status]">{{ label(req.status) }}</a-tag>
                  <a-tag :color="TYPE_COLOR[req.type]">{{ label(req.type) }}</a-tag>
                  <a-tag :color="PRIORITY_COLOR[req.priority]">{{ label(req.priority) }}</a-tag>
                  <a-tag v-if="req.overdue" color="red">{{ tf('Pt_Overdue', 'Overdue') }}</a-tag>
                  <a-tag v-if="req.channel === 'portal'" color="cyan">{{ tf('Pt_Portal', 'Customer portal') }}</a-tag>
                </a-space>
                <h2 class="subject">{{ req.subject }}</h2>
              </div>
            </div>
            <div class="body" v-if="req.body">{{ req.body }}</div>
            <div v-else class="muted">{{ tf('Pt_No_Details', 'No details were provided.') }}</div>

            <a-divider style="margin: 12px 0" />
            <div class="muted small">{{ tf('Pt_Attachments', 'Attachments') }}</div>
            <div v-if="!(req.attachments || []).length" class="muted">—</div>
            <a-space v-else wrap>
              <a v-for="a in req.attachments" :key="a.id" :href="a.url" target="_blank" rel="noopener" class="file">
                <PaperClipOutlined /> {{ a.file_name }} <span class="muted small">({{ fileSize(a.file_size) }})</span>
                <a-button v-if="req.can_manage" type="text" size="small" danger @click.prevent="removeAttachment(a)"><template #icon><DeleteOutlined /></template></a-button>
              </a>
            </a-space>
          </a-card>

          <!-- Actions -->
          <a-card v-if="req.can_act" size="small" :title="tf('Pt_Actions', 'Desk actions')" style="margin-bottom: 16px">
            <a-space wrap>
              <a-button
                v-for="a in availableActions" :key="a.key" :type="a.type" :danger="!!a.danger"
                :loading="acting === a.key" @click="startAction(a)"
              >{{ tf(a.label[0], a.label[1]) }}</a-button>
            </a-space>
          </a-card>
          <a-alert
            v-else-if="!loading && req.id" type="info" show-icon style="margin-bottom: 16px"
            :message="tf('Pt_Not_Your_Desk', 'This request is at another desk — you can follow its progress here.')"
          />

          <!-- History -->
          <a-card size="small" :title="tf('Pt_History', 'History')">
            <a-empty v-if="!(req.events || []).length" :description="$t('No_data')" />
            <a-timeline v-else>
              <a-timeline-item v-for="e in req.events" :key="e.id" :color="EVENT_COLOR[e.action] || 'gray'">
                <div class="ev-head">
                  <b>{{ label(e.action) }}</b>
                  <span v-if="e.to_desk && ['forwarded', 'returned', 'submitted'].includes(e.action)"> → {{ e.to_desk }}</span>
                  <span v-if="e.assigned_to && e.action === 'reassigned'"> → {{ e.assigned_to }}</span>
                  <a-tag v-if="e.is_internal" style="margin-left: 6px">{{ tf('Pt_Internal', 'Internal') }}</a-tag>
                </div>
                <div class="muted small">
                  {{ e.actor }}<span v-if="e.from_desk"> · {{ e.from_desk }}</span> · {{ e.created_at }}
                </div>
                <div v-if="e.note" class="ev-note">{{ e.note }}</div>
                <a-space v-if="(e.attachments || []).length" wrap style="margin-top: 4px">
                  <a v-for="a in e.attachments" :key="a.id" :href="a.url" target="_blank" rel="noopener" class="file"><PaperClipOutlined /> {{ a.file_name }}</a>
                </a-space>
              </a-timeline-item>
            </a-timeline>
          </a-card>
        </a-col>

        <!-- Right: facts -->
        <a-col :xs="24" :xl="8">
          <a-card size="small" :title="tf('Pt_Details', 'Details')">
            <a-descriptions :column="1" size="small" bordered>
              <a-descriptions-item :label="tf('Pt_Submitted_By', 'Submitted by')">{{ req.submitter }}<div v-if="req.submitter_email" class="muted small">{{ req.submitter_email }}</div></a-descriptions-item>
              <a-descriptions-item v-if="req.client_name" :label="$t('Customer')">{{ req.client_name }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Current_Desk', 'Current desk')">{{ req.current_desk || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Previous_Desk', 'Previous desk')">{{ req.previous_desk || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Assigned_To', 'Assigned to')">{{ req.assigned_to || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Due_Date', 'Due date')">{{ req.due_date || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Submitted_At', 'Submitted')">{{ req.created_at }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Received_At', 'Received')">{{ req.received_at || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Acknowledged_At', 'Acknowledged')">{{ req.acknowledged_at || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Decided_At', 'Decision')">{{ req.decided_at || '—' }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Closed_At', 'Closed')">{{ req.closed_at || '—' }}<span v-if="req.closed_by" class="muted small"> · {{ req.closed_by }}</span></a-descriptions-item>
              <a-descriptions-item :label="tf('Pt_Last_Activity', 'Last activity')">{{ req.last_activity_at || '—' }}</a-descriptions-item>
            </a-descriptions>
          </a-card>
        </a-col>
      </a-row>
    </a-spin>

    <!-- Action modal -->
    <a-modal v-model:open="actionOpen" :title="current ? tf(current.label[0], current.label[1]) : ''" :confirm-loading="!!acting" :ok-text="$t('submit')" :cancel-text="$t('Delete_cancelButtonText')" @ok="runAction">
      <a-form layout="vertical" style="margin-top: 8px">
        <a-form-item v-if="current?.desk" :label="tf('Pt_Forward_To', 'Forward to desk')" :extra="tf('Pt_Forward_Default', 'Empty = the next desk in the chain')">
          <a-select v-model:value="actionForm.to_desk_id" allow-clear :options="deskOptions" />
        </a-form-item>
        <a-form-item v-if="current?.user" :label="tf('Pt_Assign_To', 'Assign to officer')">
          <a-select v-model:value="actionForm.assigned_to_user_id" show-search option-filter-prop="label" :options="userOptions" />
        </a-form-item>
        <a-form-item :label="current?.needsNote ? tf('Pt_Note', 'Note') + ' *' : tf('Pt_Note', 'Note')">
          <a-textarea v-model:value="actionForm.note" :rows="4" />
        </a-form-item>
        <a-form-item v-if="current?.key === 'attach'" :label="tf('Pt_Internal_Only', 'Internal only (hidden from the customer)')">
          <a-switch v-model:checked="actionForm.is_internal" />
        </a-form-item>
        <a-form-item :label="$t('Attachments')">
          <a-upload :file-list="actionFiles" :before-upload="() => false" multiple @change="({ fileList }) => (actionFiles = fileList)">
            <a-button><template #icon><UploadOutlined /></template>{{ $t('Choose_a_file') }}</a-button>
          </a-upload>
        </a-form-item>
        <a-alert v-if="current?.key === 'respond'" type="info" show-icon :message="tf('Pt_Respond_Help', 'Responses are visible to the person who submitted the request (also in the customer portal).')" />
        <a-alert v-else-if="current?.key === 'note'" type="info" show-icon :message="tf('Pt_Note_Help', 'Internal notes are visible to staff only.')" />
      </a-form>
    </a-modal>

    <!-- Edit modal -->
    <a-modal v-model:open="editOpen" :title="$t('Edit')" :confirm-loading="saving" :ok-text="$t('submit')" :cancel-text="$t('Delete_cancelButtonText')" @ok="saveEdit">
      <a-form layout="vertical" style="margin-top: 8px">
        <a-form-item :label="tf('Pt_Type', 'Type')"><a-select v-model:value="edit.type" :options="typeOptions" /></a-form-item>
        <a-form-item :label="tf('Pt_Subject', 'Subject')"><a-input v-model:value="edit.subject" :maxlength="255" /></a-form-item>
        <a-form-item :label="tf('Pt_Details', 'Details')"><a-textarea v-model:value="edit.body" :rows="5" /></a-form-item>
        <a-row :gutter="16">
          <a-col :span="12"><a-form-item :label="tf('Pt_Priority', 'Priority')"><a-select v-model:value="edit.priority" :options="priorityOptions" /></a-form-item></a-col>
          <a-col :span="12"><a-form-item :label="tf('Pt_Due_Date', 'Due date')"><a-date-picker v-model:value="edit.due_date" style="width: 100%" value-format="YYYY-MM-DD" /></a-form-item></a-col>
        </a-row>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Protocol request details — GET protocol/requests/{id} → {request:{…, events,
 * attachments, can_act, can_manage}}; every desk action is POST
 * protocol/requests/{id}/action (multipart: action, note, to_desk_id,
 * assigned_to_user_id, is_internal, attachments[]).
 */
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { message, Modal } from 'ant-design-vue';
import { ArrowLeftOutlined, EditOutlined, PaperClipOutlined, DeleteOutlined, UploadOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import { useAuthStore } from '../../stores/auth';
import http from '../../lib/http';
import { TYPES, PRIORITIES, STATUS_COLOR, PRIORITY_COLOR, TYPE_COLOR, EVENT_COLOR, ACTIONS, actionsFor, makeLabel, fileSize } from './protocolShared';

const { t } = useI18n();
const tf = (key, fallback) => { const out = t(key); return out === key ? fallback : out; };
const label = makeLabel(t);
const route = useRoute();
const auth = useAuthStore();
const meId = computed(() => auth.user?.id);

const loading = ref(false);
const req = ref({});
const meta = ref({ desks: [], users: [] });

const typeOptions = computed(() => TYPES.map(x => ({ value: x, label: label(x) })));
const priorityOptions = computed(() => PRIORITIES.map(x => ({ value: x, label: label(x) })));
const deskOptions = computed(() => (meta.value.desks || []).filter(d => d.is_active && d.id !== req.value.current_desk_id).map(d => ({ value: d.id, label: d.name })));
const userOptions = computed(() => {
  // Officers of the current desk first, then everyone (managers may pick anyone).
  const desk = (meta.value.desks || []).find(d => d.id === req.value.current_desk_id);
  const ids = new Set((desk?.user_ids || []).map(Number));
  return (meta.value.users || [])
    .map(u => ({ value: u.id, label: ids.has(Number(u.id)) ? `${u.username} · ${desk?.name || ''}` : u.username }))
    .sort((a, b) => (ids.has(Number(b.value)) ? 1 : 0) - (ids.has(Number(a.value)) ? 1 : 0));
});

const availableActions = computed(() => {
  const keys = actionsFor(req.value.status);
  return ACTIONS.filter(a => keys.includes(a.key) && (a.key !== 'return' || req.value.previous_desk_id));
});

async function load() {
  loading.value = true;
  try {
    const [d, m] = await Promise.all([http.get(`protocol/requests/${route.params.id}`), http.get('protocol/meta').catch(() => null)]);
    req.value = d?.request || {};
    if (m) meta.value = m;
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    loading.value = false;
  }
}

/* ------------------------------------------------------------- actions */
const actionOpen = ref(false);
const acting = ref(null);
const current = ref(null);
const actionFiles = ref([]);
const actionForm = ref({ note: '', to_desk_id: undefined, assigned_to_user_id: undefined, is_internal: false });

function startAction(a) {
  current.value = a;
  actionForm.value = { note: '', to_desk_id: undefined, assigned_to_user_id: req.value.assigned_to_user_id || undefined, is_internal: false };
  actionFiles.value = [];
  // Simple one-click actions skip the modal.
  if (['receive', 'acknowledge', 'reopen'].includes(a.key)) {
    runAction();
    return;
  }
  actionOpen.value = true;
}

async function runAction() {
  const a = current.value;
  if (!a) return;
  if (a.needsNote && !String(actionForm.value.note || '').trim()) { message.warning(tf('Pt_Note_Required', 'A note is required.')); return; }
  if (a.needsFiles && !actionFiles.value.length) { message.warning(tf('Pt_Files_Required', 'Choose at least one file.')); return; }
  acting.value = a.key;
  try {
    const fd = new FormData();
    fd.append('action', a.key);
    if (actionForm.value.note) fd.append('note', actionForm.value.note);
    if (actionForm.value.to_desk_id) fd.append('to_desk_id', actionForm.value.to_desk_id);
    if (actionForm.value.assigned_to_user_id) fd.append('assigned_to_user_id', actionForm.value.assigned_to_user_id);
    fd.append('is_internal', actionForm.value.is_internal ? 1 : 0);
    actionFiles.value.forEach(f => { if (f.originFileObj) fd.append('attachments[]', f.originFileObj); });
    await http.postForm(`protocol/requests/${route.params.id}/action`, fd);
    message.success(t('Successfully_Updated'));
    actionOpen.value = false;
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    acting.value = null;
  }
}

function removeAttachment(a) {
  Modal.confirm({
    title: t('Delete_Title'),
    content: a.file_name,
    okType: 'danger',
    async onOk() {
      try { await http.delete(`protocol/attachments/${a.id}`); await load(); } catch (e) { message.error(t('InvalidData')); }
    },
  });
}

/* ------------------------------------------------------------- edit */
const editOpen = ref(false);
const saving = ref(false);
const edit = ref({});
function openEdit() {
  edit.value = { type: req.value.type, subject: req.value.subject, body: req.value.body || '', priority: req.value.priority, due_date: req.value.due_date || null };
  editOpen.value = true;
}
async function saveEdit() {
  saving.value = true;
  try {
    await http.put(`protocol/requests/${route.params.id}`, { ...edit.value, due_date: edit.value.due_date || '' });
    message.success(t('Successfully_Updated'));
    editOpen.value = false;
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

watch(() => route.params.id, () => { if (route.name === 'protocol-request-details') load(); });
onMounted(load);
</script>

<style scoped>
.head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.subject { font-size: 18px; font-weight: 600; margin: 8px 0 0; }
.body { white-space: pre-wrap; margin-top: 12px; line-height: 1.6; }
.muted { color: rgba(var(--ink-rgb), 0.45); }
.small { font-size: 12px; }
.file { display: inline-flex; align-items: center; gap: 4px; }
.ev-head { font-size: 14px; }
.ev-note { white-space: pre-wrap; margin-top: 4px; padding: 8px 10px; border-radius: 8px; background: rgba(var(--ink-rgb), 0.04); }
</style>
