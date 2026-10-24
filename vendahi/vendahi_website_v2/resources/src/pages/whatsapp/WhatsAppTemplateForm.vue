<template>
  <div class="page">
    <PageHeader
      :title="isEdit ? tf('Edit_Template', 'Edit Template') : tf('New_Template', 'New Template')"
      :breadcrumb="['WhatsApp', tf('Templates', 'Templates')]"
    />

    <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />
    <a-row v-else :gutter="16">
      <a-col :xs="24" :lg="14">
        <a-card size="small" :title="tf('Template_Details', 'Template Details')">
          <a-form ref="formRef" :model="form" :rules="rules" layout="vertical">
            <a-form-item :label="t('Name')" name="name">
              <a-input v-model:value="form.name" placeholder="e.g. Invoice Created" />
            </a-form-item>
            <a-form-item :label="tf('Key', 'Key')" :extra="tf('Template_Key_Hint', 'Used to match automation triggers. Leave blank to auto-generate from the name.')">
              <a-input v-model:value="form.key" placeholder="invoice_created" />
            </a-form-item>
            <a-row :gutter="12">
              <a-col :xs="24" :md="12">
                <a-form-item :label="tf('Language', 'Language')">
                  <a-input v-model:value="form.language" placeholder="en" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="t('Category')">
                  <a-input v-model:value="form.category" :placeholder="tf('Optional', 'Optional')" />
                </a-form-item>
              </a-col>
            </a-row>
            <a-form-item :label="tf('Message_Body', 'Message Body')" name="body">
              <a-textarea v-model:value="form.body" :rows="6" placeholder="Hello {{customer_name}}, ..." />
            </a-form-item>
            <a-form-item>
              <a-switch v-model:checked="form.is_active" /> <span style="margin-left: 8px">{{ tf('Active', 'Active') }}</span>
            </a-form-item>
            <a-space>
              <a-button type="primary" :loading="saving" @click="save">{{ $t('Save') }}</a-button>
              <a-button @click="$router.push('/whatsapp/templates')">{{ $t('Cancel') }}</a-button>
            </a-space>
          </a-form>
        </a-card>
      </a-col>

      <a-col :xs="24" :lg="10">
        <a-card size="small" :title="tf('Variables', 'Variables')" style="margin-bottom: 16px">
          <p class="muted">{{ tf('Click_To_Insert_Variable', 'Click to insert into the message body.') }}</p>
          <a-space wrap>
            <a-button v-for="(desc, v) in variables" :key="v" size="small" :title="desc" @click="insertVar(v)">{{ v }}</a-button>
          </a-space>
        </a-card>
        <a-card size="small" :title="tf('Preview', 'Preview')">
          <div class="wa-preview">{{ preview }}</div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/**
 * Port of legacy whatsapp/templates/Create.vue + Edit.vue (one component, the
 * route param decides). apiResource whatsapp/templates: GET list carries the
 * available `variables`; GET/PUT {id}; POST create.
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const id = computed(() => route.params.id);
const isEdit = computed(() => !!id.value);

const loading = ref(true);
const saving = ref(false);
const formRef = ref();
const form = ref({ name: '', key: '', body: '', language: 'en', category: '', is_active: true });
const rules = computed(() => ({
  name: [{ required: true, message: t('Field_is_required') }],
  body: [{ required: true, message: t('Field_is_required') }],
}));

const SAMPLE = {
  '{{customer_name}}': 'John Doe', '{{invoice_number}}': 'INV-1024', '{{total_amount}}': '149.00',
  '{{company_name}}': 'Acme Store', '{{payment_status}}': 'paid', '{{order_status}}': 'confirmed', '{{date}}': '2026-06-03',
};
const variables = ref({});
const preview = computed(() => {
  let out = form.value.body || '';
  for (const [k, v] of Object.entries(SAMPLE)) out = out.split(k).join(v);
  return out || tf('Message_Preview_Placeholder', 'Your message preview will appear here.');
});
function insertVar(v) { form.value.body = (form.value.body || '') + v; }

async function save() {
  try { await formRef.value.validate(); } catch (e) { return; }
  saving.value = true;
  try {
    if (isEdit.value) await http.put(`whatsapp/templates/${id.value}`, form.value);
    else await http.post('whatsapp/templates', form.value);
    message.success(isEdit.value ? t('Successfully_Updated') : t('Successfully_Created'));
    router.push('/whatsapp/templates');
  } catch (e) {
    message.error(e?.data?.message || t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  try {
    const [list, tpl] = await Promise.all([
      http.get('whatsapp/templates', { per_page: 1 }),
      isEdit.value ? http.get(`whatsapp/templates/${id.value}`) : Promise.resolve(null),
    ]);
    variables.value = list?.variables || Object.fromEntries(Object.keys(SAMPLE).map(k => [k, SAMPLE[k]]));
    if (tpl) {
      form.value = {
        name: tpl.name || '', key: tpl.key || '', body: tpl.body || '',
        language: tpl.language || 'en', category: tpl.category || '', is_active: !!tpl.is_active,
      };
    }
  } catch (e) {
    message.error(tf('Failed_To_Load_Template', 'Failed to load template.'));
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.wa-preview {
  white-space: pre-wrap;
  background: #e5ffd9;
  color: rgba(var(--ink-rgb), 0.88);
  border-radius: 8px;
  padding: 12px 14px;
  min-height: 80px;
  font-size: 13px;
}
</style>
