<template>
  <div class="page">
    <PageHeader :title="$t('create_ticket')" :breadcrumb="[$t('support_center'), $t('create_ticket')]" />
    <a-row justify="center">
      <a-col :xs="24" :lg="16">
        <a-card size="small" :title="$t('open_a_ticket')">
          <a-form ref="formRef" :model="form" :rules="rules" layout="vertical">
            <a-form-item :label="$t('subject')" name="subject"><a-input v-model:value="form.subject" :placeholder="$t('subject_placeholder')" /></a-form-item>
            <a-row :gutter="12">
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('category')" name="category">
                  <a-select v-model:value="form.category" :placeholder="$t('choose_category')" :options="['bug', 'technical', 'billing', 'feature_request', 'account'].map(c => ({ value: c, label: $t('cat_' + c) }))" />
                </a-form-item>
              </a-col>
              <a-col :xs="24" :md="12">
                <a-form-item :label="$t('priority')" name="priority">
                  <a-select v-model:value="form.priority" :placeholder="$t('choose_priority')" :options="['low', 'medium', 'high', 'urgent'].map(p => ({ value: p, label: $t('pri_' + p) }))" />
                </a-form-item>
              </a-col>
            </a-row>
            <a-form-item :label="$t('message')" name="message"><a-textarea v-model:value="form.message" :rows="6" :placeholder="$t('message_placeholder')" /></a-form-item>
            <a-form-item :label="$t('attach_files')" :extra="$t('attachments_hint')">
              <a-upload :file-list="files" multiple accept=".jpg,.jpeg,.png,.pdf" :before-upload="addFile" @remove="removeFile">
                <a-button><template #icon><UploadOutlined /></template>{{ $t('attach_files') }}</a-button>
              </a-upload>
            </a-form-item>
            <a-space>
              <a-button type="primary" :loading="processing" @click="submit"><template #icon><SendOutlined /></template>{{ $t('submit') }}</a-button>
              <a-button @click="$router.push('/support/tickets')">{{ $t('cancel') }}</a-button>
            </a-space>
          </a-form>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/** Port of legacy support/CreateTicket.vue — POST support/tickets (multipart: subject, category, priority, message, attachments[]). */
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { UploadOutlined, SendOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';

const { t } = useI18n();
const router = useRouter();
const formRef = ref();
const processing = ref(false);
const form = ref({ subject: '', category: undefined, priority: 'medium', message: '' });
const files = ref([]);
const rules = computed(() => ({
  subject: [{ required: true, message: t('please_fill_required') }],
  category: [{ required: true, message: t('please_fill_required') }],
  priority: [{ required: true, message: t('please_fill_required') }],
  message: [{ required: true, message: t('please_fill_required') }],
}));
function addFile(file) { files.value = [...files.value, file]; return false; }
function removeFile(file) { files.value = files.value.filter(f => f.uid !== file.uid); }

async function submit() {
  try { await formRef.value.validate(); } catch (e) { return; }
  processing.value = true;
  try {
    const fd = new FormData();
    fd.append('subject', form.value.subject);
    fd.append('category', form.value.category);
    fd.append('priority', form.value.priority);
    fd.append('message', form.value.message);
    files.value.forEach(f => fd.append('attachments[]', f.originFileObj || f));
    const data = await http.postForm('support/tickets', fd);
    message.success(t('ticket_created_success'));
    const id = data?.ticket?.id;
    router.push(id ? `/support/tickets/${id}` : '/support/tickets');
  } catch (e) {
    message.error(e?.data?.message || t('please_fill_required'));
  } finally {
    processing.value = false;
  }
}
</script>
