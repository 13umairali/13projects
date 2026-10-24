<template>
  <div class="page">
    <PageHeader :title="tf('Pt_Desks', 'Desks')" :breadcrumb="[tf('Pt_Protocol', 'Protocol'), tf('Pt_Desks', 'Desks')]">
      <template #actions>
        <a-space wrap>
          <a-button @click="$router.push({ name: 'protocol-requests' })">
            <template #icon><ArrowLeftOutlined /></template>
            {{ tf('Pt_Requests', 'Requests') }}
          </a-button>
          <a-button type="primary" @click="openCreate">
            <template #icon><PlusOutlined /></template>
            {{ $t('Add') }}
          </a-button>
        </a-space>
      </template>
    </PageHeader>

    <a-alert
      type="info" show-icon style="margin-bottom: 16px"
      :message="tf('Pt_Desks_Intro', 'Requests travel from desk to desk in the order below (e.g. Secretary → Administrator → CEO). New requests land on the first active desk; the officers attached to a desk receive, acknowledge, respond, forward, return, approve or reject what is on it.')"
    />

    <a-card size="small" :body-style="{ padding: 0 }">
      <a-table :columns="columns" :data-source="desks" :loading="loading" row-key="id" :pagination="false" size="middle">
        <template #bodyCell="{ column, record, index }">
          <template v-if="column.key === 'order'">
            <a-space>
              <span class="order-no">{{ index + 1 }}</span>
              <a-button type="text" size="small" :disabled="index === 0" @click="move(index, -1)"><template #icon><ArrowUpOutlined /></template></a-button>
              <a-button type="text" size="small" :disabled="index === desks.length - 1" @click="move(index, 1)"><template #icon><ArrowDownOutlined /></template></a-button>
            </a-space>
          </template>
          <template v-else-if="column.key === 'name'">
            <b>{{ record.name }}</b>
            <div v-if="record.description" class="muted small">{{ record.description }}</div>
          </template>
          <template v-else-if="column.key === 'users'">
            <a-space wrap size="small">
              <a-tag v-for="u in record.users" :key="u">{{ u }}</a-tag>
              <span v-if="!record.users.length" class="muted">{{ tf('Pt_No_Officers', 'No officers yet') }}</span>
            </a-space>
          </template>
          <template v-else-if="column.key === 'active_count'"><a-badge :count="record.active_count" :number-style="{ backgroundColor: record.active_count ? '#1677ff' : '#d9d9d9' }" show-zero /></template>
          <template v-else-if="column.key === 'is_active'"><a-tag :color="record.is_active ? 'success' : 'default'">{{ record.is_active ? $t('Active') : $t('Inactive') }}</a-tag></template>
          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button type="text" size="small" @click="openEdit(record)"><template #icon><EditOutlined style="color: #52c41a" /></template></a-button>
              <a-popconfirm :title="$t('Delete_Title')" :ok-text="$t('Delete_confirmButtonText')" :cancel-text="$t('Delete_cancelButtonText')" @confirm="remove(record)">
                <a-button type="text" size="small" danger><template #icon><DeleteOutlined /></template></a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <a-modal v-model:open="modalOpen" :title="editMode ? $t('Edit') : $t('Add')" :confirm-loading="submitting" :ok-text="$t('submit')" :cancel-text="$t('Delete_cancelButtonText')" @ok="submit">
      <a-form ref="formRef" :model="form" :rules="rules" layout="vertical" style="margin-top: 8px">
        <a-form-item :label="$t('Name') + ' *'" name="name">
          <a-input v-model:value="form.name" :maxlength="150" placeholder="Secretary desk" />
        </a-form-item>
        <a-form-item :label="$t('Description')">
          <a-textarea v-model:value="form.description" :rows="2" :maxlength="500" />
        </a-form-item>
        <a-form-item :label="tf('Pt_Officers', 'Officers (users on this desk)')">
          <a-select v-model:value="form.user_ids" mode="multiple" show-search option-filter-prop="label" :options="userOptions" />
        </a-form-item>
        <a-form-item :label="$t('Active')">
          <a-switch v-model:checked="form.is_active" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Protocol desks — GET protocol/desks {desks}; POST/PUT/DELETE protocol/desks
 * {name, description, sort_order, is_active, user_ids[]}. Chain order = sort_order.
 */
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { message } from 'ant-design-vue';
import { PlusOutlined, EditOutlined, DeleteOutlined, ArrowLeftOutlined, ArrowUpOutlined, ArrowDownOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';

const { t } = useI18n();
const tf = (key, fallback) => { const out = t(key); return out === key ? fallback : out; };

const loading = ref(false);
const desks = ref([]);
const users = ref([]);
const userOptions = computed(() => users.value.map(u => ({ value: u.id, label: u.username })));

const columns = computed(() => [
  { title: tf('Pt_Order', 'Order'), key: 'order', width: 130 },
  { title: $tName(), key: 'name' },
  { title: tf('Pt_Officers', 'Officers'), key: 'users' },
  { title: tf('Pt_Active_Requests', 'Active requests'), key: 'active_count', width: 130, align: 'center' },
  { title: t('Status'), key: 'is_active', width: 100 },
  { title: t('Action'), key: 'actions', width: 110, align: 'center' },
]);
function $tName() { return t('Name'); }

async function load() {
  loading.value = true;
  try {
    const [d, m] = await Promise.all([http.get('protocol/desks'), http.get('protocol/meta')]);
    desks.value = d.desks || [];
    users.value = m.users || [];
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    loading.value = false;
  }
}

async function move(index, delta) {
  const list = [...desks.value];
  const j = index + delta;
  if (j < 0 || j >= list.length) return;
  [list[index], list[j]] = [list[j], list[index]];
  // Persist the new order as sort_order 1..n.
  try {
    await Promise.all(list.map((d, i) => http.put(`protocol/desks/${d.id}`, { name: d.name, description: d.description, sort_order: i + 1, is_active: d.is_active ? 1 : 0 })));
    await load();
  } catch (e) {
    message.error(t('InvalidData'));
  }
}

const modalOpen = ref(false);
const editMode = ref(false);
const submitting = ref(false);
const formRef = ref();
const form = ref({ id: null, name: '', description: '', user_ids: [], is_active: true });
const rules = computed(() => ({ name: [{ required: true, message: t('Field_is_required') }] }));

function openCreate() {
  editMode.value = false;
  form.value = { id: null, name: '', description: '', user_ids: [], is_active: true };
  modalOpen.value = true;
}
function openEdit(record) {
  editMode.value = true;
  form.value = { id: record.id, name: record.name, description: record.description || '', user_ids: [...(record.user_ids || [])], is_active: !!record.is_active };
  modalOpen.value = true;
}
async function submit() {
  try { await formRef.value.validate(); } catch (e) { message.error(t('Please_fill_the_form_correctly')); return; }
  submitting.value = true;
  try {
    const body = { name: form.value.name, description: form.value.description, user_ids: form.value.user_ids, is_active: form.value.is_active ? 1 : 0 };
    if (editMode.value) await http.put(`protocol/desks/${form.value.id}`, body);
    else await http.post('protocol/desks', body);
    message.success(editMode.value ? t('Updated_in_successfully') : t('Created_in_successfully'));
    modalOpen.value = false;
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    submitting.value = false;
  }
}
async function remove(record) {
  try {
    await http.delete(`protocol/desks/${record.id}`);
    message.success(t('Deleted_in_successfully'));
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  }
}

onMounted(load);
</script>

<style scoped>
.muted { color: rgba(var(--ink-rgb), 0.45); }
.small { font-size: 12px; }
.order-no { display: inline-flex; width: 24px; height: 24px; border-radius: 50%; align-items: center; justify-content: center; background: rgba(var(--ink-rgb), 0.06); font-weight: 600; font-size: 12px; }
</style>
