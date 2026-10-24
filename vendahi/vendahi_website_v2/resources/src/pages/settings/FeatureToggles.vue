<template>
  <a-card :loading="loading">
    <a-alert
      type="info"
      show-icon
      :message="$t('Features')"
      description="Turn optional capabilities on or off for the whole system — the same switches as System Settings → Features."
      style="margin-bottom: 16px"
    />

    <div class="ft-toolbar">
      <a-button type="primary" :disabled="!dirty" :loading="saving" @click="save">
        <template #icon><SaveOutlined /></template>
        {{ $t('submit') }}
      </a-button>
    </div>

    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Enable_3_Decimal_Pricing') }}</div>
        <div class="setting-help">{{ $t('Enable_3_Decimal_Pricing_Help') }}</div>
      </div>
      <a-switch v-model:checked="features.enable_3_decimal_pricing" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('EnableKitchenDisplay') }}</div>
        <div class="setting-help">{{ $t('EnableKitchenDisplay_Help') }}</div>
      </div>
      <a-switch v-model:checked="features.enable_kitchen_display" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Show_Product_GTIN') }}</div>
        <div class="setting-help">{{ $t('Show_Product_GTIN_Help') }}</div>
      </div>
      <a-switch v-model:checked="features.show_product_gtin" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Track_Serial_IMEI') }}</div>
        <div class="setting-help">{{ $t('Track_Serial_IMEI_Hint') }}</div>
      </div>
      <a-switch v-model:checked="features.show_serial_tracking" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Enable_Multi_Pack_Selling') }}</div>
        <div class="setting-help">{{ $t('Enable_Multi_Pack_Selling_Hint') }}</div>
      </div>
      <a-switch v-model:checked="features.enable_multi_pack_selling" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Enable_Multi_Currency') }}</div>
        <div class="setting-help">{{ $t('Enable_Multi_Currency_Hint') }}</div>
      </div>
      <a-switch v-model:checked="features.enable_multi_currency" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Auto_Journal_Entries') }}</div>
        <div class="setting-help">{{ $t('Auto_Journal_Entries_Hint') }}</div>
      </div>
      <a-switch v-model:checked="features.accounting_auto_generate_journals" @change="dirty = true" />
    </div>
    <div class="setting-row">
      <div>
        <div class="setting-label">{{ $t('Allow_Overselling') }}</div>
        <div class="setting-help">{{ $t('Allow_Overselling_Help') }}</div>
      </div>
      <a-switch v-model:checked="features.allow_overselling" @change="dirty = true" />
    </div>
  </a-card>
</template>

<script setup>
/**
 * Feature toggles — the "Features" tab of Settings → Modules.
 *
 * Same optional-capability switches as System Settings → Features, but backed
 * by the dedicated GET/PUT feature_settings endpoint: it reads and writes ONLY
 * these fields, so saving here can't clobber the rest of the settings row the
 * way a partial post to settings/{id} would. The set is the SaaS subset of
 * the stocky reference's list — only columns the tenant settings table has.
 */
import { ref, reactive, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { SaveOutlined } from '@ant-design/icons-vue';
import http from '../../lib/http';

const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const dirty = ref(false);

const features = reactive({
  enable_3_decimal_pricing: false,
  enable_kitchen_display: false,
  show_product_gtin: true,
  show_serial_tracking: false,
  enable_multi_pack_selling: false,
  enable_multi_currency: false,
  accounting_auto_generate_journals: false,
  allow_overselling: false,
});

async function save() {
  saving.value = true;
  try {
    const payload = {};
    for (const key of Object.keys(features)) payload[key] = features[key] ? 1 : 0;
    await http.put('feature_settings', payload);
    dirty.value = false;
    message.success(t('Successfully_Updated'));
    // Same post-save side effect as System Settings: price inputs across the
    // app read their decimal count from this cache.
    try {
      localStorage.setItem('app_price_decimals', features.enable_3_decimal_pricing ? '3' : '2');
    } catch (e) { /* storage unavailable */ }
  } catch (e) {
    message.error(e?.message || t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  try {
    const data = await http.get('feature_settings');
    Object.assign(features, data?.features || {});
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.ft-toolbar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 4px;
}
.setting-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 14px 0;
  border-top: 1px solid rgba(128, 128, 128, 0.12);
}
.setting-row:first-of-type {
  border-top: none;
}
.setting-label {
  font-weight: 500;
}
.setting-help {
  font-size: 12px;
  color: #8c8c8c;
  max-width: 520px;
}
</style>
