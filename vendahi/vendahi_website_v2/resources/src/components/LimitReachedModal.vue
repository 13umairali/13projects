<template>
  <a-modal
    v-model:open="limitReachedVisible"
    :footer="null"
    :width="420"
    centered
    :z-index="2000"
    class="limit-modal"
    @cancel="hideLimitReached"
  >
    <div class="limit-body">
      <div class="limit-icon"><WarningOutlined /></div>
      <h3 class="limit-title">{{ limitReachedKind === 'feature' ? tf('UpgradeRequired', 'Upgrade Required') : tf('Plan_Limit_Reached', 'Plan Limit Reached') }}</h3>
      <p class="limit-message">{{ limitReachedMessage }}</p>
      <a-space>
        <a-button type="primary" @click="goToUpgrade">
          <template #icon><ArrowUpOutlined /></template>
          {{ tf('Upgrade_Plan', 'Upgrade Plan') }}
        </a-button>
        <a-button @click="hideLimitReached">{{ tf('Dismiss', 'Dismiss') }}</a-button>
      </a-space>
    </div>
  </a-modal>
</template>

<script setup>
/**
 * The legacy App.vue "limit-reached-overlay" as an Ant modal. Opened by
 * lib/limitReached.js (fed by the 403 handler in lib/http.js). Upgrade goes to
 * the billing plan page — legacy until Phase 8 migrates billing, hence goTo().
 */
import { WarningOutlined, ArrowUpOutlined } from '@ant-design/icons-vue';
import { limitReachedVisible, limitReachedMessage, limitReachedKind, hideLimitReached } from '../lib/limitReached';
import { t as tf } from '../i18n';
import { goTo } from '../lib/nav';

function goToUpgrade() {
  hideLimitReached();
  goTo('/app/billing/change-plan');
}
</script>

<style scoped>
.limit-body {
  text-align: center;
  padding: 8px 4px 4px;
}
.limit-icon {
  font-size: 34px;
  color: #f59e0b;
  margin-bottom: 8px;
}
.limit-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px;
}
.limit-message {
  opacity: 0.75;
  margin: 0 0 20px;
}
</style>
