<template>
  <div class="page">
    <PageHeader
      :title="$t('Payment_Gateway')"
      :breadcrumb="context === 'settings' ? [$t('Settings'), $t('Payment_Gateway')] : [$t('Store'), $t('Payment_Gateway')]"
    />

    <a-alert
      type="info" show-icon style="margin-bottom: 16px"
      message="Every gateway here is offered at the ONLINE STORE checkout once its credentials are stored and its switch is on."
      description="Each tab has its own on/off switch, saved the moment you flip it: switching a gateway off leaves its credentials in place, the checkout simply stops offering it. Stripe keys are shared globally (admin and online store use the same Stripe integration). Offline Payments holds cash on delivery and mobile money, which are paid outside the system."
    />

    <div v-if="loading" style="display: flex; justify-content: center; padding: 96px 0">
      <a-spin size="large" />
    </div>

    <template v-else>
    <!-- Gateways — one card, one tab per provider -->
    <a-card :bordered="false" class="pg-card" style="margin-bottom: 16px">
      <a-tabs v-model:active-key="gatewayTab" tab-position="left" class="pg-tabs">
        <!-- ============================== Stripe ============================== -->
        <a-tab-pane key="stripe">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': stripeOffered }"></span>
              <CreditCardOutlined /> Stripe
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #635bff; flex: 0 0 auto">
                <template #icon><CreditCardOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Stripe</div>
                <div class="pg-head__sub">{{ $t('Store') }} — {{ $t('Payment_Gateway') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="stripeOffered ? 'success' : 'default'">
                <template #icon><component :is="stripeOffered ? CheckCircleFilled : StopOutlined" /></template>
                {{ stripeOffered ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="stripeEnabled" :loading="!!flagSaving.stripe" @change="saveStripeFlag" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Keys stay editable even when cards are switched off, so turning
               them back on does not mean re-entering credentials. -->
          <a-form layout="vertical" class="pg-form">
            <!-- Field labels literal like legacy's .env-style naming. -->
            <a-form-item label="STRIPE KEY">
              <a-input v-model:value="gateway.stripe_key" :placeholder="$t('LeaveBlank')">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="STRIPE SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="gateway.stripe_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
          </a-form>
          <a-alert
            v-if="!stripeEnabled" type="info" show-icon style="margin-bottom: 20px"
            message="Card payments are switched off"
            description="Your keys are kept — the online-store checkout simply stops offering the card option until you switch it back on."
          />
          <a-alert
            v-else-if="!gateway.stripe_key" type="warning" show-icon style="margin-bottom: 20px"
            message="No Stripe key stored"
            description="Enter your Stripe key and secret to start accepting card payments in the online store."
          />

          <a-space>
            <a-button type="primary" :loading="saving" @click="save">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="gateway.stripe_key"
              :title="$t('RemoveStripeKeysConfirm')"
              @confirm="clearStripeKeys"
            >
              <a-button danger :loading="saving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Razorpay ============================== -->
        <a-tab-pane key="razorpay">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.razorpay }"></span>
              <ThunderboltOutlined /> Razorpay
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #3395ff; flex: 0 0 auto">
                <template #icon><ThunderboltOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Razorpay <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.razorpay ? 'success' : 'default'">
                <template #icon><component :is="offered.razorpay ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.razorpay ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.razorpay" :loading="!!flagSaving.razorpay" @change="saveMethodFlag('razorpay')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.razorpay" type="info" show-icon style="margin-bottom: 20px"
            message="Razorpay is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Razorpay until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="RAZORPAY KEY ID">
              <a-input v-model:value="razorpay.key_id" placeholder="rzp_test_xxxxxxxx">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="RAZORPAY KEY SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="razorpay.key_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Uses Razorpay Checkout at the storefront (cards, UPI, netbanking, wallets). Test vs live is decided by the key pair (rzp_test_/rzp_live_) — there is no separate sandbox switch. The checkout offers Razorpay as soon as both keys are stored."
            />
          </a-form>

          <a-space>
            <a-button type="primary" :loading="razorpaySaving" @click="saveRazorpay">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="razorpay.key_id"
              title="Remove the stored Razorpay keys? The checkout stops offering Razorpay."
              @confirm="clearRazorpay"
            >
              <a-button danger :loading="razorpaySaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== bKash ============================== -->
        <a-tab-pane key="bkash">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.bkash }"></span>
              <MobileOutlined /> bKash
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #e2136e; flex: 0 0 auto">
                <template #icon><MobileOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">bKash <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.bkash ? 'success' : 'default'">
                <template #icon><component :is="offered.bkash ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.bkash ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.bkash" :loading="!!flagSaving.bkash" @change="saveMethodFlag('bkash')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.bkash" type="info" show-icon style="margin-bottom: 20px"
            message="bKash is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering bKash until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="BKASH APP KEY">
              <a-input v-model:value="bkash.app_key">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="BKASH APP SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="bkash.app_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="BKASH USERNAME">
              <a-input v-model:value="bkash.username" placeholder="01XXXXXXXXX">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="BKASH PASSWORD" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="bkash.password" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item
              label="Sandbox (test mode)"
              extra="ON uses tokenized.sandbox.bka.sh with your sandbox credentials; turn OFF to charge real bKash accounts on tokenized.pay.bka.sh."
            >
              <a-switch v-model:checked="bkash.test_mode" />
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Uses bKash Tokenized Checkout (merchant credentials from the bKash merchant portal). Charges in BDT. The checkout offers bKash as soon as all four credentials are stored."
            />
          </a-form>

          <a-space>
            <a-button type="primary" :loading="bkashSaving" @click="saveBkash">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="bkash.app_key"
              title="Remove the stored bKash credentials? The checkout stops offering bKash."
              @confirm="clearBkash"
            >
              <a-button danger :loading="bkashSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== SSLCommerz ============================== -->
        <a-tab-pane key="sslcommerz">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.sslcommerz }"></span>
              <SafetyCertificateOutlined /> SSLCommerz
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #2e3192; flex: 0 0 auto">
                <template #icon><SafetyCertificateOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">SSLCommerz <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.sslcommerz ? 'success' : 'default'">
                <template #icon><component :is="offered.sslcommerz ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.sslcommerz ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.sslcommerz" :loading="!!flagSaving.sslcommerz" @change="saveMethodFlag('sslcommerz')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.sslcommerz" type="info" show-icon style="margin-bottom: 20px"
            message="SSLCommerz is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering SSLCommerz until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="SSLCOMMERZ STORE ID">
              <a-input v-model:value="sslcommerz.store_id" placeholder="teststore00live">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SSLCOMMERZ STORE PASSWORD" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="sslcommerz.store_password" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item
              label="Sandbox (test mode)"
              extra="ON uses sandbox.sslcommerz.com with sandbox credentials; turn OFF to charge real customers on securepay.sslcommerz.com."
            >
              <a-switch v-model:checked="sslcommerz.test_mode" />
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 12px"
              message="Hosted checkout covering cards, bKash/Nagad/Rocket mobile banking and internet banking in Bangladesh. Charges in BDT."
            />
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                IPN URL (recommended backstop — set it under My Stores → IPN Settings on the SSLCommerz merchant panel):
                <a-typography-text code copyable>{{ sslcommerzIpnUrl }}</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="sslcommerzSaving" @click="saveSslcommerz">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="sslcommerz.store_id"
              title="Remove the stored SSLCommerz credentials? The checkout stops offering SSLCommerz."
              @confirm="clearSslcommerz"
            >
              <a-button danger :loading="sslcommerzSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Paymob ============================== -->
        <a-tab-pane key="paymob">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.paymob }"></span>
              <GlobalOutlined /> Paymob
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #0d3b66; flex: 0 0 auto">
                <template #icon><GlobalOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Paymob <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.paymob ? 'success' : 'default'">
                <template #icon><component :is="offered.paymob ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.paymob ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.paymob" :loading="!!flagSaving.paymob" @change="saveMethodFlag('paymob')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.paymob" type="info" show-icon style="margin-bottom: 20px"
            message="Paymob is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Paymob until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="REGION" extra="Country of your Paymob account: egypt, ksa, uae, pakistan or oman (selects the API host; defaults to egypt).">
              <a-input v-model:value="paymob.region" placeholder="egypt">
                <template #prefix><GlobalOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="PUBLIC KEY">
              <a-input v-model:value="paymob.public_key" placeholder="egy_pk_test_...">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SECRET KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="paymob.secret_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="API KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="paymob.api_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="INTEGRATION ID(S)" extra="From Paymob Dashboard → Developers → Payment Integrations; comma-separate several to offer multiple methods.">
              <a-input v-model:value="paymob.integration_id" placeholder="e.g. 4419544 or 4419544,4419545">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="HMAC SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="paymob.hmac_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="CHARGE CURRENCY" extra="Must match your Paymob account's currency (EGP, SAR, AED, PKR or OMR) and your store's base currency.">
              <a-input v-model:value="paymob.currency" placeholder="EGP" style="max-width: 120px">
                <template #prefix><DollarOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 12px"
              message="Uses the Paymob Unified Checkout (cards, wallets and local methods in Egypt, KSA, UAE, Pakistan and Oman). All five credentials come from Paymob Dashboard → Settings → Account Info + Payment Integrations."
            />
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                Webhook is set automatically per payment — no dashboard setup needed. Backstop callback URL for reference:
                <a-typography-text code copyable>{{ paymobWebhookUrl }}</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="paymobSaving" @click="savePaymob">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="paymob.public_key"
              title="Remove the stored Paymob credentials? The checkout stops offering Paymob."
              @confirm="clearPaymob"
            >
              <a-button danger :loading="paymobSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== MyFatoorah ============================== -->
        <a-tab-pane key="myfatoorah">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.myfatoorah }"></span>
              <WalletOutlined /> MyFatoorah
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #3fb5a3; flex: 0 0 auto">
                <template #icon><WalletOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">MyFatoorah <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.myfatoorah ? 'success' : 'default'">
                <template #icon><component :is="offered.myfatoorah ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.myfatoorah ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.myfatoorah" :loading="!!flagSaving.myfatoorah" @change="saveMethodFlag('myfatoorah')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.myfatoorah" type="info" show-icon style="margin-bottom: 20px"
            message="MyFatoorah is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering MyFatoorah until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="API TOKEN" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="myfatoorah.api_token" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="ACCOUNT COUNTRY (LIVE)" extra="ISO code of your MyFatoorah account country — KWT, SAU, QAT, EGY, ARE, BHR, OMN or JOR. Selects the live API host; ignored in sandbox mode.">
              <a-input v-model:value="myfatoorah.country" placeholder="KWT" style="max-width: 120px">
                <template #prefix><GlobalOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="CHARGE CURRENCY" extra="Must match your MyFatoorah account currency (KWD, SAR, AED, QAR, BHD, OMR, JOD, EGP…) and your store's base currency.">
              <a-input v-model:value="myfatoorah.currency" placeholder="KWD" style="max-width: 120px">
                <template #prefix><DollarOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item
              label="Sandbox (test mode)"
              extra="ON uses apitest.myfatoorah.com with a demo API token; turn OFF to charge real customers on the live API."
            >
              <a-switch v-model:checked="myfatoorah.test_mode" />
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Hosted invoice link covering KNET, mada, cards, Apple Pay and STC Pay across the GCC. The API token comes from MyFatoorah Vendor Portal → Integration Settings → API Key."
            />
          </a-form>

          <a-space>
            <a-button type="primary" :loading="myfatoorahSaving" @click="saveMyfatoorah">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="myfatoorah.configured"
              title="Remove the stored MyFatoorah credentials? The checkout stops offering MyFatoorah."
              @confirm="clearMyfatoorah"
            >
              <a-button danger :loading="myfatoorahSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Moyasar ============================== -->
        <a-tab-pane key="moyasar">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.moyasar }"></span>
              <CreditCardOutlined /> Moyasar
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #20b2aa; flex: 0 0 auto">
                <template #icon><CreditCardOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Moyasar <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.moyasar ? 'success' : 'default'">
                <template #icon><component :is="offered.moyasar ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.moyasar ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.moyasar" :loading="!!flagSaving.moyasar" @change="saveMethodFlag('moyasar')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.moyasar" type="info" show-icon style="margin-bottom: 20px"
            message="Moyasar is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Moyasar until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="SECRET KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="moyasar.secret_key" placeholder="sk_test_... or sk_live_..." autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="CHARGE CURRENCY" extra="SAR by default — must match your store's base currency.">
              <a-input v-model:value="moyasar.currency" placeholder="SAR" style="max-width: 120px">
                <template #prefix><DollarOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Hosted invoice page covering mada, credit cards, Apple Pay and STC Pay in Saudi Arabia. Use an sk_test_ key for sandbox — Moyasar has no separate test host."
            />
          </a-form>

          <a-space>
            <a-button type="primary" :loading="moyasarSaving" @click="saveMoyasar">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="moyasar.configured"
              title="Remove the stored Moyasar credentials? The checkout stops offering Moyasar."
              @confirm="clearMoyasar"
            >
              <a-button danger :loading="moyasarSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>
        <!-- ============================== XPay (PostEx) ============================== -->
        <a-tab-pane key="xpay">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.xpay }"></span>
              <CreditCardOutlined /> XPay
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #0f766e; flex: 0 0 auto">
                <template #icon><CreditCardOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">XPay by PostEx <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.xpay ? 'success' : 'default'">
                <template #icon><component :is="offered.xpay ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.xpay ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.xpay" :loading="!!flagSaving.xpay" @change="saveMethodFlag('xpay')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.xpay" type="info" show-icon style="margin-bottom: 20px"
            message="XPay is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering XPay until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="ACCOUNT ID" extra="XPay Dashboard > Settings > Store Info.">
              <a-input v-model:value="xpay.account_id" placeholder="Your XPay account id">
                <template #prefix><IdcardOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="PUBLISHABLE KEY" extra="Settings > Store Info > Key Details > API Keys — used by the card element in the browser.">
              <a-input v-model:value="xpay.publishable_key" placeholder="xpay_pk_test_... or xpay_pk_live_...">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SECRET KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="xpay.secret_key" placeholder="xpay_sk_..." autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="API HMAC SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="xpay.hmac_secret" placeholder="Key Details > HMAC Keys" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WEBHOOK HMAC SECRET" :extra="xpay.webhook_secret_set ? $t('LeaveBlank') : 'Optional but recommended — lets XPay confirm late payments server-to-server.'">
              <a-input-password v-model:value="xpay.webhook_hmac_secret" placeholder="Key Details > HMAC Keys" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WEBHOOK URL" extra="Paste this URL into XPay Dashboard > Settings > Store Info > Webhook URL.">
              <a-typography-text code copyable>{{ xpayWebhookUrl }}</a-typography-text>
            </a-form-item>
            <a-form-item label="CHARGE CURRENCY" extra="PKR — must match your store's base currency.">
              <a-input v-model:value="xpay.currency" placeholder="PKR" style="max-width: 120px">
                <template #prefix><DollarOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item
              label="Sandbox (test mode)"
              extra="ON uses xstak-pay-stg.xstak.com with your staging keys; turn OFF (and store your live keys) to charge real customers."
            >
              <a-switch v-model:checked="xpay.test_mode" />
            </a-form-item>
            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Cards are entered in an XPay element embedded in the checkout page (no redirect). Every payment is re-verified server-side before the order is created. Note: XPay's SDK requires the API HMAC secret in the browser, as in XPay's official plugins."
            />
          </a-form>

          <a-space>
            <a-button type="primary" :loading="xpaySaving" @click="saveXpay">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="xpay.configured"
              title="Remove the stored XPay credentials? The checkout stops offering XPay."
              @confirm="clearXpay"
            >
              <a-button danger :loading="xpaySaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>
        <!-- ============================== PayFast ============================== -->
        <a-tab-pane key="payfast">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.payfast }"></span>
              <BankOutlined /> PayFast
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #e2183d; flex: 0 0 auto">
                <template #icon><BankOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">PayFast <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.payfast ? 'success' : 'default'">
                <template #icon><component :is="offered.payfast ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.payfast ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.payfast" :loading="!!flagSaving.payfast" @change="saveMethodFlag('payfast')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.payfast" type="info" show-icon style="margin-bottom: 20px"
            message="PayFast is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering PayFast until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="PAYFAST MERCHANT ID">
              <a-input v-model:value="payfast.merchant_id">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="PAYFAST MERCHANT KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="payfast.merchant_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="PAYFAST PASSPHRASE" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="payfast.passphrase" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="Sandbox (test mode)" extra="ON uses sandbox.payfast.co.za; turn OFF to charge real cards on www.payfast.co.za.">
              <a-switch v-model:checked="payfast.sandbox" />
            </a-form-item>
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                Hosted checkout (ZAR). Payment confirmation arrives on the ITN URL below — it is passed automatically with every payment, no dashboard setup needed:
                <a-typography-text code copyable>{{ storeBase }}/payfast/itn</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="payfastSaving" @click="savePayfast">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="payfast.merchant_id"
              title="Remove the stored PayFast credentials? The checkout stops offering PayFast."
              @confirm="clearPayfast"
            >
              <a-button danger :loading="payfastSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== LigdiCash ============================== -->
        <a-tab-pane key="ligdicash">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.ligdicash }"></span>
              <WalletOutlined /> LigdiCash
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #f39c12; flex: 0 0 auto">
                <template #icon><WalletOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">LigdiCash <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.ligdicash ? 'success' : 'default'">
                <template #icon><component :is="offered.ligdicash ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.ligdicash ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.ligdicash" :loading="!!flagSaving.ligdicash" @change="saveMethodFlag('ligdicash')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.ligdicash" type="info" show-icon style="margin-bottom: 20px"
            message="LigdiCash is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering LigdiCash until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="LIGDICASH API KEY">
              <a-input v-model:value="ligdicash.api_key">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="LIGDICASH AUTH TOKEN" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="ligdicash.auth_token" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                Hosted checkout for mobile money in West Africa (XOF). The callback URL below is sent with every invoice and every callback is re-verified server-side:
                <a-typography-text code copyable>{{ storeBase }}/ligdicash/callback</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="ligdicashSaving" @click="saveLigdicash">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="ligdicash.api_key"
              title="Remove the stored LigdiCash credentials? The checkout stops offering LigdiCash."
              @confirm="clearLigdicash"
            >
              <a-button danger :loading="ligdicashSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Dodo Payments ============================== -->
        <a-tab-pane key="dodo">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.dodo }"></span>
              <GlobalOutlined /> Dodo Payments
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #111827; flex: 0 0 auto">
                <template #icon><GlobalOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Dodo Payments <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.dodo ? 'success' : 'default'">
                <template #icon><component :is="offered.dodo ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.dodo ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.dodo" :loading="!!flagSaving.dodo" @change="saveMethodFlag('dodo')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.dodo" type="info" show-icon style="margin-bottom: 20px"
            message="Dodo Payments is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Dodo Payments until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="DODO PRODUCT ID" extra="A one-time “Pay What You Want” product created in your Dodo dashboard; the order total is passed as its price.">
              <a-input v-model:value="dodo.product_id" placeholder="pdt_xxxxxxxx">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="DODO API KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="dodo.api_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="DODO WEBHOOK SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="dodo.webhook_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><ApiOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="Test mode" extra="ON uses test.dodopayments.com; turn OFF for live payments on live.dodopayments.com.">
              <a-switch v-model:checked="dodo.test_mode" />
            </a-form-item>
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                Merchant-of-record hosted checkout. Register this webhook URL (Standard Webhooks signature) in your Dodo dashboard with the secret above:
                <a-typography-text code copyable>{{ storeBase }}/dodo/webhook</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="dodoSaving" @click="saveDodo">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="dodo.product_id"
              title="Remove the stored Dodo Payments credentials? The checkout stops offering Dodo."
              @confirm="clearDodo"
            >
              <a-button danger :loading="dodoSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== WaafiPay ============================== -->
        <a-tab-pane key="waafipay">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.waafipay }"></span>
              <MobileOutlined /> WaafiPay
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #0ea5e9; flex: 0 0 auto">
                <template #icon><MobileOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">WaafiPay <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.waafipay ? 'success' : 'default'">
                <template #icon><component :is="offered.waafipay ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.waafipay ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.waafipay" :loading="!!flagSaving.waafipay" @change="saveMethodFlag('waafipay')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.waafipay" type="info" show-icon style="margin-bottom: 20px"
            message="WaafiPay is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering WaafiPay until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="WAAFIPAY MERCHANT UID">
              <a-input v-model:value="waafipay.merchant_uid">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="WAAFIPAY API USER ID" extra="Direct mobile-wallet charge (EVC Plus / Zaad / Sahal): the shopper enters a 252… number and confirms on the handset.">
              <a-input v-model:value="waafipay.api_user_id">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="WAAFIPAY API KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="waafipay.api_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WAAFIPAY STORE ID" extra="Hosted payment page (fallback when no API user is set).">
              <a-input v-model:value="waafipay.store_id">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="WAAFIPAY HPP KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="waafipay.hpp_key" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WAAFIPAY WEBHOOK SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="waafipay.webhook_secret" :placeholder="$t('LeaveBlank')" autocomplete="new-password">
                <template #prefix><ApiOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="Sandbox (test mode)" extra="ON uses sandbox.waafipay.net; turn OFF to charge real wallets on api.waafipay.net.">
              <a-switch v-model:checked="waafipay.test_mode" />
            </a-form-item>
            <a-alert type="info" show-icon style="margin-bottom: 20px">
              <template #message>
                Hosted-page payments confirm through this webhook URL (register it with the secret above):
                <a-typography-text code copyable>{{ storeBase }}/waafipay/webhook</a-typography-text>
              </template>
            </a-alert>
          </a-form>

          <a-space>
            <a-button type="primary" :loading="waafipaySaving" @click="saveWaafipay">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="waafipay.merchant_uid"
              title="Remove the stored WaafiPay credentials? The checkout stops offering WaafiPay."
              @confirm="clearWaafipay"
            >
              <a-button danger :loading="waafipaySaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>


        <a-tab-pane key="paypal">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.paypal }"></span>
              <GlobalOutlined /> PayPal
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #003087; flex: 0 0 auto">
                <template #icon><GlobalOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">PayPal <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.paypal ? 'success' : 'default'">
                <template #icon><component :is="offered.paypal ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.paypal ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.paypal" :loading="!!flagSaving.paypal" @change="saveMethodFlag('paypal')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.paypal" type="info" show-icon style="margin-bottom: 20px"
            message="PayPal is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering PayPal until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="CLIENT ID">
              <a-input v-model:value="paypal.client_id" placeholder="AY...">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="CLIENT SECRET" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="paypal.client_secret" placeholder="EC..." autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WEBHOOK ID" extra="From your PayPal app's webhook settings. Required before webhook events are accepted — without it they are rejected.">
              <a-input v-model:value="paypal.webhook_id" placeholder="8SF...">
                <template #prefix><ApiOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SANDBOX MODE" extra="On = api-m.sandbox.paypal.com with your sandbox app credentials.">
              <a-switch v-model:checked="paypal.test_mode" />
            </a-form-item>
            <a-form-item label="WEBHOOK URL" extra="Add this to your PayPal dashboard. It is the server-to-server backstop that settles an order when the buyer's browser never makes it back.">
              <a-typography-text code copyable>{{ storeBase }}/api/store/webhooks/paypal</a-typography-text>
            </a-form-item>

            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Hosted PayPal checkout (Orders API v2). The buyer approves on PayPal and the payment is captured server-side when they return — an abandoned approval never marks the order paid."
            />

          </a-form>

          <a-space>
            <a-button type="primary" :loading="paypalSaving" @click="savePaypal">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="paypal.configured"
              title="Remove the stored PayPal credentials? The checkout stops offering PayPal."
              @confirm="clearPaypal"
            >
              <a-button danger :loading="paypalSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <a-tab-pane key="paystack">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.paystack }"></span>
              <WalletOutlined /> Paystack
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #011b33; flex: 0 0 auto">
                <template #icon><WalletOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Paystack <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.paystack ? 'success' : 'default'">
                <template #icon><component :is="offered.paystack ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.paystack ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.paystack" :loading="!!flagSaving.paystack" @change="saveMethodFlag('paystack')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.paystack" type="info" show-icon style="margin-bottom: 20px"
            message="Paystack is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Paystack until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="PUBLIC KEY">
              <a-input v-model:value="paystack.public_key" placeholder="pk_test_... or pk_live_...">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SECRET KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="paystack.secret_key" placeholder="sk_test_... or sk_live_..." autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WEBHOOK URL" extra="Add this to your Paystack dashboard. It is the server-to-server backstop that settles an order when the buyer's browser never makes it back.">
              <a-typography-text code copyable>{{ storeBase }}/api/store/webhooks/paystack</a-typography-text>
            </a-form-item>

            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Hosted Paystack checkout for cards, bank transfer and mobile money across Africa. Test vs live is decided by the key pair, so there is no sandbox switch."
            />

          </a-form>

          <a-space>
            <a-button type="primary" :loading="paystackSaving" @click="savePaystack">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="paystack.configured"
              title="Remove the stored Paystack credentials? The checkout stops offering Paystack."
              @confirm="clearPaystack"
            >
              <a-button danger :loading="paystackSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <a-tab-pane key="flutterwave">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': offered.flutterwave }"></span>
              <GlobalOutlined /> Flutterwave
            </span>
          </template>

          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #f5a623; flex: 0 0 auto">
                <template #icon><GlobalOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">Flutterwave <a-tag color="blue" style="margin-left: 4px">Online Store</a-tag></div>
                <div class="pg-head__sub">Used by the online store checkout only — offered while switched on and credentials are stored</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-tag :color="offered.flutterwave ? 'success' : 'default'">
                <template #icon><component :is="offered.flutterwave ? CheckCircleFilled : StopOutlined" /></template>
                {{ offered.flutterwave ? 'Enabled' : 'Disabled' }}
              </a-tag>
              <a-switch v-model:checked="methodFlags.flutterwave" :loading="!!flagSaving.flutterwave" @change="saveMethodFlag('flutterwave')" />
            </div>
          </div>

          <a-divider style="margin: 16px 0 20px" />

          <!-- Switched off keeps the credentials; only the checkout stops
               offering the gateway. -->
          <a-alert
            v-if="!methodFlags.flutterwave" type="info" show-icon style="margin-bottom: 20px"
            message="Flutterwave is switched off"
            description="Your credentials are kept — the online-store checkout simply stops offering Flutterwave until you switch it back on."
          />

          <a-form layout="vertical" class="pg-form">
            <a-form-item label="PUBLIC KEY">
              <a-input v-model:value="flutterwave.public_key" placeholder="FLWPUBK_TEST-... or FLWPUBK-...">
                <template #prefix><KeyOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input>
            </a-form-item>
            <a-form-item label="SECRET KEY" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="flutterwave.secret_key" placeholder="FLWSECK_TEST-... or FLWSECK-..." autocomplete="new-password">
                <template #prefix><LockOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="SECRET HASH" :extra="$t('LeaveBlank')">
              <a-input-password v-model:value="flutterwave.secret_hash" placeholder="Your webhook Secret Hash" autocomplete="new-password">
                <template #prefix><SafetyCertificateOutlined style="color: rgba(var(--ink-rgb), 0.25)" /></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="WEBHOOK URL" extra="Add this to your Flutterwave dashboard. It is the server-to-server backstop that settles an order when the buyer's browser never makes it back.">
              <a-typography-text code copyable>{{ storeBase }}/api/store/webhooks/flutterwave</a-typography-text>
            </a-form-item>

            <a-alert
              type="info" show-icon style="margin-bottom: 20px"
              message="Hosted Flutterwave v3 Standard checkout. Test vs live is decided by the key pair (FLWSECK_TEST- vs FLWSECK-), so there is no sandbox switch."
            />
            <a-alert
              type="warning" show-icon style="margin-bottom: 20px"
              message="Set the same Secret Hash in your Flutterwave dashboard under Settings → Webhooks. Without it, webhook events are rejected and only the browser return leg can settle an order."
            />

          </a-form>

          <a-space>
            <a-button type="primary" :loading="flutterwaveSaving" @click="saveFlutterwave">
              <template #icon><SaveOutlined /></template>
              {{ $t('submit') }}
            </a-button>
            <a-popconfirm
              v-if="flutterwave.configured"
              title="Remove the stored Flutterwave credentials? The checkout stops offering Flutterwave."
              @confirm="clearFlutterwave"
            >
              <a-button danger :loading="flutterwaveSaving">{{ $t('RemoveKeys') }}</a-button>
            </a-popconfirm>
          </a-space>
        </a-tab-pane>

        <!-- ============================== Manual / offline ============================== -->
        <a-tab-pane key="manual">
          <template #tab>
            <span class="pg-tab">
              <span class="pg-tab__dot" :class="{ 'pg-tab__dot--on': anyOfflineEnabled }"></span>
              <DollarOutlined /> {{ $t('OfflinePayments') }}
            </span>
          </template>

          <a-alert
            type="info" show-icon style="margin-bottom: 20px"
            :message="$t('OfflinePaymentsIntro')"
          />

          <!-- Cash on delivery -->
          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #52c41a; flex: 0 0 auto">
                <template #icon><DollarOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">{{ $t('CashOnDelivery') }}</div>
                <div class="pg-head__sub">{{ $t('CashOnDeliveryDesc') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-switch v-model:checked="flags.cod" />
            </div>
          </div>

          <a-divider />

          <!-- Mobile money -->
          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #1677ff; flex: 0 0 auto">
                <template #icon><MobileOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">{{ $t('MobileMoney') }}</div>
                <div class="pg-head__sub">{{ $t('MobileMoneyDesc') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-switch v-model:checked="flags.mobile_money" />
            </div>
          </div>

          <a-divider />

          <!-- GCash -->
          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #0075c9; flex: 0 0 auto">
                <template #icon><MobileOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">{{ $t('GCash') }}</div>
                <div class="pg-head__sub">{{ $t('GCashDesc') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-switch v-model:checked="manual.gcash_enabled" />
            </div>
          </div>

          <a-form v-if="manual.gcash_enabled" layout="vertical" class="pg-form" style="margin-top: 16px">
            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('AccountName')">
                  <a-input v-model:value="manual.gcash_account_name" :maxlength="150" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('MobileNumber')">
                  <a-input v-model:value="manual.gcash_account_number" :maxlength="60" placeholder="09XX XXX XXXX" />
                </a-form-item>
              </a-col>
            </a-row>
            <a-form-item :label="$t('Instructions')" :extra="$t('OfflineInstructionsHint')">
              <a-textarea v-model:value="manual.gcash_instructions" :rows="3" :maxlength="1000" show-count />
            </a-form-item>
            <a-form-item :label="$t('QrCode')">
              <a-upload
                :file-list="gcashQrList"
                :before-upload="beforeQrUpload"
                accept="image/png,image/jpeg,image/webp"
                list-type="picture"
                @remove="removeQr"
              >
                <a-button v-if="!gcashQrList.length">
                  <template #icon><UploadOutlined /></template>
                  {{ $t('Upload') }}
                </a-button>
              </a-upload>
              <img v-if="!gcashQrList.length && manual.gcash_qr_url" :src="manual.gcash_qr_url" class="pg-qr" alt="GCash QR" />
            </a-form-item>
          </a-form>

          <a-divider />

          <!-- Bank transfer -->
          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #4c5fd7; flex: 0 0 auto">
                <template #icon><BankOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">{{ $t('BankTransfer') }}</div>
                <div class="pg-head__sub">{{ $t('BankTransferDesc') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-switch v-model:checked="manual.bank_enabled" />
            </div>
          </div>

          <a-form v-if="manual.bank_enabled" layout="vertical" class="pg-form" style="margin-top: 16px">
            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('BankName')">
                  <a-input v-model:value="manual.bank_name" :maxlength="150" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('Branch')">
                  <a-input v-model:value="manual.bank_branch" :maxlength="150" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('AccountName')">
                  <a-input v-model:value="manual.bank_account_name" :maxlength="150" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('AccountNumber')">
                  <a-input v-model:value="manual.bank_account_number" :maxlength="60" />
                </a-form-item>
              </a-col>
            </a-row>
            <a-form-item :label="$t('Instructions')" :extra="$t('OfflineInstructionsHint')">
              <a-textarea v-model:value="manual.bank_instructions" :rows="3" :maxlength="1000" show-count />
            </a-form-item>
          </a-form>

          <a-divider />

          <!-- Cash on pickup -->
          <div class="pg-head">
            <div class="pg-head__left">
              <a-avatar :size="52" style="background: #f7971e; flex: 0 0 auto">
                <template #icon><ShopOutlined /></template>
              </a-avatar>
              <div>
                <div class="pg-head__title">{{ $t('CashOnPickup') }}</div>
                <div class="pg-head__sub">{{ $t('CashOnPickupDesc') }}</div>
              </div>
            </div>
            <div class="pg-head__right">
              <a-switch v-model:checked="manual.pickup_enabled" />
            </div>
          </div>

          <div v-if="manual.pickup_enabled" style="margin-top: 16px">
            <a-form layout="vertical" class="pg-form">
              <a-form-item :label="$t('Instructions')" :extra="$t('OfflineInstructionsHint')">
                <a-textarea v-model:value="manual.pickup_instructions" :rows="2" :maxlength="1000" show-count />
              </a-form-item>
            </a-form>

            <div class="pg-head__title" style="margin-top: 8px">{{ $t('PickupBranches') }}</div>
            <div class="pg-head__sub" style="margin-bottom: 12px">{{ $t('PickupBranchesHint') }}</div>

            <a-alert
              v-if="!branches.length"
              type="warning" show-icon style="margin-bottom: 12px"
              :message="$t('NoStoreWarehouses')"
            />

            <a-table
              v-else
              :columns="branchColumns"
              :data-source="branches"
              :pagination="false"
              size="small"
              row-key="warehouse_id"
              :scroll="{ x: 'max-content' }"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'active'">
                  <a-switch v-model:checked="record.active" size="small" />
                </template>
                <template v-else-if="column.key === 'address'">
                  <a-input v-model:value="record.address" :maxlength="255" :placeholder="record.city || ''" />
                </template>
                <template v-else-if="column.key === 'hours'">
                  <a-input v-model:value="record.hours" :maxlength="191" placeholder="Mon-Sat 9:00-18:00" />
                </template>
                <template v-else-if="column.key === 'contact'">
                  <a-input v-model:value="record.contact" :maxlength="100" />
                </template>
              </template>
            </a-table>
          </div>

          <a-alert
            v-if="!anyOfflineEnabled" type="warning" show-icon style="margin-top: 20px"
            message="No offline method is offered"
            description="Shoppers can only pay through the gateways whose credentials are stored in the other tabs."
          />

          <a-button type="primary" :loading="manualSaving" style="margin-top: 20px" @click="saveManual">
            <template #icon><SaveOutlined /></template>
            {{ $t('submit') }}
          </a-button>
        </a-tab-pane>
      </a-tabs>

    </a-card>

    </template>
  </div>
</template>

<script setup>
/**
 * Online-store payment gateway. Credentials live on the tenant
 * payment_settings row: GET get_payment_gateway → {gateway:{…}} (secrets come
 * back blank), POST payment_gateway with one gateway's fields (blank secret =
 * keep, <gateway>_deleted:true = wipe). The per-method switches (cards, cash
 * on delivery, mobile money) live on the store settings row
 * (admin/store/settings → payment_*_enabled).
 */
import { ref, reactive, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import {
  CreditCardOutlined, CheckCircleFilled, StopOutlined, KeyOutlined, LockOutlined, SaveOutlined, IdcardOutlined,
  DollarOutlined, MobileOutlined, WalletOutlined, DollarCircleOutlined, BankOutlined, ApiOutlined,
  GlobalOutlined, ThunderboltOutlined, ShopOutlined, UploadOutlined, SafetyCertificateOutlined,
} from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';

const { t } = useI18n();

// Rendered from two routes: /store/payment-gateway ('store', default) and
// /settings/payment-gateway ('settings' — only the breadcrumb differs).
defineProps({ context: { type: String, default: 'store' } });

const loading = ref(true);
const gatewayTab = ref('stripe');
const saving = ref(false);
// `enabled` = credentials are stored; `stripeEnabled` = the admin offers cards
// at checkout. Keeping them apart means switching cards off never wipes keys.
const enabled = ref(false);
const stripeEnabled = ref(true);
const gateway = ref({ stripe_key: '', stripe_secret: '' });
const stripeOffered = computed(() => stripeEnabled.value && !!gateway.value.stripe_key);

// Method on/off flags. The store-settings endpoint takes partial payloads, so
// these switches are saved on their own.
const flags = reactive({ cod: true, mobile_money: true });
const manualSaving = ref(false);

/**
 * Manual (offline) methods: GCash, bank transfer and cash on pickup. These
 * carry the account details the shopper pays to; GCash and bank transfer also
 * ask the shopper for a screenshot + reference number, verified per order.
 */
const manual = reactive({
  gcash_enabled: false,
  gcash_account_name: '',
  gcash_account_number: '',
  gcash_instructions: '',
  gcash_qr_url: '',
  bank_enabled: false,
  bank_name: '',
  bank_account_name: '',
  bank_account_number: '',
  bank_branch: '',
  bank_instructions: '',
  pickup_enabled: false,
  pickup_instructions: '',
});
const gcashQrList = ref([]);
const gcashQrFile = ref(null);
const branches = ref([]);

/** Lights the Offline Payments tab dot when any offline method is offered. */
const anyOfflineEnabled = computed(() =>
  flags.cod || flags.mobile_money
  || manual.gcash_enabled || manual.bank_enabled || manual.pickup_enabled);

const branchColumns = computed(() => [
  { title: t('Offer'), key: 'active', width: 80, align: 'center' },
  { title: t('warehouse'), dataIndex: 'name', key: 'name', width: 180 },
  { title: t('Address'), key: 'address', width: 260 },
  { title: t('PickupHours'), key: 'hours', width: 200 },
  { title: t('Phone'), key: 'contact', width: 160 },
]);

/** Hold the picked QR locally; it uploads with the rest of the tab. */
function beforeQrUpload(file) {
  gcashQrFile.value = file;
  gcashQrList.value = [{ uid: '-1', name: file.name, status: 'done', url: URL.createObjectURL(file) }];
  return false;
}
function removeQr() {
  gcashQrFile.value = null;
  gcashQrList.value = [];
  return true;
}

/** The offline switches live on the store settings row (payment_*_enabled). */
async function saveManual() {
  manualSaving.value = true;
  try {
    // Only this tab's own fields — echoing unrelated store settings back made
    // this save fail with a 422 on any tenant whose default currency is unset.
    const body = {
      payment_cod_enabled: flags.cod ? 1 : 0,
      payment_mobile_money_enabled: flags.mobile_money ? 1 : 0,
      payment_gcash_enabled: manual.gcash_enabled ? 1 : 0,
      gcash_account_name: manual.gcash_account_name || '',
      gcash_account_number: manual.gcash_account_number || '',
      gcash_instructions: manual.gcash_instructions || '',
      payment_bank_transfer_enabled: manual.bank_enabled ? 1 : 0,
      bank_name: manual.bank_name || '',
      bank_account_name: manual.bank_account_name || '',
      bank_account_number: manual.bank_account_number || '',
      bank_branch: manual.bank_branch || '',
      bank_instructions: manual.bank_instructions || '',
      payment_cash_on_pickup_enabled: manual.pickup_enabled ? 1 : 0,
      pickup_instructions: manual.pickup_instructions || '',
    };

    if (gcashQrFile.value) {
      // A file forces multipart; nulls would arrive as the string "null".
      const fd = new FormData();
      Object.entries(body).forEach(([k, v]) => {
        if (v !== null && v !== undefined) fd.append(k, v);
      });
      fd.append('gcash_qr', gcashQrFile.value);
      await http.postForm('admin/store/settings', fd);
      gcashQrFile.value = null;
    } else {
      await http.post('admin/store/settings', body);
    }

    // Branch details ride on their own endpoint (one row per warehouse).
    if (branches.value.length) {
      await http.post('store/pickup-branches', {
        branches: branches.value.map(b => ({
          warehouse_id: b.warehouse_id,
          active: !!b.active,
          address: b.address || null,
          hours: b.hours || null,
          contact: b.contact || null,
          notes: b.notes || null,
          sort_order: b.sort_order || 0,
        })),
      });
    }

    await loadManual();
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    manualSaving.value = false;
  }
}

/** Re-read the offline config + branch rows after a save. */
async function loadManual() {
  const [store, br] = await Promise.all([
    http.get('admin/store/settings').catch(() => null),
    http.get('store/pickup-branches').catch(() => null),
  ]);
  applyManualSettings(store?.settings || {});
  branches.value = (br?.branches || []).map(b => ({ ...b }));
}

function applyManualSettings(s) {
  flags.cod = s.payment_cod_enabled == null ? true : !!Number(s.payment_cod_enabled);
  flags.mobile_money = s.payment_mobile_money_enabled == null ? true : !!Number(s.payment_mobile_money_enabled);

  manual.gcash_enabled = !!Number(s.payment_gcash_enabled);
  manual.gcash_account_name = s.gcash_account_name || '';
  manual.gcash_account_number = s.gcash_account_number || '';
  manual.gcash_instructions = s.gcash_instructions || '';
  manual.gcash_qr_url = s.gcash_qr_path ? `/${String(s.gcash_qr_path).replace(/^\/+/, '')}` : '';
  manual.bank_enabled = !!Number(s.payment_bank_transfer_enabled);
  manual.bank_name = s.bank_name || '';
  manual.bank_account_name = s.bank_account_name || '';
  manual.bank_account_number = s.bank_account_number || '';
  manual.bank_branch = s.bank_branch || '';
  manual.bank_instructions = s.bank_instructions || '';
  manual.pickup_enabled = !!Number(s.payment_cash_on_pickup_enabled);
  manual.pickup_instructions = s.pickup_instructions || '';
  gcashQrList.value = [];
}

// Razorpay — stored via the payment_gateway endpoint (tenant payment_settings
// row), like Stripe: offered at checkout as soon as both keys are stored.
// The secret is write-only: blank means "keep the stored value".
const razorpay = reactive({ key_id: '', key_secret: '' });
const razorpaySaving = ref(false);

// PayFast / LigdiCash / Dodo Payments / WaafiPay — same storage + rules.
const payfast = reactive({ merchant_id: '', merchant_key: '', passphrase: '', sandbox: true });
const payfastSaving = ref(false);
const ligdicash = reactive({ api_key: '', auth_token: '' });
const ligdicashSaving = ref(false);
const dodo = reactive({ product_id: '', api_key: '', webhook_secret: '', test_mode: true });
const dodoSaving = ref(false);
const waafipay = reactive({ merchant_uid: '', store_id: '', api_user_id: '', hpp_key: '', api_key: '', webhook_secret: '', test_mode: true });
const waafipaySaving = ref(false);

// Storefront base URL (default /online_store or the tenant's custom path) —
// the gateway callback URLs shown in the tabs live under it.
const storeBase = ref(`${window.location.origin}/online_store`);

// bKash / SSLCommerz — stored via the payment_gateway endpoint (tenant
// payment_settings row), like Stripe: a gateway is offered at checkout as
// soon as its credentials are stored, and "remove keys" turns it off.
// Secrets are write-only: blank means "keep the stored value".
const bkash = reactive({ app_key: '', app_secret: '', username: '', password: '', test_mode: true });
const bkashSaving = ref(false);
const sslcommerz = reactive({ store_id: '', store_password: '', test_mode: true });
const sslcommerzSaving = ref(false);
const sslcommerzIpnUrl = `${window.location.origin}/api/store/sslcommerz/ipn`;
const paymob = reactive({ region: '', public_key: '', secret_key: '', api_key: '', integration_id: '', hmac_secret: '', currency: 'EGP' });
const paymobSaving = ref(false);
const paymobWebhookUrl = `${window.location.origin}/api/store/paymob/webhook`;
const myfatoorah = reactive({ api_token: '', country: '', currency: 'KWD', test_mode: true, configured: false });
const myfatoorahSaving = ref(false);
const moyasar = reactive({ secret_key: '', currency: 'SAR', configured: false });
const moyasarSaving = ref(false);
const xpay = reactive({
  account_id: '', publishable_key: '', secret_key: '', hmac_secret: '', webhook_hmac_secret: '',
  currency: 'PKR', test_mode: true, configured: false, webhook_secret_set: false,
});
const xpaySaving = ref(false);
const xpayWebhookUrl = `${window.location.origin}/api/store/xpay/webhook`;

// Per-gateway "offer at checkout" switches, the same idea as the card switch:
// they live on the store settings row (payment_<gateway>_enabled), so turning a
// gateway off never touches its stored credentials. Unlike the card switch they
// save the moment they are flipped — the tab's Save button is for credentials.
const methodFlags = reactive({
  razorpay: true,
  bkash: true,
  sslcommerz: true,
  paymob: true,
  myfatoorah: true,
  moyasar: true,
  xpay: true,
  payfast: true,
  ligdicash: true,
  dodo: true,
  waafipay: true,
  paypal: true,
  paystack: true,
  flutterwave: true,
});
const flagSaving = reactive({});

/** Credentials stored for each gateway (what the tabs below fill in). */
const configured = computed(() => ({
  razorpay: !!razorpay.key_id,
  bkash: !!bkash.app_key,
  sslcommerz: !!sslcommerz.store_id,
  paymob: !!paymob.public_key,
  myfatoorah: !!myfatoorah.configured,
  moyasar: !!moyasar.configured,
  xpay: !!xpay.configured,
  payfast: !!payfast.merchant_id,
  ligdicash: !!ligdicash.api_key,
  dodo: !!dodo.product_id,
  waafipay: !!waafipay.merchant_uid,
  paypal: !!paypal.configured,
  paystack: !!paystack.configured,
  flutterwave: !!flutterwave.configured,
}));

/** A gateway is offered at checkout when it is switched on AND configured. */
const offered = computed(() => {
  const c = configured.value;
  return Object.fromEntries(Object.keys(c).map((k) => [k, c[k] && methodFlags[k]]));
});

/**
 * Save one gateway switch on its own. The store-settings endpoint takes partial
 * payloads; a role that may edit gateways but not store settings gets the same
 * warning the card switch uses, and the switch snaps back.
 */
async function saveMethodFlag(key) {
  flagSaving[key] = true;
  try {
    await http.post('admin/store/settings', { [`payment_${key}_enabled`]: methodFlags[key] ? 1 : 0 });
    message.success(t('Successfully_Updated'));
  } catch (e) {
    methodFlags[key] = !methodFlags[key];
    message.warning(t('CardSwitchNotSaved'));
  } finally {
    flagSaving[key] = false;
  }
}

/** Same for the card switch; save() keeps sending the flag along with the keys. */
async function saveStripeFlag() {
  flagSaving.stripe = true;
  try {
    await http.post('admin/store/settings', { payment_stripe_enabled: stripeEnabled.value ? 1 : 0 });
    message.success(t('Successfully_Updated'));
  } catch (e) {
    stripeEnabled.value = !stripeEnabled.value;
    message.warning(t('CardSwitchNotSaved'));
  } finally {
    flagSaving.stripe = false;
  }
}

async function saveBkash() {
  if (!bkash.app_key.trim() || !bkash.username.trim()) {
    message.warning('bKash App Key and Username are required');
    return;
  }
  bkashSaving.value = true;
  try {
    await http.post('payment_gateway', {
      bkash_deleted: false,
      bkash_app_key: bkash.app_key.trim(),
      bkash_username: bkash.username.trim(),
      // Blank secrets → backend keeps the stored ones.
      bkash_app_secret: bkash.app_secret.trim() || '',
      bkash_password: bkash.password.trim() || '',
      bkash_test_mode: !!bkash.test_mode,
    });
    bkash.app_secret = '';
    bkash.password = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    bkashSaving.value = false;
  }
}

async function clearBkash() {
  bkashSaving.value = true;
  try {
    await http.post('payment_gateway', { bkash_deleted: true });
    bkash.app_key = '';
    bkash.app_secret = '';
    bkash.username = '';
    bkash.password = '';
    bkash.test_mode = true;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    bkashSaving.value = false;
  }
}

async function saveSslcommerz() {
  if (!sslcommerz.store_id.trim()) {
    message.warning('SSLCommerz Store ID is required');
    return;
  }
  sslcommerzSaving.value = true;
  try {
    await http.post('payment_gateway', {
      sslcommerz_deleted: false,
      sslcommerz_store_id: sslcommerz.store_id.trim(),
      // Blank password → backend keeps the stored one.
      sslcommerz_store_password: sslcommerz.store_password.trim() || '',
      sslcommerz_test_mode: !!sslcommerz.test_mode,
    });
    sslcommerz.store_password = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    sslcommerzSaving.value = false;
  }
}

async function clearSslcommerz() {
  sslcommerzSaving.value = true;
  try {
    await http.post('payment_gateway', { sslcommerz_deleted: true });
    sslcommerz.store_id = '';
    sslcommerz.store_password = '';
    sslcommerz.test_mode = true;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    sslcommerzSaving.value = false;
  }
}

async function savePaymob() {
  if (!paymob.public_key.trim() || !paymob.integration_id.trim()) {
    message.warning('Paymob Public Key and Integration ID are required');
    return;
  }
  paymobSaving.value = true;
  try {
    await http.post('payment_gateway', {
      paymob_deleted: false,
      paymob_region: paymob.region.trim().toLowerCase(),
      paymob_public_key: paymob.public_key.trim(),
      paymob_integration_id: paymob.integration_id.trim(),
      paymob_currency: paymob.currency.trim().toUpperCase() || 'EGP',
      // Blank secrets → backend keeps the stored ones.
      paymob_secret_key: paymob.secret_key.trim() || '',
      paymob_api_key: paymob.api_key.trim() || '',
      paymob_hmac_secret: paymob.hmac_secret.trim() || '',
    });
    paymob.secret_key = '';
    paymob.api_key = '';
    paymob.hmac_secret = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paymobSaving.value = false;
  }
}

async function clearPaymob() {
  paymobSaving.value = true;
  try {
    await http.post('payment_gateway', { paymob_deleted: true });
    paymob.region = '';
    paymob.public_key = '';
    paymob.secret_key = '';
    paymob.api_key = '';
    paymob.integration_id = '';
    paymob.hmac_secret = '';
    paymob.currency = 'EGP';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paymobSaving.value = false;
  }
}

async function saveMyfatoorah() {
  if (!myfatoorah.configured && !myfatoorah.api_token.trim()) {
    message.warning('MyFatoorah API Token is required');
    return;
  }
  myfatoorahSaving.value = true;
  try {
    await http.post('payment_gateway', {
      myfatoorah_deleted: false,
      myfatoorah_country: myfatoorah.country.trim().toUpperCase(),
      myfatoorah_currency: myfatoorah.currency.trim().toUpperCase() || 'KWD',
      // Blank token → backend keeps the stored one.
      myfatoorah_api_token: myfatoorah.api_token.trim() || '',
      myfatoorah_test_mode: !!myfatoorah.test_mode,
    });
    if (myfatoorah.api_token.trim()) myfatoorah.configured = true;
    myfatoorah.api_token = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    myfatoorahSaving.value = false;
  }
}

async function clearMyfatoorah() {
  myfatoorahSaving.value = true;
  try {
    await http.post('payment_gateway', { myfatoorah_deleted: true });
    myfatoorah.api_token = '';
    myfatoorah.country = '';
    myfatoorah.currency = 'KWD';
    myfatoorah.test_mode = true;
    myfatoorah.configured = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    myfatoorahSaving.value = false;
  }
}

async function saveMoyasar() {
  if (!moyasar.configured && !moyasar.secret_key.trim()) {
    message.warning('Moyasar Secret Key is required');
    return;
  }
  moyasarSaving.value = true;
  try {
    await http.post('payment_gateway', {
      moyasar_deleted: false,
      moyasar_currency: moyasar.currency.trim().toUpperCase() || 'SAR',
      // Blank key → backend keeps the stored one.
      moyasar_secret_key: moyasar.secret_key.trim() || '',
    });
    if (moyasar.secret_key.trim()) moyasar.configured = true;
    moyasar.secret_key = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    moyasarSaving.value = false;
  }
}

// PayPal / Paystack / Flutterwave — hosted redirect gateways stored on the
// tenant payment_settings row. Secrets are write-only: the API never echoes
// them back, and `configured` says whether one is stored.
const paypal = reactive({ client_id: '', client_secret: '', webhook_id: '', test_mode: true, configured: false });
const paypalSaving = ref(false);
const paystack = reactive({ public_key: '', secret_key: '', configured: false });
const paystackSaving = ref(false);
const flutterwave = reactive({ public_key: '', secret_key: '', secret_hash: '', configured: false, hash_set: false });
const flutterwaveSaving = ref(false);

async function savePaypal() {
  if (!paypal.client_id.trim()) {
    message.warning('PayPal Client ID is required');
    return;
  }
  if (!paypal.configured && !paypal.client_secret.trim()) {
    message.warning('PayPal Client Secret is required');
    return;
  }
  paypalSaving.value = true;
  try {
    await http.post('payment_gateway', {
      paypal_deleted: false,
      paypal_client_id: paypal.client_id.trim(),
      paypal_webhook_id: paypal.webhook_id.trim(),
      paypal_test_mode: paypal.test_mode,
      // Blank secret → backend keeps the stored one.
      paypal_client_secret: paypal.client_secret.trim() || '',
    });
    if (paypal.client_secret.trim()) paypal.configured = true;
    paypal.client_secret = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paypalSaving.value = false;
  }
}
async function clearPaypal() {
  paypalSaving.value = true;
  try {
    await http.post('payment_gateway', { paypal_deleted: true });
    paypal.client_id = '';
    paypal.client_secret = '';
    paypal.webhook_id = '';
    paypal.test_mode = true;
    paypal.configured = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paypalSaving.value = false;
  }
}

async function savePaystack() {
  if (!paystack.public_key.trim()) {
    message.warning('Paystack Public Key is required');
    return;
  }
  if (!paystack.configured && !paystack.secret_key.trim()) {
    message.warning('Paystack Secret Key is required');
    return;
  }
  paystackSaving.value = true;
  try {
    await http.post('payment_gateway', {
      paystack_deleted: false,
      paystack_public_key: paystack.public_key.trim(),
      paystack_secret_key: paystack.secret_key.trim() || '',
    });
    if (paystack.secret_key.trim()) paystack.configured = true;
    paystack.secret_key = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paystackSaving.value = false;
  }
}
async function clearPaystack() {
  paystackSaving.value = true;
  try {
    await http.post('payment_gateway', { paystack_deleted: true });
    paystack.public_key = '';
    paystack.secret_key = '';
    paystack.configured = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    paystackSaving.value = false;
  }
}

async function saveFlutterwave() {
  if (!flutterwave.public_key.trim()) {
    message.warning('Flutterwave Public Key is required');
    return;
  }
  if (!flutterwave.configured && !flutterwave.secret_key.trim()) {
    message.warning('Flutterwave Secret Key is required');
    return;
  }
  flutterwaveSaving.value = true;
  try {
    await http.post('payment_gateway', {
      flutterwave_deleted: false,
      flutterwave_public_key: flutterwave.public_key.trim(),
      flutterwave_secret_key: flutterwave.secret_key.trim() || '',
      flutterwave_secret_hash: flutterwave.secret_hash.trim() || '',
    });
    if (flutterwave.secret_key.trim()) flutterwave.configured = true;
    if (flutterwave.secret_hash.trim()) flutterwave.hash_set = true;
    flutterwave.secret_key = '';
    flutterwave.secret_hash = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    flutterwaveSaving.value = false;
  }
}
async function clearFlutterwave() {
  flutterwaveSaving.value = true;
  try {
    await http.post('payment_gateway', { flutterwave_deleted: true });
    flutterwave.public_key = '';
    flutterwave.secret_key = '';
    flutterwave.secret_hash = '';
    flutterwave.configured = false;
    flutterwave.hash_set = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    flutterwaveSaving.value = false;
  }
}

async function clearMoyasar() {
  moyasarSaving.value = true;
  try {
    await http.post('payment_gateway', { moyasar_deleted: true });
    moyasar.secret_key = '';
    moyasar.currency = 'SAR';
    moyasar.configured = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    moyasarSaving.value = false;
  }
}

async function saveXpay() {
  if (!xpay.account_id.trim() || !xpay.publishable_key.trim()) {
    message.warning('XPay Account ID and Publishable Key are required');
    return;
  }
  if (!xpay.configured && (!xpay.secret_key.trim() || !xpay.hmac_secret.trim())) {
    message.warning('XPay Secret Key and API HMAC Secret are required');
    return;
  }
  xpaySaving.value = true;
  try {
    await http.post('payment_gateway', {
      xpay_deleted: false,
      xpay_account_id: xpay.account_id.trim(),
      xpay_publishable_key: xpay.publishable_key.trim(),
      xpay_currency: xpay.currency.trim().toUpperCase() || 'PKR',
      xpay_test_mode: xpay.test_mode,
      // Blank secrets → backend keeps the stored ones.
      xpay_secret_key: xpay.secret_key.trim() || '',
      xpay_hmac_secret: xpay.hmac_secret.trim() || '',
      xpay_webhook_hmac_secret: xpay.webhook_hmac_secret.trim() || '',
    });
    if (xpay.secret_key.trim() && xpay.hmac_secret.trim()) xpay.configured = true;
    if (xpay.webhook_hmac_secret.trim()) xpay.webhook_secret_set = true;
    xpay.secret_key = '';
    xpay.hmac_secret = '';
    xpay.webhook_hmac_secret = '';
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    xpaySaving.value = false;
  }
}

async function clearXpay() {
  xpaySaving.value = true;
  try {
    await http.post('payment_gateway', { xpay_deleted: true });
    xpay.account_id = '';
    xpay.publishable_key = '';
    xpay.secret_key = '';
    xpay.hmac_secret = '';
    xpay.webhook_hmac_secret = '';
    xpay.currency = 'PKR';
    xpay.test_mode = true;
    xpay.configured = false;
    xpay.webhook_secret_set = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    xpaySaving.value = false;
  }
}

/**
 * One gateway at a time: POST payment_gateway with that gateway's fields.
 * Blank secrets are sent as '' so the backend keeps the stored ones.
 */
async function postGateway(savingRef, body, after) {
  savingRef.value = true;
  try {
    await http.post('payment_gateway', body);
    if (after) after();
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    savingRef.value = false;
  }
}

function saveRazorpay() {
  if (!razorpay.key_id.trim()) {
    message.warning('Razorpay Key ID is required');
    return;
  }
  return postGateway(razorpaySaving, {
    razorpay_deleted: false,
    razorpay_key_id: razorpay.key_id.trim(),
    razorpay_key_secret: razorpay.key_secret.trim() || '',
  }, () => { razorpay.key_secret = ''; });
}
function clearRazorpay() {
  return postGateway(razorpaySaving, { razorpay_deleted: true }, () => {
    razorpay.key_id = '';
    razorpay.key_secret = '';
  });
}

function savePayfast() {
  if (!payfast.merchant_id.trim()) {
    message.warning('PayFast Merchant ID is required');
    return;
  }
  return postGateway(payfastSaving, {
    payfast_deleted: false,
    payfast_merchant_id: payfast.merchant_id.trim(),
    payfast_merchant_key: payfast.merchant_key.trim() || '',
    payfast_passphrase: payfast.passphrase.trim() || '',
    payfast_sandbox: !!payfast.sandbox,
  }, () => { payfast.merchant_key = ''; payfast.passphrase = ''; });
}
function clearPayfast() {
  return postGateway(payfastSaving, { payfast_deleted: true }, () => {
    payfast.merchant_id = '';
    payfast.merchant_key = '';
    payfast.passphrase = '';
    payfast.sandbox = true;
  });
}

function saveLigdicash() {
  if (!ligdicash.api_key.trim()) {
    message.warning('LigdiCash API Key is required');
    return;
  }
  return postGateway(ligdicashSaving, {
    ligdicash_deleted: false,
    ligdicash_api_key: ligdicash.api_key.trim(),
    ligdicash_auth_token: ligdicash.auth_token.trim() || '',
  }, () => { ligdicash.auth_token = ''; });
}
function clearLigdicash() {
  return postGateway(ligdicashSaving, { ligdicash_deleted: true }, () => {
    ligdicash.api_key = '';
    ligdicash.auth_token = '';
  });
}

function saveDodo() {
  if (!dodo.product_id.trim()) {
    message.warning('Dodo Product ID is required');
    return;
  }
  return postGateway(dodoSaving, {
    dodo_deleted: false,
    dodo_product_id: dodo.product_id.trim(),
    dodo_api_key: dodo.api_key.trim() || '',
    dodo_webhook_secret: dodo.webhook_secret.trim() || '',
    dodo_test_mode: !!dodo.test_mode,
  }, () => { dodo.api_key = ''; dodo.webhook_secret = ''; });
}
function clearDodo() {
  return postGateway(dodoSaving, { dodo_deleted: true }, () => {
    dodo.product_id = '';
    dodo.api_key = '';
    dodo.webhook_secret = '';
    dodo.test_mode = true;
  });
}

function saveWaafipay() {
  if (!waafipay.merchant_uid.trim()) {
    message.warning('WaafiPay Merchant UID is required');
    return;
  }
  return postGateway(waafipaySaving, {
    waafipay_deleted: false,
    waafipay_merchant_uid: waafipay.merchant_uid.trim(),
    waafipay_store_id: waafipay.store_id.trim(),
    waafipay_api_user_id: waafipay.api_user_id.trim(),
    waafipay_hpp_key: waafipay.hpp_key.trim() || '',
    waafipay_api_key: waafipay.api_key.trim() || '',
    waafipay_webhook_secret: waafipay.webhook_secret.trim() || '',
    waafipay_test_mode: !!waafipay.test_mode,
  }, () => { waafipay.hpp_key = ''; waafipay.api_key = ''; waafipay.webhook_secret = ''; });
}
function clearWaafipay() {
  return postGateway(waafipaySaving, { waafipay_deleted: true }, () => {
    Object.assign(waafipay, { merchant_uid: '', store_id: '', api_user_id: '', hpp_key: '', api_key: '', webhook_secret: '', test_mode: true });
  });
}

async function save() {
  saving.value = true;
  try {
    await http.post('payment_gateway', {
      stripe_key: gateway.value.stripe_key,
      // blank secret → null keeps the stored one (leave-blank behaviour).
      stripe_secret: gateway.value.stripe_secret ? gateway.value.stripe_secret : null,
      deleted: false,
    });
    // The offer-cards-at-checkout switch lives on the store settings, which a
    // gateway-only role may not be allowed to write — the keys still save.
    let flagSaved = true;
    try {
      await http.post('admin/store/settings', {
        payment_stripe_enabled: stripeEnabled.value ? 1 : 0,
      });
    } catch (e) {
      flagSaved = false;
    }
    enabled.value = !!gateway.value.stripe_key;
    gateway.value.stripe_secret = '';
    if (flagSaved) {
      message.success(t('Successfully_Updated'));
    } else {
      message.warning(t('CardSwitchNotSaved'));
    }
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

/** Explicitly wipe the stored Stripe credentials (the old off-switch path). */
async function clearStripeKeys() {
  saving.value = true;
  try {
    await http.post('payment_gateway', { deleted: true });
    gateway.value.stripe_key = '';
    gateway.value.stripe_secret = '';
    enabled.value = false;
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  try {
    const [gw, store, br] = await Promise.all([
      http.get('get_payment_gateway'),
      http.get('admin/store/settings').catch(() => null),
      // Cash-on-pickup branch rows; only this tab uses them, so a failure
      // here must not take the whole page down.
      http.get('store/pickup-branches').catch(() => null),
    ]);
    const g = gw?.gateway || {};
    gateway.value = { stripe_key: g.stripe_key || '', stripe_secret: '' };
    enabled.value = !!gateway.value.stripe_key;
    bkash.app_key = g.bkash_app_key || '';
    bkash.username = g.bkash_username || '';
    bkash.test_mode = g.bkash_test_mode == null ? true : !!g.bkash_test_mode;
    sslcommerz.store_id = g.sslcommerz_store_id || '';
    sslcommerz.test_mode = g.sslcommerz_test_mode == null ? true : !!g.sslcommerz_test_mode;
    paymob.region = g.paymob_region || '';
    paymob.public_key = g.paymob_public_key || '';
    paymob.integration_id = g.paymob_integration_id || '';
    paymob.currency = g.paymob_currency || 'EGP';
    myfatoorah.country = g.myfatoorah_country || '';
    myfatoorah.currency = g.myfatoorah_currency || 'KWD';
    myfatoorah.test_mode = g.myfatoorah_test_mode == null ? true : !!g.myfatoorah_test_mode;
    myfatoorah.configured = !!g.myfatoorah_configured;
    moyasar.currency = g.moyasar_currency || 'SAR';
    moyasar.configured = !!g.moyasar_configured;
    xpay.account_id = g.xpay_account_id || '';
    xpay.publishable_key = g.xpay_publishable_key || '';
    xpay.currency = g.xpay_currency || 'PKR';
    xpay.test_mode = g.xpay_test_mode == null ? true : !!g.xpay_test_mode;
    xpay.configured = !!g.xpay_configured;
    xpay.webhook_secret_set = !!g.xpay_webhook_secret_set;
    razorpay.key_id = g.razorpay_key_id || '';
    payfast.merchant_id = g.payfast_merchant_id || '';
    payfast.sandbox = g.payfast_sandbox == null ? true : !!g.payfast_sandbox;
    ligdicash.api_key = g.ligdicash_api_key || '';
    dodo.product_id = g.dodo_product_id || '';
    dodo.test_mode = g.dodo_test_mode == null ? true : !!g.dodo_test_mode;
    waafipay.merchant_uid = g.waafipay_merchant_uid || '';
    waafipay.store_id = g.waafipay_store_id || '';
    waafipay.api_user_id = g.waafipay_api_user_id || '';
    waafipay.test_mode = g.waafipay_test_mode == null ? true : !!g.waafipay_test_mode;
    paypal.client_id = g.paypal_client_id || '';
    paypal.webhook_id = g.paypal_webhook_id || '';
    paypal.test_mode = g.paypal_test_mode == null ? true : !!g.paypal_test_mode;
    paypal.configured = !!g.paypal_configured;
    paystack.public_key = g.paystack_public_key || '';
    paystack.configured = !!g.paystack_configured;
    flutterwave.public_key = g.flutterwave_public_key || '';
    flutterwave.configured = !!g.flutterwave_configured;
    flutterwave.hash_set = !!g.flutterwave_hash_set;

    const s = store?.settings || {};
    stripeEnabled.value = s.payment_stripe_enabled == null ? true : !!Number(s.payment_stripe_enabled);
    // Missing column / fresh tenant reads as ON, like the card switch.
    Object.keys(methodFlags).forEach((k) => {
      const v = s[`payment_${k}_enabled`];
      methodFlags[k] = v == null ? true : !!Number(v);
    });
    // Callback URLs shown in the tabs follow the configured store base path.
    if (s.store_effective_url) storeBase.value = String(s.store_effective_url).replace(/\/+$/, '');
    applyManualSettings(s);
    branches.value = (br?.branches || []).map(b => ({ ...b }));
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.pg-card {
  border: 1px solid var(--line);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}
.pg-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.pg-head__left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.pg-head__title {
  font-size: 18px;
  font-weight: 700;
  line-height: 1.2;
}
.pg-head__sub {
  font-size: 12px;
  color: rgba(var(--ink-rgb), 0.45);
}
.pg-head__right {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pg-form {
  max-width: 520px;
  margin-bottom: 4px;
}

/* Gateway tabs: vertical nav rail on the left (matches Store Settings) with
   provider icon + name + a small status dot that shows at a glance which
   gateways are live without opening their tab. */
.pg-tabs :deep(.ant-tabs-nav) {
  min-width: 190px;
}
.pg-tabs :deep(.ant-tabs-tab) {
  padding: 10px 14px !important;
  margin: 2px 0 !important;
  border-radius: 8px;
  text-align: left;
}
.pg-tabs :deep(.ant-tabs-tab-active) {
  background: rgba(109, 40, 217, 0.08);
}
.pg-tabs :deep(.ant-tabs-content-holder) {
  padding-left: 8px;
}
.pg-tab {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-weight: 600;
}
/* Keep the rail from squeezing the content on small screens. */
@media (max-width: 767px) {
  .pg-tabs :deep(.ant-tabs-nav) {
    min-width: 0;
  }
  .pg-tabs :deep(.ant-tabs-content-holder) {
    padding-left: 0;
  }
}
.pg-tab__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #d9d9d9;
  flex: 0 0 auto;
}
.pg-tab__dot--on {
  background: #52c41a;
  box-shadow: 0 0 0 3px rgba(82, 196, 26, 0.18);
}

/* Offline payments tab */
.pg-qr {
  display: block;
  max-width: 160px;
  margin-top: 8px;
  border-radius: 8px;
  border: 1px solid var(--line);
}
.pm-title {
  font-size: 16px;
  font-weight: 600;
}
.pm-sub {
  font-size: 12px;
  color: rgba(var(--ink-rgb), 0.45);
  margin-bottom: 16px;
}
</style>
