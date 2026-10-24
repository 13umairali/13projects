<template>
  <div class="page">
    <PageHeader :title="tf('Update_Settings', 'Update Settings')" :breadcrumb="[$t('Settings'), tf('Update_Settings', 'Update Settings')]">
      <template #actions>
        <a-button :loading="checking" @click="checkForUpdates">
          <template #icon><ReloadOutlined /></template>
          {{ tf('Check_for_updates', 'Check for updates') }}
        </a-button>
      </template>
    </PageHeader>

    <a-row :gutter="16">
      <a-col :xs="24" :lg="16">
        <!-- Status banner + version comparison -->
        <a-card size="small" style="margin-bottom: 16px">
          <a-alert :type="bannerType" show-icon :message="statusTitle" :description="statusDescription" style="margin-bottom: 16px" />
          <div class="versions">
            <div class="version-box">
              <div class="version-label">{{ tf('Current_Version', 'Current version') }}</div>
              <div class="version-number">v{{ currentVersion || '—' }}</div>
            </div>
            <div class="version-arrow" :class="{ up: hasUpdate }"><ArrowRightOutlined /></div>
            <div class="version-box" :class="{ highlight: hasUpdate }">
              <div class="version-label">{{ tf('Latest_Version', 'Latest version') }}</div>
              <div class="version-number">v{{ latestVersion || '—' }}</div>
            </div>
          </div>
          <div v-if="lastCheckedText" class="muted" style="margin-top: 8px">{{ lastCheckedText }}</div>

          <div v-if="latestInfo && (latestInfo.notes || latestInfo.description)" style="margin-top: 12px">
            <strong>{{ tf('Release_Notes', 'Release notes') }}</strong>
            <div class="muted" style="white-space: pre-wrap">{{ latestInfo.notes || latestInfo.description }}</div>
          </div>

          <a-space style="margin-top: 16px">
            <a-button
              type="primary" size="large" :disabled="!hasUpdate || !canUpdate || updating" :loading="updating"
              @click="confirmUpdate"
            >
              <template #icon><CloudDownloadOutlined /></template>
              {{ updating ? tf('Updating', 'Updating…') : tf('Update_Now', 'Update now') }}
            </a-button>
            <a-button :loading="preflightLoading" @click="runPreflight">{{ tf('Re_run_checks', 'Re-run checks') }}</a-button>
          </a-space>
        </a-card>

        <!-- Progress timeline (while updating / after) -->
        <a-card v-if="updating || updatePercent > 0" size="small" :title="tf('Update_Progress', 'Update Progress')" style="margin-bottom: 16px">
          <a-progress :percent="updatePercent" :status="updateFailed ? 'exception' : updatePercent >= 100 ? 'success' : 'active'" />
          <a-steps size="small" :current="currentStepIndex" :status="updateFailed ? 'error' : undefined" style="margin-top: 16px" :items="updateSteps.map(s => ({ title: s.label, description: s.description }))" />
        </a-card>

        <!-- Changelog + history -->
        <a-card v-if="changelog.length" size="small" :title="tf('Changelog', 'Changelog')" style="margin-bottom: 16px">
          <div v-for="(entry, i) in changelog" :key="i" class="changelog-entry">
            <a-tag :color="String(entry.version) === String(latestVersion) ? 'processing' : 'default'">v{{ entry.version }}</a-tag>
            <span v-if="entry.date || entry.released_at" class="muted">{{ entry.date || entry.released_at }}</span>
            <ul v-if="Array.isArray(entry.items) && entry.items.length" class="muted" style="margin: 4px 0 0; padding-left: 18px">
              <li v-for="(item, j) in entry.items" :key="j">{{ item }}</li>
            </ul>
            <div v-else class="muted" style="white-space: pre-wrap; margin-top: 4px">{{ entry.notes || entry.changes || entry.description || '' }}</div>
          </div>
        </a-card>
        <a-card v-if="updateHistory.length" size="small" :title="tf('Update_History', 'Update History')">
          <a-table size="small" :data-source="updateHistory" :pagination="false" row-key="__i"
            :columns="[
              { title: tf('Version', 'Version'), dataIndex: 'version', key: 'version', customRender: ({ text }) => 'v' + text },
              { title: t('date'), key: 'date', customRender: ({ record }) => record.time || record.date || record.updated_at || record.created_at || '' },
              { title: t('Status'), dataIndex: 'status', key: 'status' },
            ]"
          />
        </a-card>
      </a-col>

      <a-col :xs="24" :lg="8">
        <!-- Pre-flight checks -->
        <a-card size="small" :title="tf('System_Checks', 'System Checks')" style="margin-bottom: 16px">
          <a-skeleton v-if="preflightLoading" active :paragraph="{ rows: 4 }" />
          <template v-else>
            <a-empty v-if="!preflightItems.length" :description="tf('No_checks_available', 'Checks unavailable')" />
            <div v-for="(item, i) in preflightItems" :key="i" class="check-row">
              <CheckCircleFilled v-if="item.ok" style="color: #52c41a" />
              <CloseCircleFilled v-else style="color: #ff4d4f" />
              <span>{{ item.label }}</span>
            </div>
            <a-alert v-if="preflight && !canUpdate" type="warning" show-icon style="margin-top: 12px" :message="tf('Fix_checks_before_updating', 'Fix the failed checks before updating.')" />
          </template>
        </a-card>

        <!-- Manual steps, as in legacy -->
        <a-card size="small" :title="tf('Manual_Update', 'Manual update')">
          <ol class="manual-steps">
            <li>{{ tf('Manual_Step_1', 'Back up your database and files first.') }}</li>
            <li>{{ tf('Manual_Step_2', 'Download the latest version from your CodeCanyon downloads and extract it.') }}</li>
            <li>{{ tf('Manual_Step_3', 'Upload the new files over the current installation, keeping your .env and storage folders.') }}</li>
            <li>{{ tf('Manual_Step_4', 'Open the site — pending database migrations run from the update screen.') }}</li>
          </ol>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
/**
 * Port of legacy settings/update_settings.vue (SaaS AutoUpdateController).
 *   GET  get_version_info → {current_version, latest_version, latest_info,
 *        changelog[], update_history[]} (or a bare version string)
 *   GET  update/preflight → {ok, permissions: {name: {writable}}, network: {ok}, disk: {storage_free}}
 *   POST one_click_update (long request)   GET update/progress → {percent, step} (polled every 1.5 s)
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { message, Modal } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { ReloadOutlined, ArrowRightOutlined, CloudDownloadOutlined, CheckCircleFilled, CloseCircleFilled } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import http from '../../lib/http';
import { t as tf } from '../../i18n';

const { t } = useI18n();

const currentVersion = ref(document.querySelector('meta[name="app-version"]')?.getAttribute('content') || '');
const latestVersion = ref('');
const latestInfo = ref(null);
const changelog = ref([]);
const updateHistory = ref([]);
const checking = ref(false);
const lastChecked = ref(null);
const tick = ref(0); // re-renders "last checked" text
const preflightLoading = ref(false);
const preflight = ref(null);
const canUpdate = ref(false);
const updating = ref(false);
const updateFailed = ref(false);
const updatePercent = ref(0);
const updateStep = ref('idle');
let progressTimer = null;
let tickTimer = null;

function versionCompare(a, b) {
  const pa = String(a).split('.').map(Number);
  const pb = String(b).split('.').map(Number);
  for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
    const na = pa[i] || 0; const nb = pb[i] || 0;
    if (na > nb) return 1;
    if (na < nb) return -1;
  }
  return 0;
}
const hasUpdate = computed(() => !!(latestVersion.value && currentVersion.value) && versionCompare(latestVersion.value, currentVersion.value) > 0);
const bannerType = computed(() => (updating.value ? 'info' : updateFailed.value ? 'error' : hasUpdate.value ? 'warning' : 'success'));
const statusTitle = computed(() => (updating.value ? tf('Updating_Application', 'Updating application')
  : updateFailed.value ? tf('Update_Failed', 'Update failed')
    : hasUpdate.value ? tf('Update_Available', 'Update available') : tf('System_Up_to_Date', 'System up to date')));
const statusDescription = computed(() => (updating.value ? tf('Please_wait_update_in_progress', 'Please wait, the update is in progress.')
  : updateFailed.value ? tf('Update_failed_rolled_back', 'The update failed and was rolled back.')
    : hasUpdate.value ? `${tf('Version', 'Version')} ${latestVersion.value} ${tf('is_ready_to_install', 'is ready to install')}`
      : tf('You_already_have_the_latest_version', 'You already have the latest version.')));
const lastCheckedText = computed(() => {
  void tick.value;
  if (!lastChecked.value) return '';
  const diff = Math.floor((Date.now() - lastChecked.value) / 1000);
  if (diff < 10) return tf('Last_checked_just_now', 'Last checked just now');
  const ago = tf('ago', 'ago');
  const lc = tf('Last_checked', 'Last checked');
  if (diff < 60) return `${lc} ${diff}s ${ago}`;
  if (diff < 3600) return `${lc} ${Math.floor(diff / 60)}m ${ago}`;
  return `${lc} ${Math.floor(diff / 3600)}h ${ago}`;
});

function formatBytes(bytes) {
  if (!bytes) return '0 B';
  const k = 1024; const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}
const preflightItems = computed(() => {
  const p = preflight.value;
  if (!p) return [];
  const items = [];
  if (p.permissions) for (const [k, v] of Object.entries(p.permissions)) items.push({ label: `${k.replace(/_/g, ' ')} writable`, ok: !!v?.writable });
  if (p.network) items.push({ label: 'Update server reachable', ok: !!p.network.ok });
  if (p.disk) {
    const free = p.disk.storage_free || 0;
    items.push({ label: `Disk space (${formatBytes(free)} free)`, ok: free > 200 * 1024 * 1024 });
  }
  return items;
});

const updateSteps = computed(() => [
  { key: 'starting', label: tf('Preparing', 'Preparing'), description: tf('Initializing_update', 'Initializing update'), threshold: 0 },
  { key: 'database_backup_created', label: tf('Backup', 'Backup'), description: tf('Creating_backup', 'Creating backup'), threshold: 10 },
  { key: 'download_complete', label: tf('Download', 'Download'), description: tf('Downloading_package', 'Downloading package'), threshold: 35 },
  { key: 'extracted', label: tf('Verify', 'Verify'), description: tf('Verifying_integrity', 'Verifying integrity'), threshold: 50 },
  { key: 'deployment_completed', label: tf('Install', 'Install'), description: tf('Deploying_files', 'Deploying files'), threshold: 70 },
  { key: 'migrations_completed', label: tf('Migrate', 'Migrate'), description: tf('Database_migrations', 'Database migrations'), threshold: 85 },
  { key: 'completed', label: tf('Done', 'Done'), description: tf('Finalizing', 'Finalizing'), threshold: 100 },
]);
const currentStepIndex = computed(() => {
  const idx = updateSteps.value.findIndex(s => updatePercent.value <= s.threshold);
  return idx < 0 ? updateSteps.value.length : idx;
});

async function checkForUpdates() {
  checking.value = true;
  try {
    const data = await http.get('get_version_info');
    if (data && typeof data === 'object') {
      currentVersion.value = data.current_version || currentVersion.value;
      latestVersion.value = data.latest_version || '';
      latestInfo.value = data.latest_info || null;
      changelog.value = Array.isArray(data.changelog) ? data.changelog : [];
      updateHistory.value = (Array.isArray(data.update_history) ? data.update_history : []).map((h, i) => ({ ...h, __i: i }));
    } else if (typeof data === 'string' && data) {
      latestVersion.value = data;
    }
    lastChecked.value = Date.now();
  } catch (e) {
    message.error(tf('Failed_to_check_updates', 'Failed to check for updates'));
  } finally {
    checking.value = false;
  }
}
async function runPreflight() {
  preflightLoading.value = true;
  try {
    const data = await http.get('update/preflight');
    preflight.value = data;
    canUpdate.value = !!(data && data.ok);
  } catch (e) {
    preflight.value = null;
    canUpdate.value = false;
  } finally {
    preflightLoading.value = false;
  }
}
function confirmUpdate() {
  Modal.confirm({
    title: t('Are_you_sure'),
    content: tf('Update_confirmation_text', 'A backup is taken first and the system is unavailable for a few minutes. Continue?'),
    okText: tf('Yes_update', 'Yes, update'),
    cancelText: t('Cancel'),
    onOk: runUpdate,
  });
}
function startProgressPolling() {
  if (progressTimer) return;
  progressTimer = setInterval(async () => {
    try {
      const res = await http.get('update/progress');
      if (res) {
        updatePercent.value = Number(res.percent) || 0;
        updateStep.value = res.step || 'running';
        if (updatePercent.value >= 100) stopProgressPolling();
      }
    } catch (e) { /* keep polling */ }
  }, 1500);
}
function stopProgressPolling() {
  if (progressTimer) { clearInterval(progressTimer); progressTimer = null; }
}
async function runUpdate() {
  updating.value = true;
  updateFailed.value = false;
  updatePercent.value = 0;
  updateStep.value = 'starting';
  startProgressPolling();
  try {
    await http.post('one_click_update', {});
    updatePercent.value = 100;
    updateStep.value = 'completed';
    message.success(t('Successfully_Updated'));
    setTimeout(checkForUpdates, 2000);
  } catch (e) {
    updateFailed.value = true;
    message.error(e?.data?.message || t('InvalidData'));
    setTimeout(() => { updating.value = false; updateFailed.value = false; }, 5000);
  } finally {
    stopProgressPolling();
    updating.value = false;
  }
}

onMounted(() => {
  checkForUpdates();
  runPreflight();
  tickTimer = setInterval(() => { tick.value++; }, 30000);
});
onBeforeUnmount(() => {
  stopProgressPolling();
  if (tickTimer) clearInterval(tickTimer);
});
</script>

<style scoped>
.muted { opacity: 0.65; font-size: 13px; }
.versions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.version-box { flex: 1 1 180px; padding: 12px 16px; border: 1px solid rgba(128, 128, 128, 0.25); border-radius: 8px; }
.version-box.highlight { border-color: #faad14; background: rgba(250, 173, 20, 0.08); }
.version-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.65; margin-bottom: 4px; }
.version-number { font-size: 22px; font-weight: 600; }
.version-arrow { font-size: 20px; opacity: 0.4; }
.version-arrow.up { opacity: 1; color: #faad14; }
.check-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.changelog-entry { padding: 8px 0; border-bottom: 1px solid rgba(128, 128, 128, 0.15); }
.changelog-entry:last-child { border-bottom: none; }
.manual-steps { padding-left: 18px; margin: 0; }
.manual-steps li { margin-bottom: 6px; }
</style>
