<template>
  <div class="page">
    <PageHeader :title="tf('Choose_Plan', 'Choose Your Plan')" :breadcrumb="[tf('Billing', 'Billing'), tf('Change_Plan', 'Change Plan')]">
      <template #actions>
        <a-button @click="$router.push('/subscription/current-plan')"><template #icon><ArrowLeftOutlined /></template>{{ $t('Back') }}</a-button>
      </template>
    </PageHeader>

    <a-skeleton v-if="loading" active :paragraph="{ rows: 10 }" />
    <template v-else>
      <a-alert v-if="pendingUpgrade" type="info" show-icon style="margin-bottom: 16px" :message="tf('Pending_Upgrade', 'Pending Upgrade Request')">
        <template #description>
          {{ tf('Pending_upgrade_message', 'You have a pending upgrade to') }} <strong>{{ pendingUpgrade.plan_name }}</strong>.
          {{ tf('Pending_upgrade_wait', 'Please wait for admin approval before submitting another request.') }}
        </template>
      </a-alert>

      <div style="text-align: center; margin-bottom: 20px">
        <a-radio-group v-model:value="cycle" button-style="solid">
          <a-radio-button value="monthly">{{ tf('Monthly', 'Monthly') }}</a-radio-button>
          <a-radio-button value="yearly">{{ tf('Yearly', 'Yearly') }} <a-tag color="success" style="margin-left: 6px">{{ $t('Save') }}</a-tag></a-radio-button>
        </a-radio-group>
      </div>

      <a-row :gutter="[16, 16]" justify="center">
        <a-col v-for="p in plans" :key="p.id" :xs="24" :md="12" :xl="8">
          <a-card class="plan-card" :class="{ current: p.is_current }">
            <a-tag v-if="p.is_current" color="processing" class="current-badge">{{ tf('Current', 'Current') }}</a-tag>
            <h3 class="plan-name">{{ p.name }}</h3>
            <div class="muted">{{ p.slug }}</div>
            <div class="price">
              <span class="amount">{{ currencySymbol }}{{ (cycle === 'yearly' ? p.yearly_price : p.price).toFixed(2) }}</span>
              <span class="period">/{{ cycle === 'yearly' ? tf('year', 'year') : tf('month', 'month') }}</span>
            </div>
            <div v-if="cycle === 'yearly' && p.savings_percent > 0" style="color: #52c41a; font-weight: 600">{{ $t('Save') }} {{ p.savings_percent }}%</div>
            <a-divider />
            <div class="section-label">{{ tf('Limits', 'Limits') }}</div>
            <div v-for="(limit, key) in p.limits" :key="'l' + key" class="row-item"><CheckCircleOutlined style="color: #1677ff" /> {{ limit.display }} {{ limit.label }}</div>
            <div class="section-label" style="margin-top: 12px">{{ $t('Features') }}</div>
            <div v-for="(feat, key) in p.features" :key="'f' + key" class="row-item"><CheckOutlined style="color: #52c41a" /> {{ feat.label }}</div>
            <div v-for="(feat, key) in missingFeatures(p)" :key="'m' + key" class="row-item muted"><CloseOutlined /> {{ feat.label }}</div>
            <div style="margin-top: 20px">
              <a-button v-if="p.is_current" block disabled>{{ tf('Current_Plan', 'Current Plan') }}</a-button>
              <a-button v-else-if="pendingUpgrade" block disabled>{{ tf('Upgrade_Pending', 'Upgrade Pending') }}</a-button>
              <a-button v-else type="primary" block @click="$router.push({ path: `/subscription/checkout/${p.id}`, query: { cycle } })">
                <template v-if="hasActive && currentPlanPrice !== null && p.price > currentPlanPrice">{{ tf('Upgrade', 'Upgrade') }}</template>
                <template v-else-if="hasActive">{{ tf('Switch', 'Switch') }}</template>
                <template v-else>{{ tf('Get_Started', 'Get Started') }}</template>
              </a-button>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </template>
  </div>
</template>

<script setup>
/** Port of legacy billing/change_plan.vue — GET billing/plans → {plans, current_plan_id, current_plan_price, has_active, pending_upgrade, currency_symbol}. */
import { ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { ArrowLeftOutlined, CheckCircleOutlined, CheckOutlined, CloseOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';
import { useBillingFormat } from './billingFormat';

const { currencySymbol } = useBillingFormat();
const loading = ref(true);
const plans = ref([]);
const cycle = ref('monthly');
const currentPlanPrice = ref(null);
const hasActive = ref(false);
const pendingUpgrade = ref(null);
const allFeatures = ref({});

function missingFeatures(p) {
  const active = p.features || {};
  const out = {};
  for (const key of Object.keys(allFeatures.value || {})) if (!active[key]) out[key] = allFeatures.value[key];
  return out;
}

onMounted(async () => {
  try {
    const data = await http.get('billing/plans');
    plans.value = data.plans || [];
    currentPlanPrice.value = data.current_plan_price ?? null;
    hasActive.value = !!data.has_active;
    pendingUpgrade.value = data.pending_upgrade || null;
    currencySymbol.value = data.currency_symbol || '$';
    if (plans.value.length) allFeatures.value = plans.value[0].all_features || {};
  } catch (e) {
    message.error(e?.data?.message || 'Failed to load plans.');
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.plan-card { position: relative; height: 100%; }
.plan-card.current { border-color: #1677ff; }
.current-badge { position: absolute; top: 12px; right: 12px; margin: 0; }
.plan-name { margin: 0; font-size: 20px; }
.muted { opacity: 0.6; font-size: 13px; }
.price { margin: 12px 0 4px; }
.amount { font-size: 30px; font-weight: 700; }
.period { opacity: 0.6; }
.section-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.65; margin-bottom: 6px; }
.row-item { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 4px; }
</style>
