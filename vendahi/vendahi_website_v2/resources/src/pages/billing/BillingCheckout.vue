<template>
  <div class="page">
    <PageHeader :title="tf('Checkout', 'Checkout')" :breadcrumb="[tf('Billing', 'Billing'), tf('Checkout', 'Checkout')]">
      <template #actions>
        <a-button @click="$router.push('/subscription/change-plan')"><template #icon><ArrowLeftOutlined /></template>{{ tf('Back_to_plans', 'Back to plans') }}</a-button>
      </template>
    </PageHeader>

    <a-skeleton v-if="loading" active :paragraph="{ rows: 10 }" />

    <a-alert v-else-if="pendingUpgrade" type="info" show-icon :message="tf('Pending_Upgrade', 'Pending Upgrade Request')">
      <template #description>
        {{ tf('Pending_upgrade_message', 'You have a pending upgrade to') }} <strong>{{ pendingUpgrade.plan_name }}</strong>.
        {{ tf('Pending_upgrade_checkout_block', 'You cannot submit a new request until the pending one is processed or cancelled.') }}
        <div style="margin-top: 8px"><a-button size="small" @click="$router.push('/subscription/current-plan')">{{ tf('Back_to_plan', 'Back to Current Plan') }}</a-button></div>
      </template>
    </a-alert>

    <a-row v-else :gutter="16">
      <a-col :xs="24" :lg="14">
        <a-card size="small" :title="tf('Billing_Cycle', 'Billing Cycle')" style="margin-bottom: 16px">
          <a-radio-group v-model:value="selectedCycle" style="width: 100%">
            <a-row :gutter="12">
              <a-col :span="12">
                <a-radio-button value="monthly" class="cycle-btn">
                  <div><strong>{{ tf('Monthly', 'Monthly') }}</strong><div class="muted">{{ tf('Billed_monthly', 'Billed every month') }}</div></div>
                  <strong>{{ currencySymbol }}{{ plan.price.toFixed(2) }}</strong>
                </a-radio-button>
              </a-col>
              <a-col :span="12">
                <a-radio-button value="yearly" class="cycle-btn">
                  <div>
                    <strong>{{ tf('Yearly', 'Yearly') }} <a-tag v-if="plan.savings_percent > 0" color="success">-{{ plan.savings_percent }}%</a-tag></strong>
                    <div class="muted">{{ tf('Billed_annually', 'Billed annually') }}</div>
                  </div>
                  <strong>{{ currencySymbol }}{{ plan.yearly_price.toFixed(2) }}</strong>
                </a-radio-button>
              </a-col>
            </a-row>
          </a-radio-group>
        </a-card>

        <a-card size="small" :title="tf('Payment_Method', 'Payment Method')" style="margin-bottom: 16px">
          <a-empty v-if="!gateways.length" :description="tf('No_gateways', 'No payment gateways available. Please contact support.')" />
          <a-radio-group v-else v-model:value="selectedGateway" style="width: 100%">
            <div v-for="gw in gateways" :key="gw.key" class="gateway" :class="{ selected: selectedGateway === gw.key }" @click="selectedGateway = gw.key">
              <a-radio :value="gw.key" />
              <div class="gw-icon" :style="{ background: (gw.color || '#1677ff') + '22', color: gw.color || '#1677ff' }"><CreditCardOutlined /></div>
              <div>
                <div><strong>{{ gw.label }}</strong></div>
                <div class="muted">{{ gw.key === 'offline' ? tf('Pay_via_bank', 'Pay via bank transfer and upload proof of payment') : `${tf('Pay_with', 'Pay securely with')} ${gw.label}` }}</div>
              </div>
            </div>
          </a-radio-group>
        </a-card>

        <a-card v-if="isOffline" size="small" :title="tf('Bank_Transfer_Details', 'Bank Transfer Details')" style="margin-bottom: 16px">
          <div class="amount-box">
            <div class="muted">{{ tf('Amount_to_Transfer', 'Amount to Transfer') }}</div>
            <div class="amount-value">{{ currencySymbol }}{{ displayAmount }} {{ currencyCode }}</div>
          </div>
          <a-descriptions v-if="hasBankDetails" size="small" :column="1" bordered style="margin: 12px 0">
            <a-descriptions-item v-if="bankDetails.bank_name" :label="tf('Bank_Name', 'Bank Name')">{{ bankDetails.bank_name }}</a-descriptions-item>
            <a-descriptions-item v-if="bankDetails.account_holder" :label="tf('Account_Holder', 'Account Holder')">{{ bankDetails.account_holder }}</a-descriptions-item>
            <a-descriptions-item v-if="bankDetails.account_number" :label="tf('Account_Number', 'Account Number')">{{ bankDetails.account_number }}</a-descriptions-item>
            <a-descriptions-item v-if="bankDetails.iban" label="IBAN">{{ bankDetails.iban }}</a-descriptions-item>
            <a-descriptions-item v-if="bankDetails.swift" label="SWIFT / BIC">{{ bankDetails.swift }}</a-descriptions-item>
            <a-descriptions-item v-if="bankDetails.branch" :label="tf('Branch', 'Branch')">{{ bankDetails.branch }}</a-descriptions-item>
          </a-descriptions>
          <a-alert v-if="bankDetails.instructions" type="warning" style="margin-bottom: 12px" :message="tf('Instructions', 'Instructions')" :description="bankDetails.instructions" />
          <a-alert v-if="!hasBankDetails" type="warning" show-icon style="margin-bottom: 12px" :message="tf('Bank_details_not_configured', 'Bank details are not configured yet. Please contact support.')" />
          <a-divider />
          <div style="font-weight: 600; margin-bottom: 8px">{{ tf('Upload_Proof', 'Upload Proof of Payment') }} <span style="color: #ff4d4f">*</span></div>
          <a-upload-dragger :file-list="proofFile ? [proofFile] : []" :max-count="1" accept=".jpg,.jpeg,.png,.webp,.pdf" :before-upload="setProof" @remove="proofFile = null">
            <p><UploadOutlined style="font-size: 28px; opacity: 0.5" /></p>
            <p><strong>{{ tf('Click_or_drag', 'Click or drag file here') }}</strong></p>
            <p class="muted">{{ tf('File_formats', 'JPG, PNG, WebP or PDF (max 5MB)') }}</p>
          </a-upload-dragger>
        </a-card>

        <a-button v-if="gateways.length" type="primary" block size="large" :loading="processing" @click="submitPayment">
          <template #icon><LockOutlined /></template>
          {{ isOffline ? tf('Submit_Payment_Proof', 'Submit Payment Proof') : `${tf('Pay', 'Pay')} ${currencySymbol}${displayAmount} ${currencyCode}` }}
        </a-button>
        <p class="muted" style="text-align: center; margin-top: 12px">
          {{ isOffline ? tf('Proof_review_note', 'Your proof will be reviewed by our team. Your plan activates after approval.') : tf('Payment_secure', 'Your payment is secured and encrypted. You will be redirected to complete payment.') }}
        </p>
      </a-col>

      <a-col :xs="24" :lg="10">
        <a-card size="small" :title="tf('Order_Summary', 'Order Summary')">
          <div class="sum"><span class="muted">{{ tf('Plan', 'Plan') }}</span><strong>{{ plan.name }}</strong></div>
          <div class="sum"><span class="muted">{{ tf('Billing_Cycle', 'Billing Cycle') }}</span><strong>{{ selectedCycle === 'yearly' ? tf('Yearly', 'Yearly') : tf('Monthly', 'Monthly') }}</strong></div>
          <a-divider style="margin: 8px 0" />
          <div class="sum"><span class="muted">{{ tf('Subtotal', 'Subtotal') }}</span><strong>{{ currencySymbol }}{{ displayAmount }} {{ currencyCode }}</strong></div>
          <div class="sum"><span class="muted">{{ $t('Tax') }}</span><strong>{{ currencySymbol }}0.00</strong></div>
          <a-divider style="margin: 8px 0" />
          <div class="sum total"><span>{{ $t('Total') }}</span><span>{{ currencySymbol }}{{ displayAmount }} {{ currencyCode }}</span></div>
          <a-alert v-if="isUpgrade !== null" type="info" show-icon style="margin-top: 12px" :message="isUpgrade ? tf('Upgrade_note', 'Your current plan will be replaced immediately after payment.') : tf('Downgrade_note', 'Your new plan will start immediately after payment.')" />
          <div class="section-label" style="margin-top: 16px">{{ tf('Included', 'Included') }}</div>
          <div v-for="(limit, key) in plan.limits" :key="'l' + key" class="row-item"><CheckOutlined style="color: #52c41a" /> {{ limit.display }} {{ limit.label }}</div>
          <div v-for="(feat, key) in plan.features" :key="'f' + key" class="row-item"><CheckOutlined style="color: #52c41a" /> {{ feat.label }}</div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/**
 * Port of legacy billing/checkout.vue.
 *   GET  billing/checkout-data/{plan} → {plan, gateways[{key,label,icon,color}], is_upgrade,
 *        pending_upgrade, currency_code, currency_symbol, bank_details}
 *   POST billing/checkout {plan_id, gateway, billing_cycle} → {success, payment_url}  (redirect)
 *   POST billing/offline-payment (multipart: plan_id, billing_cycle, offline_method, payment_proof)
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { ArrowLeftOutlined, CreditCardOutlined, UploadOutlined, LockOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';
import { useBillingFormat } from './billingFormat';

const route = useRoute();
const router = useRouter();
const { currencySymbol } = useBillingFormat();

const loading = ref(true);
const processing = ref(false);
const plan = ref({ name: '', price: 0, yearly_price: 0, savings_percent: 0, limits: {}, features: {} });
const gateways = ref([]);
const selectedCycle = ref(route.query.cycle === 'yearly' ? 'yearly' : 'monthly');
const selectedGateway = ref('');
const proofFile = ref(null);
const isUpgrade = ref(null);
const pendingUpgrade = ref(null);
const currencyCode = ref('USD');
const bankDetails = ref({});

const displayAmount = computed(() => ((selectedCycle.value === 'yearly' ? plan.value.yearly_price : plan.value.price) || 0).toFixed(2));
const isOffline = computed(() => selectedGateway.value === 'offline');
const hasBankDetails = computed(() => !!(bankDetails.value.bank_name || bankDetails.value.account_number));

function setProof(file) {
  if (file.size > 5 * 1024 * 1024) { message.warning(tf('File_too_large', 'File must be less than 5MB.')); return false; }
  if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'].includes(file.type)) {
    message.warning(tf('Invalid_file_type', 'Only JPG, PNG, WebP, and PDF files are allowed.'));
    return false;
  }
  proofFile.value = file;
  return false; // manual upload
}

async function submitPayment() {
  if (!selectedGateway.value) { message.warning(tf('Select_gateway', 'Please select a payment method.')); return; }
  processing.value = true;
  try {
    if (isOffline.value) {
      if (!proofFile.value) { message.warning(tf('Upload_proof_required', 'Please upload proof of payment.')); return; }
      const fd = new FormData();
      fd.append('plan_id', plan.value.id);
      fd.append('billing_cycle', selectedCycle.value);
      fd.append('offline_method', 'bank_transfer');
      fd.append('payment_proof', proofFile.value);
      const data = await http.postForm('billing/offline-payment', fd);
      if (data?.success) {
        message.success(data.message || 'Payment proof submitted successfully.');
        router.push('/subscription/current-plan');
      } else {
        message.error(data?.message || 'Request failed.');
      }
      return;
    }
    const data = await http.post('billing/checkout', { plan_id: plan.value.id, gateway: selectedGateway.value, billing_cycle: selectedCycle.value });
    if (data?.success && data.payment_url) window.location.href = data.payment_url;
    else message.error(data?.message || 'Payment initialization failed.');
  } catch (e) {
    message.error(e?.data?.message || 'Payment failed. Please try again.');
  } finally {
    processing.value = false;
  }
}

onMounted(async () => {
  try {
    const data = await http.get(`billing/checkout-data/${route.params.id}`);
    plan.value = data.plan;
    gateways.value = data.gateways || [];
    isUpgrade.value = data.is_upgrade ?? null;
    pendingUpgrade.value = data.pending_upgrade || null;
    currencyCode.value = data.currency_code || 'USD';
    currencySymbol.value = data.currency_symbol || '$';
    bankDetails.value = data.bank_details || {};
    if (gateways.value.length) selectedGateway.value = gateways.value[0].key;
  } catch (e) {
    message.error(e?.data?.message || 'Failed to load checkout data.');
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.cycle-btn { width: 100%; height: auto; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; gap: 12px; line-height: 1.3; }
.gateway { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid rgba(128, 128, 128, 0.25); border-radius: 8px; margin-bottom: 8px; cursor: pointer; }
.gateway.selected { border-color: #1677ff; background: rgba(22, 119, 255, 0.06); }
.gw-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex: none; }
.amount-box { text-align: center; padding: 10px; border: 1px solid rgba(13, 148, 136, 0.35); background: rgba(13, 148, 136, 0.06); border-radius: 8px; }
.amount-value { font-size: 22px; font-weight: 700; color: #0d9488; }
.sum { display: flex; justify-content: space-between; padding: 4px 0; }
.sum.total { font-size: 16px; font-weight: 700; color: #1677ff; }
.section-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.65; margin-bottom: 6px; }
.row-item { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 4px; }
</style>
