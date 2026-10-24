<template>
  <div class="page">
    <PageHeader :title="tf('WhatsApp_Settings', 'WhatsApp Settings')" :breadcrumb="['WhatsApp', tf('Settings', 'Settings')]" />

    <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />
    <template v-else>
      <a-alert
        v-if="!globallyEnabled" type="warning" show-icon style="margin-bottom: 16px"
        :message="tf('WhatsApp_Disabled_By_Platform', 'WhatsApp is currently disabled by the platform administrator. You can configure your account, but messages will not be sent until it is enabled.')"
      />

      <a-row :gutter="16">
        <a-col :xs="24" :lg="16">
          <a-card size="small" :title="tf('Connection', 'Connection')" style="margin-bottom: 16px">
            <a-form layout="vertical">
              <a-form-item :label="tf('Provider', 'Provider')">
                <a-input value="Meta Cloud API" disabled />
              </a-form-item>
              <a-row :gutter="12">
                <a-col :xs="24" :md="12">
                  <a-form-item :label="tf('Phone_Number_ID', 'Phone Number ID')">
                    <a-input v-model:value="form.phone_number_id" placeholder="e.g. 123456789012345" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :md="12">
                  <a-form-item :label="tf('WhatsApp_Business_Account_ID', 'WhatsApp Business Account ID')">
                    <a-input v-model:value="form.business_account_id" :placeholder="tf('Optional', 'Optional')" />
                  </a-form-item>
                </a-col>
              </a-row>
              <a-form-item :label="tf('Access_Token', 'Access Token')">
                <a-input-password
                  v-model:value="form.access_token" autocomplete="new-password"
                  :placeholder="status.has_access_token ? '••••••••  (leave blank to keep)' : 'Permanent access token'"
                />
              </a-form-item>
              <a-row :gutter="12">
                <a-col :xs="24" :md="12">
                  <a-form-item :label="tf('App_Secret', 'App Secret')">
                    <a-input-password
                      v-model:value="form.app_secret" autocomplete="new-password"
                      :placeholder="status.has_app_secret ? '••••••••  (leave blank to keep)' : 'For webhook signature (optional)'"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :md="12">
                  <a-form-item :label="tf('Webhook_Verify_Token', 'Webhook Verify Token')">
                    <a-input v-model:value="form.webhook_verify_token" placeholder="Any secret string you set in Meta" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :md="12">
                  <a-form-item :label="tf('Default_Country_Code', 'Default Country Code')" :extra="tf('Default_Country_Code_Hint', 'Prepended to local numbers starting with 0.')">
                    <a-input v-model:value="form.default_country_code" placeholder="e.g. 1, 44, 212" />
                  </a-form-item>
                </a-col>
              </a-row>
              <a-space>
                <a-button type="primary" :loading="saving" @click="save">{{ $t('Save') }}</a-button>
                <a-button :loading="testing" :disabled="saving" @click="testConnection">{{ tf('Test_Connection', 'Test Connection') }}</a-button>
              </a-space>
            </a-form>
          </a-card>

          <a-card size="small" :title="tf('Automated_Messages', 'Automated Messages')">
            <p class="muted">{{ tf('Automated_Messages_Help', 'Automatically send a WhatsApp message when these events happen. Each event uses the matching template (by key).') }}</p>
            <div v-for="key in automationKeys" :key="key" style="margin-bottom: 8px">
              <a-switch :checked="!!form.automations[key]" @update:checked="v => (form.automations[key] = v)" />
              <span style="margin-left: 8px">{{ automationLabels[key] || key }}</span>
            </div>
            <a-empty v-if="!automationKeys.length" :description="tf('No_Automations_Available', 'No automation events available.')" />
          </a-card>
        </a-col>

        <a-col :xs="24" :lg="8">
          <a-card size="small" style="margin-bottom: 16px; text-align: center">
            <h4 style="margin-bottom: 8px">{{ tf('Connection_Status', 'Connection Status') }}</h4>
            <a-tag :color="status.is_connected ? 'success' : 'default'" style="font-size: 13px; padding: 4px 12px">
              {{ status.is_connected ? tf('Connected', 'Connected') : tf('Not_Connected', 'Not Connected') }}
            </a-tag>
            <p v-if="status.connected_at" class="muted" style="margin: 8px 0 0">{{ tf('Since', 'Since') }} {{ status.connected_at }}</p>
          </a-card>

          <a-card size="small" :title="tf('Monthly_Quota', 'Monthly Quota')" style="margin-bottom: 16px">
            <div class="row"><span>{{ tf('Used', 'Used') }}</span><strong>{{ quota.used }}</strong></div>
            <div class="row"><span>{{ tf('Limit', 'Limit') }}</span><strong>{{ quota.max === null ? tf('Unlimited', 'Unlimited') : quota.max }}</strong></div>
            <div v-if="quota.remaining !== null" class="row"><span>{{ tf('Remaining', 'Remaining') }}</span><strong>{{ quota.remaining }}</strong></div>
            <a-progress v-if="quota.max" :percent="quotaPercent" :status="quotaStatus" :show-info="false" style="margin-top: 8px" />
          </a-card>

          <a-card size="small" :title="tf('Webhook_URL', 'Webhook URL')">
            <p class="muted" style="margin-bottom: 6px">{{ tf('Webhook_URL_Hint', 'Paste this into your Meta app webhook configuration:') }}</p>
            <a-textarea :value="webhookUrl" readonly :rows="2" />
          </a-card>
        </a-col>
      </a-row>
    </template>
  </div>
</template>

<script setup>
/**
 * SaaS WhatsApp (Meta Cloud API) tenant settings — port of legacy
 * whatsapp/Settings.vue.
 *   GET  whatsapp/settings → {globally_enabled, webhook_url, automation_keys,
 *        settings: {phone_number_id, business_account_id, webhook_verify_token,
 *        default_country_code, automations, is_connected, connected_at,
 *        has_access_token, has_app_secret}, quota: {used, max, remaining}}
 *   POST whatsapp/settings (form)   POST whatsapp/test → {status, message}
 * Secrets are write-only: blank keeps the stored value.
 */
import { ref, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';

const loading = ref(true);
const saving = ref(false);
const testing = ref(false);
const globallyEnabled = ref(false);
const webhookUrl = ref('');
const automationKeys = ref([]);
const automationLabels = {
  invoice_created: 'Invoice created (new sale)',
  payment_received: 'Payment received',
};
const status = ref({ is_connected: false, connected_at: null, has_access_token: false, has_app_secret: false });
const quota = ref({ used: 0, max: null, remaining: null });
const form = ref({
  phone_number_id: '', business_account_id: '', access_token: '', app_secret: '',
  webhook_verify_token: '', default_country_code: '', automations: {},
});

const quotaPercent = computed(() => (quota.value.max ? Math.min(100, Math.round((quota.value.used / quota.value.max) * 100)) : 0));
const quotaStatus = computed(() => (quotaPercent.value >= 100 ? 'exception' : quotaPercent.value >= 80 ? 'active' : 'normal'));

async function load() {
  loading.value = true;
  try {
    const d = await http.get('whatsapp/settings');
    globallyEnabled.value = !!d.globally_enabled;
    webhookUrl.value = d.webhook_url || '';
    automationKeys.value = d.automation_keys || [];
    status.value = d.settings || status.value;
    quota.value = d.quota || quota.value;
    const s = d.settings || {};
    form.value = {
      ...form.value,
      phone_number_id: s.phone_number_id || '',
      business_account_id: s.business_account_id || '',
      webhook_verify_token: s.webhook_verify_token || '',
      default_country_code: s.default_country_code || '',
      automations: { ...(s.automations || {}) },
      access_token: '',
      app_secret: '',
    };
  } catch (e) {
    message.error(tf('Failed_To_Load_WhatsApp_Settings', 'Failed to load WhatsApp settings.'));
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  try {
    await http.post('whatsapp/settings', form.value);
    message.success(tf('WhatsApp_Settings_Saved', 'WhatsApp settings saved.'));
    await load();
  } catch (e) {
    message.error(e?.data?.message || tf('Failed_To_Save', 'Failed to save.'));
  } finally {
    saving.value = false;
  }
}

async function testConnection() {
  testing.value = true;
  try {
    // Persist first so the test uses the latest credentials (legacy flow).
    await http.post('whatsapp/settings', form.value);
    const res = await http.post('whatsapp/test', {});
    if (res?.status === 'success') message.success(res.message || 'OK');
    else message.error(res?.message || tf('Connection_Test_Failed', 'Connection test failed.'));
    await load();
  } catch (e) {
    message.error(e?.data?.message || tf('Connection_Test_Failed', 'Connection test failed.'));
  } finally {
    testing.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.row { display: flex; justify-content: space-between; margin-bottom: 4px; }
</style>
