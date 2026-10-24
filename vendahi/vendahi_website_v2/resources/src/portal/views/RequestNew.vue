<template>
  <div class="portal-page portal-request-new">
    <header class="pc-page-header">
      <div>
        <h1 class="pc-page-title">{{ $t('new_request') }}</h1>
        <p class="pc-page-sub">{{ $t('new_request_subtitle') }}</p>
      </div>
      <router-link to="/requests" class="pc-link-back">{{ $t('back_to_requests') }}</router-link>
    </header>

    <form class="pc-card pc-form" @submit.prevent="submit">
      <div class="pc-form-row">
        <label>{{ $t('rq_type') }} <span class="pc-required">*</span></label>
        <select v-model="form.type" class="rq-select" required>
          <option v-for="t in TYPES" :key="t" :value="t">{{ $t('rq_type_' + t) }}</option>
        </select>
      </div>
      <div class="pc-form-row">
        <label>{{ $t('subject') }} <span class="pc-required">*</span></label>
        <input v-model="form.subject" type="text" maxlength="255" required />
      </div>
      <div class="pc-form-row">
        <label>{{ $t('details') }} <span class="pc-required">*</span></label>
        <textarea v-model="form.body" rows="6" maxlength="10000" required></textarea>
      </div>
      <div class="pc-form-row">
        <label>{{ $t('attachments') }} <span class="pc-muted">{{ $t('optional') }}</span></label>
        <input type="file" multiple @change="onFiles" />
        <ul v-if="files.length" class="rq-files">
          <li v-for="(f, i) in files" :key="i">{{ f.name }} <button type="button" class="pc-icon-btn" @click="files.splice(i, 1)">×</button></li>
        </ul>
        <small class="pc-muted">{{ $t('rq_attachments_hint') }}</small>
      </div>

      <div v-if="error" class="pc-alert pc-alert-error">{{ error }}</div>

      <div class="pc-form-actions">
        <router-link to="/requests" class="pc-btn pc-btn-ghost">{{ $t('cancel') }}</router-link>
        <button type="submit" class="pc-btn pc-btn-primary" :disabled="submitting">
          <span v-if="submitting" class="pc-btn-spinner"></span>{{ $t('submit_request') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
/**
 * Customer portal — submit a protocol request (multipart with attachments[]).
 * POST /api/portal/requests → {id, Ref}.
 */
import http from '../lib/http';

const TYPES = ['complaint', 'request', 'memo', 'letter', 'document', 'enquiry', 'other'];

export default {
  data() {
    return { form: { type: 'request', subject: '', body: '' }, files: [], submitting: false, error: '', TYPES };
  },
  methods: {
    onFiles(e) {
      const picked = Array.from(e.target.files || []);
      this.files = this.files.concat(picked).slice(0, 5);
      e.target.value = '';
    },
    async submit() {
      this.error = '';
      this.submitting = true;
      try {
        const fd = new FormData();
        fd.append('type', this.form.type);
        fd.append('subject', this.form.subject);
        fd.append('body', this.form.body);
        this.files.forEach(f => fd.append('attachments[]', f));
        const { data } = await http.post('/portal/requests', fd);
        this.$router.push(`/requests/${data.id}`);
      } catch (e) {
        const d = e && e.response && e.response.data;
        this.error = (d && (d.message || (d.errors && Object.values(d.errors).flat().join(' ')))) || this.$t('request_submit_failed');
      }
      this.submitting = false;
    },
  },
};
</script>

<style scoped>
.rq-select {
  width: 100%;
  height: 40px;
  padding: 0 10px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  background: #fff;
  font: inherit;
  color: inherit;
}
.rq-files { list-style: none; padding: 0; margin: 8px 0 0; font-size: 13px; }
.rq-files li { display: flex; align-items: center; gap: 8px; padding: 2px 0; }
</style>
