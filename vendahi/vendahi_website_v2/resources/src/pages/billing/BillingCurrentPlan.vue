<template>
  <div class="page">
    <PageHeader :title="tf('Current_Plan', 'Current Plan')" :breadcrumb="[tf('Billing', 'Billing'), tf('Current_Plan', 'Current Plan')]" />

    <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />

    <!-- No subscription -->
    <a-card v-else-if="!subscription" style="text-align: center; padding: 24px 0">
      <CreditCardOutlined style="font-size: 40px; opacity: 0.4" />
      <h3 style="margin-top: 12px">{{ tf('No_active_subscription', 'No Active Subscription') }}</h3>
      <p class="muted">{{ tf('Choose_plan_to_start', 'Choose a plan to start using the application.') }}</p>
      <a-button type="primary" @click="$router.push('/subscription/change-plan')">{{ tf('Choose_a_Plan', 'Choose a Plan') }}</a-button>
    </a-card>

    <!-- Expired -->
    <a-card v-else-if="subscription.status === 'expired'" style="text-align: center; padding: 24px 0">
      <CloseCircleOutlined style="font-size: 40px; color: #ff4d4f" />
      <h3 style="margin-top: 12px">{{ tf('Plan_Has_Ended', 'Your Plan Has Ended') }}</h3>
      <p class="muted" style="max-width: 520px; margin: 0 auto 12px">
        {{ tf('Plan_ended_message', 'Your subscription has expired. Resubscribe now to regain access to all features and continue managing your business.') }}
      </p>
      <p>
        <span class="muted">{{ tf('Previous_Plan', 'Previous Plan') }}:</span> <strong>{{ plan ? plan.name : '—' }}</strong>
        <a-divider type="vertical" />
        <span class="muted">{{ tf('Expired_On', 'Expired On') }}:</span> <strong>{{ fmtDate(subscription.ends_at) }}</strong>
      </p>
      <a-button type="primary" size="large" @click="$router.push('/subscription/change-plan')">{{ tf('Resubscribe_Now', 'Resubscribe Now') }}</a-button>
    </a-card>

    <!-- Active -->
    <template v-else>
      <a-alert
        v-if="pendingUpgrade" type="info" show-icon style="margin-bottom: 16px"
        :message="tf('Pending_Upgrade', 'Pending Upgrade Request')"
      >
        <template #description>
          {{ tf('Pending_upgrade_message', 'You have a pending upgrade to') }} <strong>{{ pendingUpgrade.plan_name }}</strong>
          ({{ cycleLabel(pendingUpgrade.billing_cycle) }} — {{ money(pendingUpgrade.amount, pendingUpgrade.currency) }}).
          {{ tf('Pending_upgrade_wait', 'Please wait for admin approval before submitting another request.') }}
        </template>
        <template #action>
          <a-button size="small" danger :loading="cancelUpgradeLoading" @click="cancelUpgradeOpen = true">{{ tf('Cancel_Upgrade', 'Cancel Upgrade') }}</a-button>
        </template>
      </a-alert>

      <a-row :gutter="16">
        <a-col :xs="24" :lg="16">
          <a-card size="small" style="margin-bottom: 16px">
            <template #title>{{ plan.name }}</template>
            <template #extra><a-tag :color="statusColor(subscription.status)">{{ capitalize(subscription.status) }}</a-tag></template>
            <a-descriptions size="small" :column="{ xs: 1, md: 2 }" bordered>
              <a-descriptions-item :label="tf('Billing_Cycle', 'Billing Cycle')">{{ cycleLabel(subscription.billing_cycle) }}</a-descriptions-item>
              <a-descriptions-item :label="tf('End_Date', 'End Date')">{{ fmtDate(subscription.ends_at) }}</a-descriptions-item>
              <a-descriptions-item :label="$t('Amount')"><strong>{{ money(subscription.amount, subscription.currency) }}</strong></a-descriptions-item>
              <a-descriptions-item v-if="subscription.trial_ends_at" :label="tf('Trial_Ends', 'Trial Ends')">{{ fmtDate(subscription.trial_ends_at) }}</a-descriptions-item>
              <a-descriptions-item :label="tf('start_date', 'Start Date')">{{ fmtDate(subscription.starts_at) }}</a-descriptions-item>
              <a-descriptions-item :label="tf('Days_Remaining', 'Days Remaining')">
                <span :style="subscription.days_remaining <= 7 ? 'color:#ff4d4f;font-weight:600' : ''">{{ subscription.days_remaining }} {{ tf('days', 'days') }}</span>
              </a-descriptions-item>
            </a-descriptions>

            <a-alert v-if="subscription.status === 'pending'" type="info" show-icon style="margin-top: 12px" :message="tf('Subscription_pending_notice', 'Your subscription is currently pending approval.')" />
            <a-alert v-if="subscription.is_cancelled" type="warning" show-icon style="margin-top: 12px" :message="`${tf('Subscription_cancelled_notice', 'Your subscription is cancelled and will expire on')} ${fmtDate(subscription.ends_at)}.`" />

            <a-space wrap style="margin-top: 16px">
              <a-button v-if="subscription.status !== 'pending' && !pendingUpgrade" type="primary" @click="$router.push('/subscription/change-plan')">{{ tf('Change_Plan', 'Change Plan') }}</a-button>
              <a-button v-else-if="pendingUpgrade" disabled>{{ tf('Upgrade_Pending', 'Upgrade Pending') }}</a-button>
              <a-button @click="$router.push('/subscription/history')">{{ tf('Billing_History', 'Billing History') }}</a-button>
              <a-button @click="$router.push('/subscription/invoices')">{{ tf('Invoices', 'Invoices') }}</a-button>
              <a-button v-if="subscription.status === 'active'" danger @click="cancelOpen = true">{{ tf('Cancel_Subscription', 'Cancel Subscription') }}</a-button>
              <a-button v-if="subscription.can_resume" type="primary" ghost :loading="resumeLoading" @click="resumeSubscription">{{ tf('Resume_Subscription', 'Resume Subscription') }}</a-button>
            </a-space>
          </a-card>
        </a-col>

        <a-col :xs="24" :lg="8">
          <a-card size="small" :title="tf('Usage_Limits', 'Usage Limits')" style="margin-bottom: 16px">
            <a-skeleton v-if="usageLoading" active :paragraph="{ rows: 3 }" />
            <template v-else-if="planUsage && planUsage.has_plan">
              <div v-for="(limit, key) in planUsage.limits" :key="key" class="usage">
                <div class="usage-head">
                  <span>{{ limit.label }}</span>
                  <span :style="limitStyle(limit)">{{ limit.current }} / {{ limit.unlimited ? '∞' : limit.max }}</span>
                </div>
                <a-progress v-if="!limit.unlimited" :percent="Math.min(100, Number(limit.percentage) || 0)" :show-info="false" size="small" :status="limit.percentage >= 100 ? 'exception' : 'normal'" />
              </div>
            </template>
            <p v-else class="muted">{{ tf('No_plan_data', 'No plan data available.') }}</p>
          </a-card>

          <a-card size="small" :title="$t('Features')">
            <a-skeleton v-if="usageLoading" active :paragraph="{ rows: 3 }" />
            <template v-else-if="planUsage && planUsage.has_plan">
              <div v-for="(feat, key) in planUsage.features" :key="key" class="feature" :class="{ off: !feat.enabled }">
                <CheckOutlined v-if="feat.enabled" style="color: #52c41a" /><CloseOutlined v-else style="color: #ff4d4f" />
                <span>{{ feat.label }}</span>
              </div>
            </template>
            <p v-else class="muted">{{ tf('No_features_listed', 'No features listed for this plan.') }}</p>
          </a-card>
        </a-col>
      </a-row>
    </template>

    <a-modal v-model:open="cancelOpen" :title="tf('Cancel_Subscription', 'Cancel Subscription')" :ok-text="tf('Confirm_Cancel', 'Yes, Cancel')" :cancel-text="tf('Keep_Subscription', 'Keep Subscription')" ok-type="danger" :confirm-loading="cancelLoading" @ok="cancelSubscription">
      <p>{{ tf('Cancel_confirm_message', 'Are you sure you want to cancel? Your subscription will remain active until the end of the current billing period.') }}</p>
      <p v-if="subscription"><span class="muted">{{ tf('Active_until', 'Active until') }}:</span> <strong>{{ fmtDate(subscription.ends_at) }}</strong></p>
    </a-modal>
    <a-modal v-model:open="cancelUpgradeOpen" :title="tf('Cancel_Pending_Upgrade', 'Cancel Pending Upgrade')" :ok-text="tf('Confirm_Cancel_Upgrade', 'Yes, Cancel Upgrade')" :cancel-text="tf('Keep_Upgrade', 'Keep Upgrade')" ok-type="danger" :confirm-loading="cancelUpgradeLoading" @ok="cancelPendingUpgrade">
      <p>
        {{ tf('Cancel_upgrade_confirm', 'Are you sure you want to cancel your pending upgrade to') }} <strong>{{ pendingUpgrade ? pendingUpgrade.plan_name : '' }}</strong>?
        {{ tf('Cancel_upgrade_note', 'Your current plan will remain unchanged.') }}
      </p>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * SaaS billing — current plan (port of legacy billing/current_plan.vue).
 *   GET  billing/current-plan → {subscription, plan, has_active, pending_upgrade, currency_symbol}
 *   GET  billing/plan-usage   → {has_plan, limits{}, features{}} (falls back to window.__planSummary)
 *   POST billing/cancel | billing/resume | billing/cancel-upgrade
 */
import { ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { CreditCardOutlined, CloseCircleOutlined, CheckOutlined, CloseOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';
import { usePlanStore } from '../../stores/plan';
import { useBillingFormat } from './billingFormat';

const { fmtDate, cycleLabel, capitalize, statusColor, currencySymbol, money } = useBillingFormat();
const plan$ = usePlanStore();

const loading = ref(true);
const subscription = ref(null);
const plan = ref(null);
const pendingUpgrade = ref(null);
const planUsage = ref(null);
const usageLoading = ref(true);
const cancelOpen = ref(false);
const cancelLoading = ref(false);
const cancelUpgradeOpen = ref(false);
const cancelUpgradeLoading = ref(false);
const resumeLoading = ref(false);

function limitStyle(limit) {
  if (limit.unlimited) return 'color:#1677ff';
  if (limit.percentage >= 100) return 'color:#ff4d4f;font-weight:600';
  if (limit.percentage >= 80) return 'color:#faad14;font-weight:600';
  return '';
}

async function fetchCurrentPlan() {
  loading.value = true;
  try {
    const data = await http.get('billing/current-plan');
    subscription.value = data.subscription;
    plan.value = data.plan;
    pendingUpgrade.value = data.pending_upgrade || null;
    currencySymbol.value = data.currency_symbol || '$';
  } catch (e) {
    message.error(e?.data?.message || 'Failed to load plan data.');
  } finally {
    loading.value = false;
  }
}
async function fetchPlanUsage() {
  usageLoading.value = true;
  try {
    planUsage.value = await http.get('billing/plan-usage');
    plan$.refresh(planUsage.value);
  } catch (e) {
    planUsage.value = plan$.summary;
  } finally {
    usageLoading.value = false;
  }
}
async function cancelSubscription() {
  cancelLoading.value = true;
  try {
    const data = await http.post('billing/cancel', {});
    message.success(data?.message || 'Subscription cancelled.');
    cancelOpen.value = false;
    await fetchCurrentPlan();
  } catch (e) {
    message.error(e?.data?.message || 'Failed to cancel subscription.');
  } finally {
    cancelLoading.value = false;
  }
}
async function resumeSubscription() {
  resumeLoading.value = true;
  try {
    const data = await http.post('billing/resume', {});
    message.success(data?.message || 'Subscription resumed.');
    await fetchCurrentPlan();
  } catch (e) {
    message.error(e?.data?.message || 'Failed to resume subscription.');
  } finally {
    resumeLoading.value = false;
  }
}
async function cancelPendingUpgrade() {
  cancelUpgradeLoading.value = true;
  try {
    const data = await http.post('billing/cancel-upgrade', {});
    message.success(data?.message || 'Pending upgrade cancelled.');
    cancelUpgradeOpen.value = false;
    await fetchCurrentPlan();
  } catch (e) {
    message.error(e?.data?.message || 'Failed to cancel upgrade.');
  } finally {
    cancelUpgradeLoading.value = false;
  }
}

onMounted(() => { fetchCurrentPlan(); fetchPlanUsage(); });
</script>

<style scoped>
.muted { opacity: 0.65; }
.usage { margin-bottom: 12px; }
.usage-head { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 2px; }
.feature { display: flex; align-items: center; gap: 8px; padding: 4px 0; }
.feature.off { opacity: 0.55; }
</style>
