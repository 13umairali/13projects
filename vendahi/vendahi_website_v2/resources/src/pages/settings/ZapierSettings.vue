<template>
  <div class="page">
    <PageHeader title="Zapier" :breadcrumb="[$t('Settings'), 'Zapier']" />

    <div v-if="isLoading" style="display: flex; justify-content: center; padding: 96px 0">
      <a-spin size="large" />
    </div>

    <a-card v-else>
      <a-tabs>
        <!-- ============================== Connection ============================== -->
        <a-tab-pane key="connection" :tab="$t('Connection')">
          <a-alert
            type="info"
            show-icon
            style="max-width: 720px; margin-bottom: 16px"
            :message="$t('Zapier_Connect_Hint')"
            :description="connectionUrl"
          />

          <a-descriptions :column="1" bordered size="small" style="max-width: 720px; margin-bottom: 16px">
            <a-descriptions-item :label="$t('Status')">
              <a-tag :color="state.enabled ? 'success' : 'default'">
                {{ state.enabled ? $t('Enabled') : $t('Disabled') }}
              </a-tag>
            </a-descriptions-item>
            <a-descriptions-item :label="$t('API_Key')">
              <template v-if="state.has_key">
                <code>zap_…{{ state.api_key_last4 }}</code>
                <span v-if="state.key_generated_at" style="color: rgba(var(--ink-rgb), .45); margin-left: 8px">
                  {{ $t('Generated') }} {{ date(state.key_generated_at) }}
                </span>
              </template>
              <span v-else style="color: rgba(var(--ink-rgb), .45)">{{ $t('No_Key_Yet') }}</span>
            </a-descriptions-item>
            <a-descriptions-item :label="$t('Last_Used')">
              {{ state.last_used_at ? date(state.last_used_at) : '—' }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('Acting_User')">
              <a-select
                v-model:value="state.user_id"
                style="min-width: 260px"
                :options="userOptions"
                :placeholder="$t('Acting_User')"
                @change="saveSettings"
              />
              <div style="color: rgba(var(--ink-rgb), .45); font-size: 12px; margin-top: 4px">
                {{ $t('Zapier_Acting_User_Hint') }}
              </div>
            </a-descriptions-item>
          </a-descriptions>

          <a-space wrap>
            <a-switch v-model:checked="state.enabled" :disabled="!state.has_key" @change="saveSettings" />
            <span>{{ $t('Enable_Zapier') }}</span>
            <a-divider type="vertical" />
            <a-button type="primary" :loading="busy" @click="generateKey">
              {{ state.has_key ? $t('Rotate_Key') : $t('Generate_Key') }}
            </a-button>
            <a-popconfirm v-if="state.has_key" :title="$t('Zapier_Revoke_Warning')" :ok-text="$t('Yes')" :cancel-text="$t('No')" @confirm="revokeKey">
              <a-button danger :loading="busy">{{ $t('Revoke_Key') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Triggers ============================== -->
        <a-tab-pane key="triggers" :tab="$t('Triggers')">
          <p style="max-width: 720px; color: rgba(var(--ink-rgb), .65)">{{ $t('Zapier_Triggers_Hint') }}</p>
          <a-space wrap style="margin-bottom: 20px">
            <a-tag v-for="ev in state.trigger_events" :key="ev" color="purple">{{ ev }}</a-tag>
          </a-space>

          <a-table
            :data-source="state.hooks"
            :columns="hookColumns"
            row-key="id"
            size="small"
            :pagination="false"
            :locale="{ emptyText: $t('Zapier_No_Hooks') }"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'events'">
                <a-tag v-for="ev in record.events" :key="ev">{{ ev }}</a-tag>
              </template>
              <template v-else-if="column.key === 'is_active'">
                <a-tag :color="record.is_active ? 'success' : 'default'">
                  {{ record.is_active ? $t('Enabled') : $t('Disabled') }}
                </a-tag>
              </template>
              <template v-else-if="column.key === 'last_fired_at'">
                {{ record.last_fired_at ? date(record.last_fired_at) : '—' }}
              </template>
            </template>
          </a-table>
        </a-tab-pane>

        <!-- ============================== Deliveries ============================== -->
        <a-tab-pane key="deliveries" :tab="$t('Deliveries')">
          <a-table
            :data-source="state.deliveries"
            :columns="deliveryColumns"
            row-key="id"
            size="small"
            :pagination="false"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'status'">
                <a-tag :color="record.status === 'success' ? 'success' : record.status === 'failed' ? 'error' : 'processing'">
                  {{ record.status }}
                </a-tag>
              </template>
              <template v-else-if="column.key === 'created_at'">
                {{ date(record.created_at) }}
              </template>
            </template>
          </a-table>
        </a-tab-pane>
      </a-tabs>
    </a-card>

    <!-- Generated key — shown exactly once -->
    <a-modal v-model:open="keyModalOpen" :title="$t('API_Key')" :footer="null">
      <a-alert type="warning" show-icon :message="$t('Zapier_Key_Shown_Once')" style="margin-bottom: 12px" />
      <a-input-group compact>
        <a-input :value="generatedKey" readonly style="width: calc(100% - 90px)" />
        <a-button type="primary" style="width: 90px" @click="copyKey">{{ $t('Copy') }}</a-button>
      </a-input-group>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Zapier integration settings: API key lifecycle (generated once, stored
 * hashed), acting user, enable switch, plus a read-only view of Zapier's
 * REST-hook subscriptions and their recent deliveries.
 */
import { ref, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '../../components/PageHeader.vue';
import { useFormat } from '../../composables/useFormat';
import http from '../../lib/http';

const { t } = useI18n();
const { date } = useFormat();

const isLoading = ref(true);
const busy = ref(false);
const state = ref({ enabled: false, has_key: false, users: [], hooks: [], deliveries: [], trigger_events: [] });
const keyModalOpen = ref(false);
const generatedKey = ref('');

const connectionUrl = computed(() => window.location.origin + '/api/zapier');
const userOptions = computed(() =>
  (state.value.users || []).map(u => ({ value: u.id, label: u.username || u.email }))
);

const hookColumns = [
  { title: t('Name'), dataIndex: 'name' },
  { title: t('Events'), key: 'events' },
  { title: 'URL', dataIndex: 'url', ellipsis: true },
  { title: t('Status'), key: 'is_active', width: 110 },
  { title: t('Last_Fired'), key: 'last_fired_at', width: 160 },
];

const deliveryColumns = [
  { title: 'ID', dataIndex: 'id', width: 70 },
  { title: t('Events'), dataIndex: 'event' },
  { title: t('Status'), key: 'status', width: 110 },
  { title: t('Response'), dataIndex: 'response_code', width: 100 },
  { title: t('Date'), key: 'created_at', width: 160 },
];

async function load() {
  isLoading.value = true;
  try {
    state.value = await http.get('zapier/settings');
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    isLoading.value = false;
  }
}

async function saveSettings() {
  try {
    await http.put('zapier/settings', {
      enabled: !!state.value.enabled,
      user_id: state.value.user_id || null,
    });
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
    await load();
  }
}

async function generateKey() {
  busy.value = true;
  try {
    const res = await http.post('zapier/settings/generate-key');
    generatedKey.value = res.api_key;
    keyModalOpen.value = true;
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    busy.value = false;
  }
}

async function revokeKey() {
  busy.value = true;
  try {
    await http.post('zapier/settings/revoke-key');
    message.success(t('Successfully_Updated'));
    await load();
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    busy.value = false;
  }
}

async function copyKey() {
  try {
    await navigator.clipboard.writeText(generatedKey.value);
    message.success(t('Copied'));
  } catch (e) {
    message.error(t('InvalidData'));
  }
}

onMounted(load);
</script>
