<template>
  <ReportPage
    :title="$t('Login_Activity_Report')"
    :breadcrumb="[$t('Reports'), $t('Login_Activity_Report')]"
    :crud="crud"
    :columns="columns"
    row-key="token_id"
    export-endpoint="security/login-activity-report"
    export-rows-key="sessions"
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'login_at'">{{ dateTime(record.login_at) }}</template>
      <template v-else-if="column.key === 'last_activity_at'">
        {{ record.last_activity_at ? dateTime(record.last_activity_at) : '—' }}
      </template>
      <template v-else-if="column.key === 'status'">
        <a-tag :color="record.is_active ? 'success' : 'default'">
          {{ statusLabel(record) }}
        </a-tag>
      </template>
    </template>
  </ReportPage>
</template>

<script setup>
/**
 * Endpoint is under security/, not report/.
 */
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import ReportPage from '../../components/ReportPage.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { useFormat } from '../../composables/useFormat';
import { t as tf } from '../../i18n';

const { t } = useI18n();
const { dateTime } = useFormat();

// Payload: { sessions, totalRows } — rowsKey required.
const crud = useCrudTable('security/login-activity-report', {
  rowsKey: 'sessions',
  sortField: 'login_at',
  sortType: 'desc',
  limit: 50, // legacy default for this page
});

const columns = computed(() => [
  { title: t('Device_Browser'), dataIndex: 'device', key: 'device' },
  { title: t('IP_Address'), dataIndex: 'ip_address', key: 'ip_address' },
  { title: t('Login_Date_Time'), key: 'login_at', dataIndex: 'login_at', exportValue: r => dateTime(r.login_at) },
  { title: t('Last_Activity'), key: 'last_activity_at', dataIndex: 'last_activity_at', exportValue: r => (r.last_activity_at ? dateTime(r.last_activity_at) : '') },
  { title: t('Status'), key: 'status', dataIndex: 'is_active', exportValue: r => statusLabel(r) },
]);

// Backend returns a boolean `is_active` (+ `revoked_at`), not a status string.
const statusLabel = r => (r.is_active ? tf('Active', 'Active') : tf('Revoked', 'Revoked'));

onMounted(crud.fetchRows);
</script>
