<template>
  <div class="portal-page portal-profile">
    <header class="pc-page-header">
      <div>
        <h1 class="pc-page-title">{{ $t('profile') }}</h1>
        <p class="pc-page-sub">{{ $t('profile_subtitle') }}</p>
      </div>
    </header>

    <div v-if="profile" class="pc-profile-grid">
      <!-- Account info card -->
      <section class="pc-card">
        <div class="pc-card-head">
          <div class="pc-profile-head">
            <div class="pc-profile-avatar">{{ initials }}</div>
            <div>
              <h2 class="pc-card-title">{{ profile.client && profile.client.name }}</h2>
              <p class="pc-card-sub">{{ profile.portal_email }}</p>
            </div>
          </div>
        </div>
        <div class="pc-card-body">
          <dl class="pc-info">
            <div class="pc-info-row">
              <dt>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
                {{ $t('portal_email') }}
              </dt>
              <dd>{{ profile.portal_email }}</dd>
            </div>
            <div class="pc-info-row">
              <dt>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v6l9 4 9-4V7"/></svg>
                {{ $t('client') }}
              </dt>
              <dd>{{ profile.client && profile.client.name }}</dd>
            </div>
            <div class="pc-info-row">
              <dt>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
                {{ $t('email') }}
              </dt>
              <dd>{{ profile.client && profile.client.email || '—' }}</dd>
            </div>
            <div class="pc-info-row">
              <dt>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92V20a2 2 0 01-2.18 2A19.8 19.8 0 013.07 4.18 2 2 0 015 2h3.09a2 2 0 012 1.72 12.3 12.3 0 00.7 2.81 2 2 0 01-.45 2.11L9.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.3 12.3 0 002.81.7 2 2 0 011.72 2z"/></svg>
                {{ $t('phone') }}
              </dt>
              <dd>{{ profile.client && profile.client.phone || '—' }}</dd>
            </div>
            <div class="pc-info-row">
              <dt>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $t('address') }}
              </dt>
              <dd>{{ profile.client && profile.client.adresse || '—' }}</dd>
            </div>
          </dl>
        </div>
      </section>

      <!-- Password card -->
      <section class="pc-card">
        <div class="pc-card-head">
          <div>
            <h2 class="pc-card-title">{{ $t('change_password') }}</h2>
            <p class="pc-card-sub">{{ $t('use_8_chars') }}</p>
          </div>
        </div>
        <form @submit.prevent="changePassword" class="pc-card-body pc-form">
          <div class="pc-field">
            <label>{{ $t('current_password') }}</label>
            <input v-model="currentPassword" type="password" required placeholder="••••••••" />
          </div>
          <div class="pc-field">
            <label>{{ $t('new_password') }}</label>
            <input v-model="newPassword" type="password" required minlength="8" :placeholder="$t('min_8_chars')" />
          </div>
          <div class="pc-field">
            <label>{{ $t('confirm_new_password') }}</label>
            <input v-model="confirmPassword" type="password" required placeholder="••••••••" />
          </div>
          <p v-if="pwError" class="pc-alert pc-alert-error">{{ pwError }}</p>
          <p v-if="pwSuccess" class="pc-alert pc-alert-success">{{ $t('password_updated') }}</p>
          <button type="submit" class="pc-btn-primary" :disabled="pwLoading">
            <span v-if="pwLoading" class="pc-btn-spinner"></span>
            {{ pwLoading ? $t('updating') : $t('update_password') }}
          </button>
        </form>
      </section>
      <!-- Danger zone: self-service account closure (DELETE portal/profile — not on this backend, see ../modules.js) -->
      <section v-if="canDeleteAccount" class="pc-card pc-danger-card">
        <div class="pc-card-head">
          <div>
            <h2 class="pc-card-title">{{ $t('delete_account') }}</h2>
            <p class="pc-card-sub">{{ $t('delete_account_subtitle') }}</p>
          </div>
        </div>
        <div class="pc-card-body">
          <p class="pc-danger-copy">{{ $t('delete_copy_1') }}</p>
          <p class="pc-danger-copy">{{ $t('delete_copy_2') }}</p>
          <button type="button" class="pc-btn-danger" @click="openDelete">{{ $t('delete_account') }}</button>
        </div>
      </section>
    </div>

    <!-- Confirmation dialog -->
    <div v-if="delOpen" class="pc-modal-root" role="dialog" aria-modal="true" aria-labelledby="pc-del-title">
      <div class="pc-modal-overlay" @click="closeDelete"></div>
      <div class="pc-modal-card">
        <h3 id="pc-del-title" class="pc-modal-title">{{ $t('delete_account_q') }}</h3>
        <p class="pc-modal-sub">{{ $t('delete_warning') }}</p>

        <form @submit.prevent="confirmDelete" class="pc-form">
          <div class="pc-field">
            <label>{{ $t('type_email_confirm') }}</label>
            <input v-model="delEmail" type="text" autocomplete="off"
                   :placeholder="profile && profile.portal_email" />
          </div>
          <div class="pc-field">
            <label>{{ $t('password') }}</label>
            <input v-model="delPassword" type="password" autocomplete="current-password" placeholder="••••••••" />
          </div>
          <div class="pc-field">
            <label>{{ $t('reason') }} <span class="pc-optional">{{ $t('optional') }}</span></label>
            <textarea v-model="delReason" rows="2" maxlength="500"
                      :placeholder="$t('reason_placeholder')"></textarea>
          </div>

          <p v-if="delError" class="pc-alert pc-alert-error">{{ delError }}</p>

          <div class="pc-modal-actions">
            <button type="button" class="pc-btn-ghost" @click="closeDelete">{{ $t('cancel') }}</button>
            <button type="submit" class="pc-btn-danger" :disabled="delLoading || !canDelete">
              <span v-if="delLoading" class="pc-btn-spinner"></span>
              {{ delLoading ? $t('deleting') : $t('delete_my_account') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="!profile" class="pc-inline-loading">
      <div class="pc-spinner"></div>
      <span>{{ $t('loading_profile') }}</span>
    </div>
  </div>
</template>

<script>
import http from '../lib/http';
import { moduleEnabled } from '../modules';
export default {
  computed: {
    canDeleteAccount() { return moduleEnabled('deleteAccount'); },
  },
  data() {
    return {
      profile: null,
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
      pwError: '',
      pwSuccess: false,
      pwLoading: false,
      delOpen: false,
      delEmail: '',
      delPassword: '',
      delReason: '',
      delError: '',
      delLoading: false,
    };
  },
  computed: {
    canDelete() {
      return !!this.delEmail.trim() && !!this.delPassword;
    },
    initials() {
      const name = (this.profile && this.profile.client && this.profile.client.name) || '';
      return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(w => w.charAt(0).toUpperCase())
        .join('') || 'A';
    },
  },
  mounted() {
    this.fetch();
  },
  methods: {
    async fetch() {
      try {
        const { data } = await http.get('/portal/profile');
        this.profile = data;
      } catch (_) {}
    },
    openDelete() {
      this.delOpen = true;
      this.delError = '';
      this.delEmail = '';
      this.delPassword = '';
      this.delReason = '';
    },
    closeDelete() {
      if (this.delLoading) return;
      this.delOpen = false;
    },
    async confirmDelete() {
      if (!this.canDelete) return;
      this.delError = '';
      this.delLoading = true;
      try {
        await http.delete('/portal/profile', {
          data: {
            current_password: this.delPassword,
            confirm_email: this.delEmail.trim(),
            reason: this.delReason || null,
          },
        });
        // The session is already invalidated server-side; a full reload drops
        // any cached portal state and lands on the login screen.
        window.location.href = '/portal/login';
      } catch (e) {
        const data = e && e.response && e.response.data ? e.response.data : e;
        this.delError = (data && data.message) || this.$t('delete_failed');
        this.delLoading = false;
      }
    },
    async changePassword() {
      if (this.newPassword !== this.confirmPassword) {
        this.pwError = this.$t('passwords_no_match');
        return;
      }
      this.pwError = '';
      this.pwSuccess = false;
      this.pwLoading = true;
      try {
        await http.put('/portal/profile/password', {
          current_password: this.currentPassword,
          password: this.newPassword,
          password_confirmation: this.confirmPassword,
        });
        this.pwSuccess = true;
        this.currentPassword = this.newPassword = this.confirmPassword = '';
      } catch (e) {
        const data = e && e.response && e.response.data ? e.response.data : e;
        this.pwError = (data && data.message) || this.$t('update_password_failed');
      }
      this.pwLoading = false;
    },
  },
};
</script>

<style scoped>
.portal-profile { padding-bottom: 1rem; }

.pc-page-header { margin-bottom: 1.25rem; }
.pc-page-title { font-size: 1.5rem; font-weight: 700; color: var(--pc-text); margin: 0 0 0.2rem; letter-spacing: -0.01em; }
.pc-page-sub { font-size: 0.9rem; color: var(--pc-text-muted); margin: 0; }

.pc-profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
  align-items: flex-start;
}
.pc-card {
  background: var(--pc-surface);
  border: 1px solid var(--pc-border);
  border-radius: var(--pc-radius);
  box-shadow: var(--pc-shadow-sm);
  overflow: hidden;
}
.pc-card-head {
  padding: 1.15rem 1.35rem;
  border-bottom: 1px solid var(--pc-border);
  background: linear-gradient(180deg, var(--pc-surface) 0%, var(--pc-surface-alt) 100%);
}
.pc-card-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--pc-text);
  margin: 0;
  letter-spacing: -0.005em;
}
.pc-card-sub {
  font-size: 0.85rem;
  color: var(--pc-text-muted);
  margin: 0.15rem 0 0;
}
.pc-card-body { padding: 1.25rem 1.35rem 1.4rem; }

.pc-profile-head { display: flex; align-items: center; gap: 0.9rem; }
.pc-profile-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.1rem;
  flex-shrink: 0;
  box-shadow: 0 6px 14px -6px rgba(79, 70, 229, 0.55);
}

.pc-info { margin: 0; display: flex; flex-direction: column; gap: 0.65rem; }
.pc-info-row {
  display: grid;
  grid-template-columns: 140px 1fr;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0;
  border-bottom: 1px dashed var(--pc-border);
}
.pc-info-row:last-child { border-bottom: none; }
.pc-info-row dt {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  color: var(--pc-text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.pc-info-row dd {
  margin: 0;
  font-size: 0.92rem;
  color: var(--pc-text);
  word-break: break-word;
}

/* Form */
.pc-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.pc-field { display: flex; flex-direction: column; gap: 0.35rem; }
.pc-field label {
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--pc-text);
}
.pc-field input {
  padding: 0.65rem 0.9rem;
  border: 1px solid var(--pc-border-strong);
  border-radius: 10px;
  font-size: 0.95rem;
  background: var(--pc-surface-alt);
  color: var(--pc-text);
  box-sizing: border-box;
  transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
}
.pc-field input:focus {
  outline: none;
  background: var(--pc-surface);
  border-color: var(--pc-primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.pc-alert {
  margin: 0;
  padding: 0.65rem 0.85rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 500;
}
.pc-alert-error { background: var(--pc-danger-bg); color: var(--pc-danger); border: 1px solid #fecaca; }
.pc-alert-success { background: var(--pc-success-bg); color: var(--pc-success); border: 1px solid #a7f3d0; }

.pc-btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.7rem 1.25rem;
  background: var(--pc-primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, transform 0.15s;
  box-shadow: 0 4px 10px -4px rgba(79, 70, 229, 0.5);
}
.pc-btn-primary:hover:not(:disabled) { background: var(--pc-primary-600); transform: translateY(-1px); }
.pc-btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.pc-btn-spinner {
  display: inline-block;
  width: 0.95rem;
  height: 0.95rem;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: pc-spin 0.7s linear infinite;
}
@keyframes pc-spin { to { transform: rotate(360deg); } }

.pc-inline-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 3rem;
  color: var(--pc-text-muted);
}
.pc-spinner {
  width: 28px; height: 28px;
  border: 2px solid rgba(79, 70, 229, 0.15);
  border-top-color: var(--pc-primary);
  border-radius: 50%;
  animation: pc-spin 0.7s linear infinite;
}

/* ---- Danger zone: account deletion ---- */
.pc-danger-card {
  grid-column: 1 / -1;
  border-color: rgba(220, 38, 38, 0.28);
}
.pc-danger-card .pc-card-title {
  color: #b91c1c;
}
.pc-danger-copy {
  margin: 0 0 0.7rem;
  font-size: 0.9rem;
  line-height: 1.55;
  color: var(--pc-text-muted);
}
.pc-btn-danger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.7rem 1.25rem;
  background: #dc2626;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.pc-btn-danger:hover:not(:disabled) { background: #b91c1c; }
.pc-btn-danger:disabled { opacity: 0.55; cursor: not-allowed; }
.pc-btn-ghost {
  padding: 0.7rem 1.1rem;
  background: transparent;
  border: 1px solid rgba(15, 23, 42, 0.16);
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--pc-text-muted);
  cursor: pointer;
}
.pc-optional { font-weight: 400; color: var(--pc-text-muted); }

.pc-modal-root {
  position: fixed;
  inset: 0;
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.pc-modal-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
}
.pc-modal-card {
  position: relative;
  z-index: 1;
  width: min(460px, 100%);
  max-height: 88vh;
  overflow: auto;
  background: #fff;
  border-radius: 14px;
  padding: 1.4rem 1.5rem;
  box-shadow: 0 24px 60px -12px rgba(15, 23, 42, 0.35);
}
.pc-modal-title {
  margin: 0 0 0.3rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: #b91c1c;
}
.pc-modal-sub {
  margin: 0 0 1.1rem;
  font-size: 0.9rem;
  color: var(--pc-text-muted);
}
.pc-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 0.4rem;
}

@media (max-width: 900px) {
  .pc-profile-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .pc-page-title { font-size: 1.3rem; }
  .pc-card-head { padding: 1rem 1.15rem; }
  .pc-card-body { padding: 1.1rem 1.15rem; }
  .pc-info-row { grid-template-columns: 1fr; gap: 0.15rem; padding: 0.55rem 0; }
}
</style>
