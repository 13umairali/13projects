<template>
  <div class="page">
    <PageHeader title="FBR Digital Invoicing (Pakistan)" :breadcrumb="[$t('Settings'), 'FBR E-Invoicing']" />

    <a-skeleton v-if="loading" active :paragraph="{ rows: 10 }" />
    <a-card v-else size="small">
      <a-tabs v-model:activeKey="tab" @change="onTab">
        <!-- ======================= Configuration ======================= -->
        <a-tab-pane key="config" :tab="tf('Configuration', 'Configuration')">
          <a-form layout="vertical">
            <a-row :gutter="12">
              <a-col :xs="24" :md="8">
                <a-form-item :label="tf('Enable_FBR', 'Enable FBR Digital Invoicing')">
                  <a-switch v-model:checked="form.enabled" /> <span style="margin-left: 8px">{{ form.enabled ? tf('Enabled', 'Enabled') : tf('Disabled', 'Disabled') }}</span>
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="8">
                <a-form-item :label="tf('FBR_Auto_Submit', 'Auto-submit new sales')">
                  <a-switch v-model:checked="form.auto_submit" /> <span style="margin-left: 8px">{{ form.auto_submit ? tf('Automatic', 'Automatic') : tf('Manual', 'Manual') }}</span>
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="8">
                <a-form-item :label="tf('Environment', 'Environment')" :extra="tf('FBR_Environment_Hint', 'Sandbox uses the *_sb endpoints and requires a test scenario; production issues real FBR invoice numbers.')">
                  <a-select v-model:value="form.environment" :options="toOptions(environmentOptions)" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-divider orientation="left">{{ tf('Security_Token', 'Security Token') }}</a-divider>
            <a-row :gutter="12">
              <a-col :xs="24" :md="12">
                <a-form-item
                  :label="tf('Security_Token_Bearer', 'Security Token (Bearer)')"
                  :extra="form.has_token ? `A token is saved (••••${form.token_last4 || ''}). Leave blank to keep it.` : 'Obtain the token from the PRAL / IRIS e-invoicing portal.'"
                >
                  <a-input-password v-model:value="bearerToken" autocomplete="new-password" placeholder="Paste the token from the PRAL / IRIS e-invoicing portal" />
                </a-form-item>
              </a-col>
              <a-col v-if="form.environment === 'sandbox'" :xs="24" :md="12">
                <a-form-item :label="tf('Sandbox_Scenario', 'Sandbox Scenario')" :extra="tf('FBR_Scenario_Hint', 'Test scenario assigned to you by FBR — required on the sandbox environment.')">
                  <a-select v-model:value="form.scenario_id" :options="toOptions(scenarioOptions)" allow-clear show-search option-filter-prop="label" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-divider orientation="left">{{ tf('Seller_Profile', 'Seller Profile') }}</a-divider>
            <a-row :gutter="12">
              <a-col :xs="24" :md="8"><a-form-item label="NTN / CNIC"><a-input v-model:value="form.seller_ntn_cnic" /></a-form-item></a-col>
              <a-col :xs="24" :md="8"><a-form-item :label="tf('Business_Name', 'Business Name')"><a-input v-model:value="form.seller_business_name" :placeholder="company.name || ''" /></a-form-item></a-col>
              <a-col :xs="24" :md="8">
                <a-form-item :label="tf('Province', 'Province')">
                  <a-select v-model:value="form.seller_province" :options="toOptions(provinceOptions)" allow-clear show-search option-filter-prop="label" />
                </a-form-item>
              </a-col>
              <a-col :span="24"><a-form-item :label="$t('Address')"><a-input v-model:value="form.seller_address" :placeholder="company.address || ''" /></a-form-item></a-col>
            </a-row>

            <a-divider orientation="left">{{ tf('Item_Defaults', 'Item Defaults') }}</a-divider>
            <a-row :gutter="12">
              <a-col :xs="24" :md="8">
                <a-form-item :label="tf('Default_HS_Code', 'Default HS Code')" :extra="tf('Default_HS_Code_Hint', 'Used when a product has no HS code of its own — set per-product HS codes on the product form.')">
                  <a-input v-model:value="form.default_hs_code" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="8"><a-form-item :label="tf('Default_UoM', 'Default UoM')"><a-input v-model:value="form.default_uom" /></a-form-item></a-col>
              <a-col :xs="24" :md="8"><a-form-item :label="tf('Default_Sale_Type', 'Default Sale Type')"><a-input v-model:value="form.default_sale_type" /></a-form-item></a-col>
            </a-row>

            <a-space>
              <a-button type="primary" :loading="busy" @click="save">{{ $t('Save') }}</a-button>
              <a-button :loading="busy" @click="testConnection">{{ tf('Test_Connection', 'Test Connection') }}</a-button>
            </a-space>
          </a-form>
        </a-tab-pane>

        <!-- ======================= Documents ======================= -->
        <a-tab-pane key="documents" :tab="tf('Documents', 'Documents')">
          <a-space style="margin-bottom: 12px">
            <a-select v-model:value="docStatus" style="width: 180px" :options="docStatusOptions" @change="loadDocuments(1)" />
            <a-button @click="loadDocuments(docPage)"><template #icon><ReloadOutlined /></template>{{ tf('Refresh', 'Refresh') }}</a-button>
          </a-space>
          <a-table
            size="small" :columns="docColumns" :data-source="documents.data" row-key="id" :loading="docsLoading"
            :pagination="{ current: documents.current_page || 1, pageSize: 20, total: documents.total || 0, showSizeChanger: false }"
            :scroll="{ x: 900 }"
            @change="p => loadDocuments(p.current)"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'environment'">
                <a-tag :color="record.environment === 'production' ? 'error' : 'blue'">{{ record.environment }}</a-tag>
              </template>
              <template v-else-if="column.key === 'status'">
                <a-tag :color="docColor(record.status)">{{ record.status }}</a-tag>
              </template>
              <template v-else-if="column.key === 'error'">
                <a-tooltip v-if="record.error" :title="record.error"><span style="color: #ff4d4f">{{ truncate(record.error, 60) }}</span></a-tooltip>
                <span v-else>—</span>
              </template>
              <template v-else-if="column.key === 'actions'">
                <a-button v-if="record.status !== 'valid'" size="small" danger :loading="busy" @click="retry(record)">{{ tf('Retry', 'Retry') }}</a-button>
              </template>
            </template>
          </a-table>
        </a-tab-pane>

        <!-- ======================= Logs ======================= -->
        <a-tab-pane key="logs" :tab="tf('Logs', 'Logs')">
          <a-button style="margin-bottom: 12px" @click="loadLogs"><template #icon><ReloadOutlined /></template>{{ tf('Refresh', 'Refresh') }}</a-button>
          <a-table size="small" :columns="logColumns" :data-source="logs" row-key="id" :loading="logsLoading" :pagination="false" :scroll="{ x: 900 }">
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'http_status'">
                <a-tag :color="record.http_status >= 200 && record.http_status < 300 ? 'success' : 'error'">{{ record.http_status }}</a-tag>
              </template>
              <template v-else-if="column.key === 'response_body'">
                <span class="mono" style="cursor: pointer; word-break: break-all" @click="toggle('log-' + record.id)">
                  {{ expanded['log-' + record.id] ? record.response_body : truncate(record.response_body, 120) }}
                </span>
              </template>
            </template>
          </a-table>
        </a-tab-pane>

        <!-- ======================= Guide ======================= -->
        <a-tab-pane key="guide" :tab="tf('Guide', 'Guide')">
          <a-typography>
            <h3>What is FBR Digital Invoicing?</h3>
            <p>
              Under SRO 69(I)/2025, the Federal Board of Revenue (Pakistan) requires integrated businesses to report
              sales invoices electronically in real time through the PRAL Digital Invoicing (DI) API. Each accepted sale
              receives an official FBR invoice number, and receipts must display that number together with a
              verification QR code.
            </p>
            <h3>Steps to go live</h3>
            <ol>
              <li>Register on the FBR IRIS e-invoicing portal and obtain a <strong>sandbox</strong> security token.</li>
              <li>Configure your seller profile on the Configuration tab (NTN/CNIC, business name, province, address), paste the token, and enable the integration with the environment set to <strong>sandbox</strong>.</li>
              <li>Make test sales covering the scenario(s) FBR assigned to you (pick the scenario in the Sandbox Scenario field). Check the Documents tab to confirm they come back <a-tag color="success">valid</a-tag>.</li>
              <li>Once FBR approves your sandbox testing, obtain the <strong>production</strong> token from the portal, paste it here, and switch the environment to <strong>production</strong>.</li>
              <li>Receipts automatically show the FBR invoice number and QR code once a sale is accepted — no extra steps needed at the point of sale.</li>
            </ol>
            <h3>Good to know</h3>
            <ul>
              <li>Set HS codes per product on the product form (field "HS Code"). The Default HS Code configured here is only used for products without one.</li>
              <li>Unregistered walk-in customers are reported to FBR as "Unregistered".</li>
              <li>Registered buyers need their NTN entered in the customer's Tax Number field so they are reported as registered.</li>
              <li>Failed or invalid submissions can be retried from the Documents tab after fixing the cause; the Logs tab shows the raw API exchanges for troubleshooting.</li>
            </ul>
          </a-typography>
        </a-tab-pane>
      </a-tabs>
    </a-card>
  </div>
</template>

<script setup>
/**
 * SaaS FBR (Pakistan) e-invoicing settings — port of legacy settings/fbr_settings.vue.
 *   GET  fbr/settings → {settings, company, environments, scenarios, provinces}
 *   POST fbr/settings (+ bearer_token when re-entered)   POST fbr/test-connection
 *   GET  fbr/documents?limit&page&status (Laravel paginator)   POST fbr/sales/{id}/submit
 *   GET  fbr/logs?limit
 * Option lists arrive either as [{value,text}] (Bootstrap style) or plain
 * strings; toOptions() normalizes both for a-select.
 */
import { ref, reactive, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { ReloadOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const loading = ref(true);
const busy = ref(false);
const tab = ref('config');
const bearerToken = ref('');
const company = ref({ name: '', address: '' });
const form = ref({
  enabled: false, auto_submit: true, environment: 'sandbox', has_token: false, token_last4: null,
  seller_ntn_cnic: '', seller_business_name: '', seller_province: '', seller_address: '',
  scenario_id: '', default_hs_code: '', default_uom: '', default_sale_type: '',
});
const environmentOptions = ref([]);
const scenarioOptions = ref([]);
const provinceOptions = ref([]);

const toOptions = list => (list || []).map(o =>
  (typeof o === 'object' ? { value: o.value, label: o.text ?? o.label ?? String(o.value) } : { value: o, label: String(o) }));

// documents
const documents = ref({ data: [], total: 0, last_page: 1, current_page: 1 });
const docPage = ref(1);
const docStatus = ref('');
const docsLoading = ref(false);
const docStatusOptions = [
  { value: '', label: tf('All_statuses', 'All statuses') },
  { value: 'valid', label: 'Valid' }, { value: 'invalid', label: 'Invalid' },
  { value: 'error', label: 'Error' }, { value: 'pending', label: 'Pending' },
];
const docColumns = computed(() => [
  { title: '#', dataIndex: 'id', key: 'id', width: 70 },
  { title: 'Ref', dataIndex: 'invoice_ref', key: 'invoice_ref' },
  { title: 'FBR Invoice No', dataIndex: 'fbr_invoice_number', key: 'fbr_invoice_number' },
  { title: tf('Environment', 'Environment'), key: 'environment', width: 120 },
  { title: t('Status'), key: 'status', width: 100 },
  { title: tf('Error', 'Error'), key: 'error' },
  { title: tf('Submitted', 'Submitted'), dataIndex: 'submitted_at', key: 'submitted_at', width: 160 },
  { title: '', key: 'actions', width: 90 },
]);

// logs
const logs = ref([]);
const logsLoading = ref(false);
const expanded = reactive({});
const logColumns = computed(() => [
  { title: t('date'), dataIndex: 'created_at', key: 'created_at', width: 160 },
  { title: t('Action'), dataIndex: 'action', key: 'action', width: 140 },
  { title: 'HTTP', key: 'http_status', width: 80 },
  { title: 'Endpoint', dataIndex: 'endpoint', key: 'endpoint' },
  { title: tf('Response', 'Response'), key: 'response_body' },
]);

function toggle(k) { expanded[k] = !expanded[k]; }
function truncate(text, len) { const s = String(text || ''); return s.length > len ? s.slice(0, len) + '…' : s; }
function docColor(s) { return { valid: 'success', invalid: 'error', error: 'warning', pending: 'default' }[s] || 'default'; }

async function load() {
  try {
    const data = await http.get('fbr/settings');
    Object.assign(form.value, data.settings || {});
    company.value = data.company || company.value;
    environmentOptions.value = data.environments || [];
    scenarioOptions.value = data.scenarios || [];
    provinceOptions.value = data.provinces || [];
  } catch (e) {
    message.error(e?.data?.message || 'Failed loading FBR settings');
  } finally {
    loading.value = false;
  }
}
async function save() {
  busy.value = true;
  try {
    const payload = { ...form.value };
    if (bearerToken.value) payload.bearer_token = bearerToken.value;
    const data = await http.post('fbr/settings', payload);
    Object.assign(form.value, data.settings || {});
    bearerToken.value = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || 'Save failed');
  } finally {
    busy.value = false;
  }
}
async function testConnection() {
  busy.value = true;
  try {
    const data = await http.post('fbr/test-connection', {});
    (data.success ? message.success : message.warning)(data.message || (data.success ? 'Connection OK' : 'Connection failed'));
  } catch (e) {
    message.error(e?.data?.message || 'Connection test failed');
  } finally {
    busy.value = false;
  }
}
async function loadDocuments(page) {
  docPage.value = Math.max(1, page || 1);
  docsLoading.value = true;
  try {
    documents.value = await http.get('fbr/documents', { limit: 20, page: docPage.value, status: docStatus.value || undefined });
  } catch (e) {
    message.error('Failed loading documents');
  } finally {
    docsLoading.value = false;
  }
}
async function retry(doc) {
  busy.value = true;
  try {
    const data = await http.post(`fbr/sales/${doc.source_id}/submit`, {});
    if (data.success) message.success('Status: ' + data.status + (data.fbr_invoice_number ? ' — ' + data.fbr_invoice_number : ''));
    else message.warning(data.error || ('Status: ' + data.status));
    loadDocuments(docPage.value);
  } catch (e) {
    message.error(e?.data?.message || 'Retry failed');
  } finally {
    busy.value = false;
  }
}
async function loadLogs() {
  logsLoading.value = true;
  try {
    logs.value = (await http.get('fbr/logs', { limit: 50 })) || [];
  } catch (e) {
    message.error('Failed loading logs');
  } finally {
    logsLoading.value = false;
  }
}
function onTab(key) {
  if (key === 'documents' && !documents.value.data.length) loadDocuments(1);
  if (key === 'logs' && !logs.value.length) loadLogs();
}

onMounted(load);
</script>

<style scoped>
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; }
</style>
