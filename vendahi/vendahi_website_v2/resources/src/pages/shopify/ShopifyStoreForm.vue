<template>
  <a-modal
    :open="open"
    :title="isEdit ? `Edit ${store.name}` : 'Connect a Shopify store'"
    :width="720"
    :confirm-loading="saving"
    :ok-text="isEdit ? $t('submit') : 'Connect'"
    :cancel-text="$t('Cancel')"
    @ok="submit"
    @cancel="close"
  >
    <a-form ref="formRef" :model="form" :rules="rules" layout="vertical">
      <a-tabs v-model:activeKey="tab">
        <!-- ------------------------------------------------ connection -->
        <a-tab-pane key="connection" tab="Connection">
          <a-row :gutter="16">
            <a-col :xs="24" :md="12">
              <a-form-item label="Name *" name="name" extra="How this shop is labelled in the ERP">
                <a-input v-model:value="form.name" placeholder="e.g. Main storefront" allow-clear />
              </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
              <a-form-item label="Shop domain *" name="shop_domain" extra="acme.myshopify.com — the admin URL works too">
                <a-input
                  v-model:value="form.shop_domain" placeholder="acme.myshopify.com"
                  :disabled="isEdit && !allowRebind" allow-clear
                />
              </a-form-item>
            </a-col>
            <a-col :span="24">
              <a-form-item label="App credentials" name="auth_mode" style="margin-bottom: 8px">
                <a-radio-group v-model:value="form.auth_mode" @change="onAuthModeChange">
                  <a-radio value="client_credentials">Client ID + client secret (Dev Dashboard app)</a-radio>
                  <a-radio value="token">Admin API access token (legacy custom app)</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>

            <template v-if="form.auth_mode === 'client_credentials'">
              <a-col :span="24">
                <a-alert type="info" show-icon style="margin-bottom: 12px">
                  <template #message>
                    Since January 2026 Shopify no longer creates custom apps in the admin. Create the app in the
                    <strong>Dev Dashboard</strong>, grant its Admin API scopes, install it on this shop, then copy the
                    Client ID and Client secret from its <em>Settings</em> page. The ERP fetches a fresh access token
                    itself every 24 hours. The app and the shop must belong to the same Shopify organisation.
                  </template>
                </a-alert>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item
                  :label="isEdit && clientPairStored ? 'Client ID' : 'Client ID *'" name="client_id"
                  :extra="isEdit && clientPairStored ? 'Leave blank to keep the stored one.' : ''"
                >
                  <a-input
                    v-model:value="form.client_id"
                    :placeholder="isEdit && store?.client_id ? store.client_id : 'From the app\'s Settings page'"
                    autocomplete="off" allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item
                  :label="isEdit && clientPairStored ? 'Client secret' : 'Client secret *'" name="client_secret"
                  :extra="isEdit && clientPairStored ? 'Leave blank to keep the stored one.' : 'Also signs webhooks, so no separate signing secret is needed.'"
                >
                  <a-input-password
                    v-model:value="form.client_secret"
                    :placeholder="isEdit && store?.has_client_secret ? '•••••••••••••• (unchanged)' : 'shpss_…'"
                    autocomplete="new-password"
                  />
                </a-form-item>
              </a-col>
            </template>

            <a-col v-else :span="24">
              <a-form-item
                :label="isEdit && store?.auth_mode === 'token' ? 'Admin API access token' : 'Admin API access token *'"
                name="access_token"
                :extra="isEdit && store?.auth_mode === 'token'
                  ? 'Leave blank to keep the token already stored.'
                  : 'Only for custom apps created in Shopify admin before 2026 (Apps → Develop apps → API credentials). New apps do not show a token; use the client ID + secret instead.'"
              >
                <a-input-password
                  v-model:value="form.access_token"
                  :placeholder="isEdit && store?.auth_mode === 'token' && store.has_token ? '•••••••••••••• (unchanged)' : 'shpat_…'"
                  autocomplete="new-password"
                />
              </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
              <a-form-item label="API version" name="api_version">
                <a-input v-model:value="form.api_version" placeholder="2024-10" allow-clear />
              </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
              <a-form-item
                label="Webhook signing secret" name="webhook_secret"
                :extra="form.auth_mode === 'client_credentials'
                  ? 'Optional here — the client secret above signs webhooks for a Dev Dashboard app.'
                  : 'Required before webhooks are accepted — unsigned deliveries are refused.'"
              >
                <a-input-password
                  v-model:value="form.webhook_secret"
                  :placeholder="form.clear_webhook_secret ? 'Will be cleared on save' : (store?.has_explicit_webhook_secret ? '•••••••••••••• (unchanged)' : 'Optional')"
                  :disabled="form.clear_webhook_secret"
                  autocomplete="new-password"
                />
                <template v-if="isEdit && store?.has_explicit_webhook_secret">
                  <a-button
                    v-if="!form.clear_webhook_secret" type="link" size="small" danger
                    style="padding: 0; margin-top: 4px" @click="form.clear_webhook_secret = true"
                  >
                    Clear the stored secret
                  </a-button>
                  <a-button
                    v-else type="link" size="small" style="padding: 0; margin-top: 4px"
                    @click="form.clear_webhook_secret = false"
                  >
                    Keep it after all
                  </a-button>
                </template>
              </a-form-item>
            </a-col>

            <a-col v-if="isEdit && !allowRebind" :span="24">
              <a-alert type="info" show-icon banner style="margin-bottom: 12px">
                <template #message>
                  The domain is locked because changing it invalidates every mapping for this shop.
                  <a class="link" @click="allowRebind = true">Change it anyway</a>
                </template>
              </a-alert>
            </a-col>
            <a-col v-else-if="isEdit && allowRebind" :span="24">
              <a-alert
                type="warning" show-icon banner style="margin-bottom: 12px"
                message="Saving a different domain deletes all record mappings for this connection — the stored ids belong to the old shop."
              />
            </a-col>
          </a-row>
        </a-tab-pane>

        <!-- ------------------------------------------------ targets -->
        <a-tab-pane key="targets" tab="Where data lands">
          <a-row :gutter="16">
            <a-col :xs="24" :md="12">
              <a-form-item
                label="ERP warehouse" name="warehouse_id"
                extra="Stock levels and imported orders belong to this warehouse."
              >
                <a-select
                  v-model:value="form.warehouse_id" show-search option-filter-prop="label"
                  :options="warehouseOptions" allow-clear placeholder="Select a warehouse"
                />
              </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
              <a-form-item label="Shopify location" name="location_id">
                <a-select
                  v-model:value="form.location_id" :options="locationOptions" allow-clear
                  :loading="loadingLocations"
                  :placeholder="isEdit ? 'Load locations from Shopify' : 'Available after connecting'"
                  :disabled="!isEdit"
                />
                <a-button
                  v-if="isEdit" type="link" size="small" style="padding: 0; margin-top: 4px"
                  :loading="loadingLocations" @click="loadLocations"
                >
                  Fetch locations from Shopify
                </a-button>
              </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
              <a-form-item label="Price sent to Shopify" name="price_field">
                <a-select v-model:value="form.price_field" :options="PRICE_FIELDS" />
              </a-form-item>
            </a-col>
            <a-col :span="24">
              <a-form-item style="margin-bottom: 8px">
                <a-checkbox v-model:checked="form.create_missing_products">
                  Create ERP products for Shopify products that do not match a SKU
                </a-checkbox>
              </a-form-item>
              <a-form-item style="margin-bottom: 0">
                <a-checkbox v-model:checked="form.create_missing_customers">
                  Create ERP customers for Shopify customers that do not match an email
                </a-checkbox>
                <div class="hint">With this off, an order from an unknown customer is skipped rather than guessed at.</div>
              </a-form-item>
            </a-col>
          </a-row>
        </a-tab-pane>

        <!-- ------------------------------------------------ entities -->
        <a-tab-pane key="entities" tab="What to sync">
          <p class="lead">
            Only the entities switched on here can be synced — a shop you just pull orders from
            should not have a stray product push rewriting its catalogue.
          </p>
          <div class="ent-grid">
            <label v-for="entity in ENTITIES" :key="entity.value" class="ent-card">
              <a-checkbox v-model:checked="form[`sync_${entity.value}`]" />
              <span class="ent-body">
                <span class="ent-title">
                  <component :is="entity.icon" :size="15" />
                  {{ entity.label }}
                </span>
                <span class="ent-hint">{{ entity.hint }}</span>
                <span class="ent-dirs">
                  <a-tag v-for="d in entity.directions" :key="d" :color="optionOf(DIRECTIONS, d).color" class="dir-tag">
                    {{ labelOf(DIRECTIONS, d) }}
                  </a-tag>
                </span>
              </span>
            </label>
          </div>
        </a-tab-pane>

        <!-- ------------------------------------------------ automatic -->
        <a-tab-pane key="auto" tab="Automatic sync">
          <a-form-item style="margin-bottom: 10px">
            <a-checkbox v-model:checked="form.auto_sync">
              Sync automatically on a schedule
            </a-checkbox>
            <div class="hint">
              The scheduler checks every 5 minutes, which is the finest the intervals below
              can use. Syncs can always be started by hand.
            </div>
          </a-form-item>

          <a-form-item style="margin-bottom: 10px">
            <a-checkbox v-model:checked="form.instant_stock_push" :disabled="!form.sync_inventory">
              Push stock the moment it changes
            </a-checkbox>
            <div class="hint">
              A sale, return or adjustment sends a stock push for just those products the instant
              it is saved, so the shop is right within seconds instead of at the next scheduled
              run. Nothing else to install — the push runs after the sale has been answered, so
              the till never waits for Shopify.
              <template v-if="!form.sync_inventory">
                Switch Inventory on under <em>What to sync</em> first.
              </template>
            </div>
            <!-- Spelled out, because the consequence of leaving this off is
                 the one people meet later as a mystery: a product sold at the
                 till that the shop keeps selling. -->
            <a-alert
              v-if="form.sync_inventory && !form.instant_stock_push"
              type="info" show-icon style="margin-top: 8px"
              :message="`Leave it off and a POS sale reaches Shopify only on the next Inventory push — up to ${inventoryEvery} minutes later.`"
            />
          </a-form-item>

          <a-form-item label="Default interval (minutes)" name="sync_interval_minutes" style="max-width: 260px">
            <a-input-number
              v-model:value="form.sync_interval_minutes" :min="5" :max="1440"
              style="width: 100%" :disabled="!form.auto_sync"
            />
            <div class="hint">Used by every step that does not set its own.</div>
          </a-form-item>

          <a-divider style="margin: 12px 0" />

          <div class="plan-head">
            <div>
              <strong>What runs automatically</strong>
              <div class="hint">
                Each step is one entity in one direction. Orders only ever come <em>from</em> Shopify.
              </div>
            </div>
            <a-button size="small" :disabled="!form.auto_sync || !remainingChoices.length" @click="addStep">
              <template #icon><PlusOutlined /></template>
              Add step
            </a-button>
          </div>

          <a-alert
            v-if="!form.auto_sync_plan.length"
            type="info" show-icon style="margin-top: 10px"
            message="Using the default plan"
          >
            <template #description>
              Orders are pulled in and stock is pushed out, every
              {{ form.sync_interval_minutes || 60 }} minutes. Add a step to choose your own —
              clearing the list again restores this default.
            </template>
          </a-alert>

          <div v-else class="plan-list">
            <div v-for="(step, i) in form.auto_sync_plan" :key="i" class="plan-row">
              <a-select
                v-model:value="step.entity" :options="entityChoices(step)" style="flex: 1 1 150px"
                :disabled="!form.auto_sync" @change="onEntityChange(step)"
              />
              <a-select
                v-model:value="step.direction" :options="directionChoices(step)" style="flex: 1 1 150px"
                :disabled="!form.auto_sync"
              />
              <a-input-number
                v-model:value="step.every" :min="5" :max="10080" style="flex: 0 0 130px"
                :placeholder="`${form.sync_interval_minutes || 60} min`" :disabled="!form.auto_sync"
              />
              <a-button type="text" danger :disabled="!form.auto_sync" @click="form.auto_sync_plan.splice(i, 1)">
                <template #icon><DeleteOutlined /></template>
              </a-button>
              <div v-if="!form[`sync_${step.entity}`]" class="plan-warn">
                {{ entityOf(step.entity).label }} is switched off on the previous tab — this step will be skipped.
              </div>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-form>
  </a-modal>
</template>

<script setup>
/**
 * Connect or edit one shop.
 *
 * The token field is write-only: an existing store shows a masked placeholder
 * and an empty submit keeps whatever is stored. The API never returns the token,
 * so there is nothing here that could leak it back out.
 */
import { ref, reactive, computed, watch } from 'vue';
import { message } from 'ant-design-vue';
import { PlusOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import {
  ENTITIES, PRICE_FIELDS, DIRECTIONS, autoStepChoices, entityOf, stepKey, labelOf, optionOf,
} from './shopifyOptions';
import http from '../../lib/http';
import { t } from '../../i18n';

const props = defineProps({
  open: { type: Boolean, default: false },
  store: { type: Object, default: null },
  warehouses: { type: Array, default: () => [] },
  /** Which tab to open on — lets a page link straight to one setting. */
  initialTab: { type: String, default: 'connection' },
});
const emit = defineEmits(['update:open', 'saved']);

const formRef = ref();
const tab = ref('connection');
const saving = ref(false);
const allowRebind = ref(false);

const isEdit = computed(() => !!props.store?.id);
const warehouseOptions = computed(() => props.warehouses.map(w => ({ value: w.id, label: w.name })));

const locations = ref([]);
const loadingLocations = ref(false);
const locationOptions = computed(() => locations.value.map(l => ({
  value: l.id,
  label: `${l.name}${l.city ? ' — ' + l.city : ''}${l.active ? '' : ' (inactive)'}`,
})));

const form = reactive(emptyForm());

function emptyForm() {
  return {
    name: '',
    shop_domain: '',
    // Dev Dashboard apps are the only kind Shopify still creates, so they are
    // the default; the token option stays for shops connected before 2026.
    auth_mode: 'client_credentials',
    access_token: '',
    client_id: '',
    client_secret: '',
    webhook_secret: '',
    clear_webhook_secret: false,
    api_version: '2024-10',
    warehouse_id: undefined,
    location_id: undefined,
    price_field: 'price',
    auto_sync: false,
    instant_stock_push: false,
    sync_interval_minutes: 60,
    // Empty means "the default plan" — the server reads it the same way.
    auto_sync_plan: [],
    create_missing_products: true,
    create_missing_customers: true,
    sync_products: true,
    sync_inventory: true,
    sync_customers: true,
    sync_orders: true,
    sync_collections: true,
    sync_fulfillments: true,
  };
}

const required = () => [{ required: true, message: t('Field_is_required', 'This field is required') }];

/** Whether the store already holds a client id + secret the edit may keep. */
const clientPairStored = computed(() => isEdit.value && props.store?.auth_mode === 'client_credentials');

const rules = computed(() => {
  const usingPair = form.auth_mode === 'client_credentials';
  // Credentials are only mandatory when they are new: on edit, blank means
  // "keep the stored one" — unless the kind is being switched, in which case
  // the other kind's credentials must actually be supplied.
  const tokenStored = isEdit.value && props.store?.auth_mode === 'token';

  return {
    name: required(),
    shop_domain: required(),
    access_token: !usingPair && !tokenStored ? required() : [],
    client_id: usingPair && !clientPairStored.value ? required() : [],
    client_secret: usingPair && !clientPairStored.value ? required() : [],
  };
});

function onAuthModeChange() {
  formRef.value?.clearValidate?.(['access_token', 'client_id', 'client_secret']);
}

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;

  tab.value = props.initialTab || 'connection';
  allowRebind.value = false;
  locations.value = [];
  Object.assign(form, emptyForm());

  if (props.store) {
    Object.keys(form).forEach((key) => {
      if (props.store[key] !== undefined && props.store[key] !== null) form[key] = props.store[key];
    });
    // Never prefill secrets — they are not returned by the API anyway. The
    // client id is shown as a placeholder instead so it can be checked
    // against the Dev Dashboard without being resubmitted.
    form.auth_mode = props.store.auth_mode || 'token';
    form.access_token = '';
    form.client_id = '';
    form.client_secret = '';
    form.webhook_secret = '';
    form.clear_webhook_secret = false;
    // Copied, not referenced: the rows are edited in place and the store prop
    // belongs to the list page behind this modal.
    form.auto_sync_plan = (props.store.auto_sync_plan || []).map(s => ({ ...s }));
    if (props.store.location_id) {
      locations.value = [{ id: props.store.location_id, name: `Location ${props.store.location_id}`, active: true }];
    }
  }

  formRef.value?.clearValidate?.();
});

// ---------------------------------------------------------- automatic plan --
// One step per entity+direction: two "products push" steps would race each
// other over the same mappings, so a pair already in the list is never offered
// again.

const usedKeys = computed(() => new Set(form.auto_sync_plan.map(stepKey)));

/** The lag a POS sale would have without instant push. */
const inventoryEvery = computed(() => {
  const step = form.auto_sync_plan.find(s => s.entity === 'inventory' && s.direction === 'push');

  return step?.every || form.sync_interval_minutes || 60;
});

const remainingChoices = computed(() => autoStepChoices().filter(c => !usedKeys.value.has(stepKey(c))));

function takenByAnother(step, entity, direction) {
  return form.auto_sync_plan.some(s => s !== step && s.entity === entity && s.direction === direction);
}

function entityChoices(step) {
  return ENTITIES.map(e => ({
    value: e.value,
    label: e.label,
    disabled: e.directions.every(d => takenByAnother(step, e.value, d)),
  }));
}

function directionChoices(step) {
  return entityOf(step.entity).directions.map(d => ({
    value: d,
    label: optionOf(DIRECTIONS, d).label,
    disabled: takenByAnother(step, step.entity, d),
  }));
}

/** Keep the direction legal: orders cannot be pushed, and pairs cannot repeat. */
function onEntityChange(step) {
  const allowed = entityOf(step.entity).directions.filter(d => !takenByAnother(step, step.entity, d));
  if (!allowed.includes(step.direction)) step.direction = allowed[0];
}

function addStep() {
  const next = remainingChoices.value[0];
  if (next) form.auto_sync_plan.push({ ...next, every: null });
}

async function loadLocations() {
  if (!isEdit.value) return;
  loadingLocations.value = true;
  try {
    const res = await http.get(`shopify/stores/${props.store.id}/locations`);
    locations.value = res?.locations || [];
    if (!locations.value.length) message.info('That shop reports no locations');
  } catch (e) {
    message.error(e?.data?.error || 'Could not load locations — check the connection first');
  } finally {
    loadingLocations.value = false;
  }
}

async function submit() {
  try {
    await formRef.value.validate();
  } catch (e) {
    tab.value = 'connection';   // the required fields all live there
    return;
  }

  saving.value = true;
  try {
    const payload = { ...form };
    // A half-filled row is dropped rather than sent — the server would discard
    // it anyway, and silently saving nine steps when ten are on screen is worse
    // than saving the nine that are actually complete.
    payload.auto_sync_plan = form.auto_sync_plan
      .filter(s => s.entity && s.direction)
      .map(s => ({ entity: s.entity, direction: s.direction, every: s.every || 0 }));
    // Only the chosen kind of credential travels; blank fields are dropped
    // so the server keeps what it has rather than erasing it.
    if (payload.auth_mode === 'client_credentials') {
      delete payload.access_token;
      if (!payload.client_id) delete payload.client_id;
      if (!payload.client_secret) delete payload.client_secret;
    } else {
      delete payload.client_id;
      delete payload.client_secret;
      if (!payload.access_token) delete payload.access_token;
    }
    if (!payload.webhook_secret) delete payload.webhook_secret;
    if (payload.clear_webhook_secret) delete payload.webhook_secret;
    else delete payload.clear_webhook_secret;

    if (isEdit.value) await http.put(`shopify/stores/${props.store.id}`, payload);
    else await http.post('shopify/stores', payload);

    message.success(isEdit.value ? 'Store updated' : 'Store connected');
    emit('saved');
  } catch (e) {
    message.error(firstError(e) || 'Could not save this store');
  } finally {
    saving.value = false;
  }
}

function firstError(e) {
  const errors = e?.data?.errors;
  if (errors) {
    const first = Object.values(errors)[0];
    if (Array.isArray(first) && first.length) return first[0];
  }
  return e?.data?.message || '';
}

function close() {
  emit('update:open', false);
}
</script>

<style scoped>
.lead {
  margin-bottom: 14px;
  font-size: 13px;
  opacity: 0.7;
}
.hint {
  font-size: 11.5px;
  opacity: 0.55;
  margin-top: 2px;
}
.link {
  color: #5f9e3f;
  cursor: pointer;
}
.ent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 10px;
}
.ent-card {
  display: flex;
  gap: 10px;
  padding: 12px 14px;
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 12px;
  cursor: pointer;
}
.ent-card:hover {
  border-color: rgba(95, 158, 63, 0.5);
}
.ent-body {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}
.ent-title {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  font-size: 13.5px;
}
.ent-hint {
  font-size: 11.5px;
  opacity: 0.6;
}
.ent-dirs {
  margin-top: 3px;
}
.dir-tag {
  font-size: 10.5px;
  line-height: 17px;
  margin-inline-end: 3px;
}
.plan-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}
.plan-list {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.plan-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.plan-warn {
  flex: 1 1 100%;
  font-size: 11.5px;
  color: #d48806;
}
</style>
