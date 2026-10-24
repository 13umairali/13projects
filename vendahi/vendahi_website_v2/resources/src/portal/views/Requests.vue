<template>
  <div class="portal-page portal-requests">
    <header class="pc-page-header">
      <div>
        <h1 class="pc-page-title">{{ $t('requests') }}</h1>
        <p class="pc-page-sub">{{ $t('requests_subtitle') }}</p>
      </div>
      <router-link to="/requests/new" class="pc-btn pc-btn-primary">{{ $t('new_request') }}</router-link>
    </header>

    <div class="pc-card">
      <div class="pc-toolbar">
        <div class="pc-search">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          <input v-model="search" type="text" :placeholder="$t('search_requests')" @input="debounceFetch" />
        </div>
        <select v-model="status" class="rq-select" @change="page = 1; fetch()">
          <option value="">{{ $t('all_statuses') }}</option>
          <option v-for="s in STATUSES" :key="s" :value="s">{{ $t('rq_status_' + s) }}</option>
        </select>
      </div>

      <div v-if="loading" class="pc-inline-loading">
        <div class="pc-spinner"></div>
        <span>{{ $t('loading') }}</span>
      </div>

      <template v-else>
        <div v-if="!requests.length" class="pc-empty">
          <div class="pc-empty-icon">📨</div>
          <p>{{ $t('no_requests_found') }}</p>
        </div>

        <!-- Desktop table -->
        <div v-else class="pc-table-wrap pc-hide-mobile">
          <table class="pc-table">
            <thead>
              <tr>
                <th>{{ $t('ref') }}</th>
                <th>{{ $t('subject') }}</th>
                <th>{{ $t('rq_type') }}</th>
                <th>{{ $t('status') }}</th>
                <th>{{ $t('rq_last_activity') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in requests" :key="r.id">
                <td><router-link :to="`/requests/${r.id}`" class="pc-link">{{ r.Ref }}</router-link></td>
                <td><router-link :to="`/requests/${r.id}`" class="pc-link">{{ r.subject }}</router-link></td>
                <td>{{ $t('rq_type_' + r.type) }}</td>
                <td><span :class="'pc-badge pc-badge-' + badge(r.status)">{{ $t('rq_status_' + r.status) }}</span></td>
                <td>{{ r.last_activity_at || r.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile card list -->
        <ul v-if="requests.length" class="pc-mobile-list pc-show-mobile">
          <li v-for="r in requests" :key="'m-' + r.id" class="pc-mobile-row">
            <router-link :to="`/requests/${r.id}`" class="pc-mobile-row-main">
              <div class="pc-mobile-top">
                <strong class="pc-mobile-ref">{{ r.Ref }}</strong>
                <span :class="'pc-badge pc-badge-' + badge(r.status)">{{ $t('rq_status_' + r.status) }}</span>
              </div>
              <div class="pc-mobile-mid">
                <span>{{ r.subject }}</span>
              </div>
              <div class="pc-mobile-foot">
                <span class="pc-kv"><small>{{ $t('rq_type') }}</small>{{ $t('rq_type_' + r.type) }}</span>
                <span class="pc-kv"><small>{{ $t('rq_last_activity') }}</small>{{ r.last_activity_at || r.created_at }}</span>
              </div>
            </router-link>
          </li>
        </ul>

        <div v-if="totalPages > 1" class="pc-pagination">
          <button type="button" class="pc-pg-btn" :disabled="page <= 1" @click="page--; fetch()">{{ $t('previous') }}</button>
          <span class="pc-pg-info">{{ $t('page_of', { page: page, total: totalPages }) }}</span>
          <button type="button" class="pc-pg-btn" :disabled="page >= totalPages" @click="page++; fetch()">{{ $t('next') }}</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
/**
 * Customer portal — my protocol requests (complaints, requests, enquiries…).
 * GET /api/portal/requests {requests, total, last_page}.
 */
import http from '../lib/http';

export const STATUSES = ['open', 'received', 'acknowledged', 'in_progress', 'approved', 'rejected', 'closed'];

/** Map request statuses onto the badge palette the portal stylesheet ships. */
export function statusBadge(status) {
  if (status === 'approved' || status === 'closed') return 'paid';
  if (status === 'rejected') return 'unpaid';
  if (status === 'open') return 'pending';
  if (status === 'in_progress') return 'progress';
  return 'partial';
}

export default {
  data() {
    return { requests: [], loading: false, search: '', status: '', page: 1, pageSize: 10, totalPages: 1, STATUSES, timer: null };
  },
  mounted() { this.fetch(); },
  methods: {
    async fetch() {
      this.loading = true;
      try {
        const { data } = await http.get('/portal/requests', { params: { page: this.page, limit: this.pageSize, search: this.search || undefined, status: this.status || undefined } });
        this.requests = data.requests || [];
        this.totalPages = data.last_page || 1;
      } catch (_) {
        this.requests = [];
      }
      this.loading = false;
    },
    debounceFetch() {
      clearTimeout(this.timer);
      this.timer = setTimeout(() => { this.page = 1; this.fetch(); }, 300);
    },
    badge: statusBadge,
  },
};
</script>

<style scoped>
.rq-select {
  height: 38px;
  padding: 0 10px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  background: #fff;
  font: inherit;
  color: inherit;
}
</style>
