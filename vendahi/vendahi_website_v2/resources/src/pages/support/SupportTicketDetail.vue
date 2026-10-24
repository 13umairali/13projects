<template>
  <div class="page">
    <PageHeader :title="ticket ? ticket.ticket_number : $t('ticket')" :breadcrumb="[$t('support_center'), ticket ? ticket.ticket_number : '…']">
      <template #actions>
        <a-button @click="$router.push('/support/tickets')"><template #icon><ArrowLeftOutlined /></template>{{ $t('back_to_tickets') }}</a-button>
      </template>
    </PageHeader>

    <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />
    <a-row v-else-if="ticket" :gutter="16">
      <a-col :xs="24" :lg="16">
        <a-card size="small">
          <template #title>
            <div>{{ ticket.subject }}</div>
            <div class="muted" style="font-weight: 400">{{ ticket.ticket_number }} · {{ $t('cat_' + ticket.category) }} · {{ $t('opened_on') }} {{ fmtDateTime(ticket.created_at) }}</div>
          </template>
          <template #extra>
            <a-tag :color="priorityColor(ticket.priority)">{{ $t('pri_' + ticket.priority) }}</a-tag>
            <a-tag :color="statusColor(ticket.status)">{{ $t('status_' + ticket.status) }}</a-tag>
          </template>

          <div ref="threadEl" class="thread">
            <div v-for="m in ticket.messages" :key="m.id" class="row" :class="m.author_type === 'tenant' ? 'me' : 'them'">
              <div class="bubble">
                <div class="head">
                  <strong>{{ m.author_type === 'tenant' ? (m.author_name || $t('you')) : (m.author_name || $t('support_team')) }}</strong>
                  <span class="time">{{ fmtDateTime(m.created_at) }}</span>
                </div>
                <div class="body">{{ m.body }}</div>
                <div v-if="m.attachments && m.attachments.length" class="files">
                  <template v-for="a in m.attachments" :key="a.id">
                    <a-image v-if="a.is_image" :src="a.url" :width="110" />
                    <a v-else :href="a.url" target="_blank" rel="noopener"><PaperClipOutlined /> {{ a.original_name }}</a>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <div v-if="ticket.status !== 'closed'" class="reply">
            <a-textarea v-model:value="replyBody" :rows="3" :placeholder="$t('write_reply')" />
            <div class="reply-bar">
              <a-upload :file-list="replyFiles" multiple accept=".jpg,.jpeg,.png,.pdf" :before-upload="addFile" @remove="removeFile">
                <a-button size="small"><template #icon><PaperClipOutlined /></template>{{ $t('attach_files') }}</a-button>
              </a-upload>
              <a-button type="primary" :loading="sending" :disabled="!replyBody" @click="sendReply"><template #icon><SendOutlined /></template>{{ $t('send_reply') }}</a-button>
            </div>
          </div>
          <div v-else class="muted" style="text-align: center; padding: 12px">{{ $t('status_closed') }}</div>
        </a-card>
      </a-col>

      <a-col :xs="24" :lg="8">
        <a-card size="small">
          <a-descriptions :column="1" size="small">
            <a-descriptions-item :label="$t('status')"><a-tag :color="statusColor(ticket.status)">{{ $t('status_' + ticket.status) }}</a-tag></a-descriptions-item>
            <a-descriptions-item :label="$t('priority')"><a-tag :color="priorityColor(ticket.priority)">{{ $t('pri_' + ticket.priority) }}</a-tag></a-descriptions-item>
            <a-descriptions-item :label="$t('category')">{{ $t('cat_' + ticket.category) }}</a-descriptions-item>
            <a-descriptions-item :label="$t('ticket_number')">{{ ticket.ticket_number }}</a-descriptions-item>
            <a-descriptions-item :label="$t('opened_on')">{{ fmtDateTime(ticket.created_at) }}</a-descriptions-item>
          </a-descriptions>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/**
 * Port of legacy support/TicketDetail.vue.
 *   GET  support/tickets/{id} → {ticket{…, messages[{author_type, author_name, body, attachments[]}]}}
 *   POST support/tickets/{id}/reply (multipart: message, attachments[])
 * Polls the thread every 12 s while the tab is visible, like legacy.
 */
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { ArrowLeftOutlined, PaperClipOutlined, SendOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { statusColor, priorityColor, fmtDateTime } from './supportFormat';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const loading = ref(true);
const sending = ref(false);
const ticket = ref(null);
const replyBody = ref('');
const replyFiles = ref([]);
const threadEl = ref(null);
let pollTimer = null;

function addFile(file) { replyFiles.value = [...replyFiles.value, file]; return false; }
function removeFile(file) { replyFiles.value = replyFiles.value.filter(f => f.uid !== file.uid); }
function scrollToBottom() { nextTick(() => { if (threadEl.value) threadEl.value.scrollTop = threadEl.value.scrollHeight; }); }

async function fetchTicket(initial = false) {
  try {
    const prev = ticket.value?.messages?.length || 0;
    const data = await http.get(`support/tickets/${route.params.id}`);
    ticket.value = data.ticket;
    if (initial || (ticket.value.messages || []).length !== prev) scrollToBottom();
  } catch (e) {
    if (initial) router.push('/support/tickets');
  } finally {
    loading.value = false;
  }
}
async function sendReply() {
  if (!replyBody.value) return;
  sending.value = true;
  try {
    const fd = new FormData();
    fd.append('message', replyBody.value);
    replyFiles.value.forEach(f => fd.append('attachments[]', f.originFileObj || f));
    await http.postForm(`support/tickets/${route.params.id}/reply`, fd);
    replyBody.value = '';
    replyFiles.value = [];
    message.success(t('reply_sent_success'));
    await fetchTicket();
  } catch (e) {
    message.error(e?.data?.message || t('please_fill_required'));
  } finally {
    sending.value = false;
  }
}
onMounted(async () => {
  await fetchTicket(true);
  pollTimer = setInterval(() => { if (!document.hidden) fetchTicket(); }, 12000);
});
onBeforeUnmount(() => { if (pollTimer) clearInterval(pollTimer); });
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.thread { max-height: 55vh; overflow-y: auto; padding: 4px 0; }
.row { display: flex; margin-bottom: 12px; }
.row.me { justify-content: flex-end; }
.bubble { max-width: 78%; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(128, 128, 128, 0.25); background: rgba(128, 128, 128, 0.06); }
.me .bubble { background: rgba(22, 119, 255, 0.08); border-color: rgba(22, 119, 255, 0.35); }
.head { display: flex; justify-content: space-between; gap: 12px; font-size: 12px; margin-bottom: 4px; }
.time { opacity: 0.55; white-space: nowrap; }
.body { white-space: pre-wrap; line-height: 1.5; }
.files { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.reply { margin-top: 12px; }
.reply-bar { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
</style>
