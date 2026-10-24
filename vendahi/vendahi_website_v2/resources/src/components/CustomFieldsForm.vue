<template>
  <div v-if="loading" class="cf-loading">
    <a-spin size="small" /> <span class="muted">{{ tf('Loading', 'Loading…') }}</span>
  </div>
  <div v-else-if="fields.length">
    <a-divider orientation="left" style="margin: 8px 0 12px">
      <DatabaseOutlined /> {{ $t('CustomFields') }}
    </a-divider>
    <a-row :gutter="12">
      <a-col v-for="field in fields" :key="field.id" :xs="24" :md="field.field_type === 'textarea' ? 24 : 12">
        <a-form-item
          :label="field.name"
          :required="!!field.is_required"
          :validate-status="errors[field.id] ? 'error' : undefined"
          :help="errors[field.id] || undefined"
        >
          <a-input
            v-if="field.field_type === 'text'"
            :value="values[field.id]" :placeholder="field.name"
            @update:value="v => set(field, v)"
          />
          <a-input-number
            v-else-if="field.field_type === 'number'"
            :value="values[field.id]" :placeholder="field.name" style="width: 100%"
            @update:value="v => set(field, v)"
          />
          <a-textarea
            v-else-if="field.field_type === 'textarea'"
            :value="values[field.id]" :placeholder="field.name" :rows="3"
            @update:value="v => set(field, v)"
          />
          <a-date-picker
            v-else-if="field.field_type === 'date'"
            :value="values[field.id] ? dayjs(values[field.id]) : null" value-format="YYYY-MM-DD"
            style="width: 100%"
            @update:value="v => set(field, v)"
          />
          <a-select
            v-else-if="field.field_type === 'select'"
            :value="values[field.id] || undefined" :options="selectOptions(field)"
            :placeholder="$t('PleaseSelect')" allow-clear show-search
            @update:value="v => set(field, v ?? '')"
          />
          <a-checkbox
            v-else-if="field.field_type === 'checkbox'"
            :checked="!!values[field.id]"
            @update:checked="v => set(field, !!v)"
          >
            {{ field.name }}
          </a-checkbox>
          <a-input v-else :value="values[field.id]" @update:value="v => set(field, v)" />
        </a-form-item>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/**
 * Admin-defined custom fields for a customer or supplier (SaaS: Settings →
 * Custom Fields). Vue 3 port of the legacy components/CustomFieldsForm.vue.
 *
 *   GET  custom-fields?entity_type=client|provider  → {custom_fields: [{id, name,
 *        field_type: text|number|textarea|date|select|checkbox, is_required,
 *        is_active, default_value}]}
 *   GET  custom-field-values?entity_type=App\Models\Client&entity_id=ID
 *        → {success, values: {fieldId: {value, field}}}
 *   POST custom-field-values {entity_type, entity_id, values: {fieldId: value}}
 *
 * v-model = {fieldId: value}. Values load automatically once `entityId` is
 * known. The parent saves them with `save(entityId)` after its own request
 * succeeds (the legacy create flow could not, because POST clients returns no
 * id — see CustomerForm). `validate()` enforces `is_required` and returns
 * true/false, marking the fields inline.
 */
import { ref, computed, watch, onMounted } from 'vue';
import dayjs from 'dayjs';
import { DatabaseOutlined } from '@ant-design/icons-vue';
import http from '../lib/http';
import { t as tf } from '../i18n';

const props = defineProps({
  entityType: { type: String, required: true, validator: v => ['client', 'provider'].includes(v) },
  entityId: { type: [Number, String], default: null },
  modelValue: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['update:modelValue']);

const fields = ref([]);
const values = ref({ ...props.modelValue });
const errors = ref({});
const loading = ref(false);

const MODEL = computed(() => (props.entityType === 'client' ? 'App\\Models\\Client' : 'App\\Models\\Provider'));

function defaultValue(field) {
  if (field.default_value !== null && field.default_value !== undefined && field.default_value !== '') {
    if (field.field_type === 'checkbox') return ['1', 1, true].includes(field.default_value);
    if (field.field_type !== 'select') return field.default_value;
  }
  switch (field.field_type) {
    case 'checkbox': return false;
    case 'number': return null;
    default: return '';
  }
}

function selectOptions(field) {
  if (!field.default_value) return [];
  let opts = [];
  try {
    opts = Array.isArray(field.default_value) ? field.default_value : JSON.parse(field.default_value);
  } catch (e) {
    return [];
  }
  return (opts || []).map(o => ({ value: String(o), label: String(o) }));
}

function set(field, v) {
  values.value = { ...values.value, [field.id]: v };
  if (errors.value[field.id]) errors.value = { ...errors.value, [field.id]: '' };
  emit('update:modelValue', values.value);
}

async function loadFields() {
  loading.value = true;
  try {
    const data = await http.get('custom-fields', { entity_type: props.entityType });
    fields.value = (data?.custom_fields || []).filter(f => f.is_active !== false);
    const init = {};
    for (const f of fields.value) init[f.id] = defaultValue(f);
    values.value = { ...init, ...values.value };
    emit('update:modelValue', values.value);
  } catch (e) {
    fields.value = [];
  } finally {
    loading.value = false;
  }
}

async function loadValues() {
  if (!props.entityId || !fields.value.length) return;
  try {
    const data = await http.get('custom-field-values', { entity_type: MODEL.value, entity_id: props.entityId });
    if (!data?.success || !data.values) return;
    const loaded = {};
    for (const [fid, v] of Object.entries(data.values)) {
      const isCheckbox = v?.field?.field_type === 'checkbox'
        || fields.value.find(f => String(f.id) === String(fid))?.field_type === 'checkbox';
      loaded[fid] = isCheckbox ? ['1', 1, true].includes(v?.value) : (v?.value ?? '');
    }
    values.value = { ...values.value, ...loaded };
    emit('update:modelValue', values.value);
  } catch (e) { /* values are optional */ }
}

/** Required-field check; marks offending fields and returns whether all pass. */
function validate() {
  const errs = {};
  for (const f of fields.value) {
    const v = values.value[f.id];
    const empty = v === null || v === undefined || v === '' || (f.field_type === 'checkbox' && !v);
    if (f.is_required && empty) errs[f.id] = tf('Field_is_required', 'This field is required');
  }
  errors.value = errs;
  return !Object.keys(errs).length;
}

/** Persist the values for an entity; no-op when there are no fields. */
async function save(entityId) {
  const id = entityId ?? props.entityId;
  if (!id || !fields.value.length) return;
  await http.post('custom-field-values', { entity_type: MODEL.value, entity_id: id, values: values.value });
}

watch(() => props.entityId, (n, o) => { if (n && n !== o) loadValues(); });
onMounted(async () => {
  await loadFields();
  if (props.entityId) await loadValues();
});

defineExpose({ validate, save, hasFields: computed(() => fields.value.length > 0) });
</script>

<style scoped>
.cf-loading { display: flex; align-items: center; gap: 8px; padding: 4px 0 12px; }
.muted { opacity: 0.65; font-size: 12px; }
</style>
