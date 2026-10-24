<template>
  <div class="portal-page portal-request-detail">
    <header class="pc-page-header">
      <div>
        <h1 class="pc-page-title">{{ request.Ref || $t('rq_request') }}</h1>
        <p class="pc-page-sub" v-if="request.created_at">{{ $t('created_on', { date: request.created_at }) }}</p>
      </div>
      <router-link to="/requests" class="pc-link-back">{{ $t('back_to_requests') }}</router-link>
    </header>

    <div v-if="loading" class="pc-inline-loading">
      <div class="pc-spinner"></div><span>{{ $t('loading') }}</span>
    </div>

    <template v-else>
      <div class="pc-card pc-detail">
        <div class="pc-detail-header">
          <span :class="'pc-badge pc-badge-' + badge(request.status)">{{ $t('rq_status_' + request.status) }}</span>
          <span class="pc-muted">{{ $t('rq_type_' + request.type) }}</span>
        </div>
        <h2 class="rq-subject">{{ request.subject }}</h2>
        <dl class="pc-meta-grid">
          <div><dt>{{ $t('rq_current_desk') }}</dt><dd>{{ request.current_desk || '—' }}</dd></div>
          <div><dt>{{ $t('rq_last_activity') }}</dt><dd>{{ request.last_activity_at || '—' }}</dd></div>
          <div v-if="request.closed_at"><dt>{{ $t('rq_closed_at') }}</dt><dd>{{ request.closed_at }}</dd></div>
        </dl>
        <div class="pc-notes">
          <h3>{{ $t('details') }}</h3>
          <pre>{{ request.body }}</pre>
        </div>
      </div>

      <!-- History (customer-visible events only) -->
      <div class="pc-card">
        <h3 class="rq-h3">{{ $t('rq_history') }}</h3>
        <ol class="rq-timeline">
          <li v-for="e in request.events" :key="e.id" :class="{ 'rq-mine': e.by_customer }">
            <div class="rq-ev-head">
              <strong>{{ $t('rq_ev_' + e.action) }}</strong>
              <span v-if="e.to_desk && ['submitted', 'forwarded', 'returned'].includes(e.action)"> → {{ e.to_desk }}</span>
              <span class="pc-muted"> · {{ e.by_customer ? $t('you') : e.actor }} · {{ e.created_at }}</span>
            </div>
            <pre v-if="e.note" class="rq-note">{{ e.note }}</pre>
            <div v-if="e.attachments && e.attachments.length" class="rq-files">
              <a v-for="a in e.attachments" :key="a.id" :href="a.url" target="_blank" rel="noopener" class="pc-chip">📎 {{ a.file_name }}</a>
            </div>
          </li>
        </ol>
      </div>

      <!-- Reply -->
      <form v-if="request.status !== 'closed'" class="pc-card pc-form" @submit.prevent="reply">
        <div class="pc-form-row">
          <label>{{ $t('your_message') }}</label>
          <textarea v-model="note" rows="4" maxlength="10000" required></textarea>
        </div>
        <div class="pc-form-row">
          <label>{{ $t('attachments') }} <span class="pc-muted">{{ $t('optional') }}</span></label>
          <input type="file" multiple @change="onFiles" />
          <ul v-if="files.length" class="rq-files-list">
            <li v-for="(f, i) in files" :key="i">{{ f.name }} <button type="button" class="pc-icon-btn" @click="files.splice(i, 1)">×</button></li>
          </ul>
        </div>
        <div v-if="error" class="pc-alert pc-alert-error">{{ error }}</div>
        <div class="pc-form-actions">
          <button type="submit" class="pc-btn pc-btn-primary" :disabled="sending">
            <span v-if="sending" class="pc-btn-spinner"></span>{{ $t('send_reply') }}
          </button>
        </div>
      </form>
      <div v-else class="pc-alert pc-alert-success">{{ $t('rq_closed_note') }}</div>
    </template>
  </div>
</template>

<script>
/**
 * Customer portal — one request with its customer-visible history and a reply
 * box. GET /api/portal/requests/{id}; POST /api/portal/requests/{id}/reply.
 */
import http from '../lib/http';
import { statusBadge } from './Requests.vue';

export default {
  data() {
    return { request: { events: [] }, loading: false, note: '', files: [], sending: false, error: '' };
  },
  mounted() { this.fetch(); },
  watch: { '$route.params.id': 'fetch' },
  methods: {
    async fetch() {
      this.loading = true;
      try {
        const { data } = await http.get(`/portal/requests/${this.$route.params.id}`);
        this.request = data.request || { events: [] };
      } catch (_) {}
      this.loading = false;
    },
    onFiles(e) {
      this.files = this.files.concat(Array.from(e.target.files || [])).slice(0, 5);
      e.target.value = '';
    },
    async reply() {
      this.error = '';
      this.sending = true;
      try {
        const fd = new FormData();
        fd.append('note', this.note);
        this.files.forEach(f => fd.append('attachments[]', f));
        await http.post(`/portal/requests/${this.$route.params.id}/reply`, fd);
        this.note = '';
        this.files = [];
        await this.fetch();
      } catch (e) {
        const d = e && e.response && e.response.data;
        this.error = (d && d.message) || this.$t('request_submit_failed');
      }
      this.sending = false;
    },
    badge: statusBadge,
  },
};
</script>

<style scoped>
.rq-subject { font-size: 18px; margin: 10px 0 12px; }
.rq-h3 { margin: 0 0 12px; font-size: 15px; }
.rq-timeline { list-style: none; padding: 0; margin: 0; border-left: 2px solid rgba(0, 0, 0, 0.08); }
.rq-timeline li { position: relative; padding: 0 0 16px 18px; }
.rq-timeline li::before { content: ''; position: absolute; left: -7px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #94a3b8; }
.rq-timeline li.rq-mine::before { background: #6c5ce7; }
.rq-ev-head { font-size: 14px; }
.rq-note { white-space: pre-wrap; font: inherit; margin: 6px 0 0; padding: 8px 10px; border-radius: 8px; background: rgba(0, 0, 0, 0.04); }
.rq-files { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.rq-files-list { list-style: none; padding: 0; margin: 8px 0 0; font-size: 13px; }
.rq-files-list li { display: flex; align-items: center; gap: 8px; padding: 2px 0; }
</style>
