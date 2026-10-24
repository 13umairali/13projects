<template>
  <div class="page">
    <PageHeader :title="tf('Templates', 'Templates')" :breadcrumb="['WhatsApp', tf('Templates', 'Templates')]">
      <template #actions>
        <a-button type="primary" @click="$router.push('/whatsapp/templates/create')">
          <template #icon><PlusOutlined /></template>
          {{ tf('New_Template', 'New Template') }}
        </a-button>
      </template>
    </PageHeader>

    <DataTable :crud="crud" :columns="columns">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'is_active'">
          <a-tag :color="record.is_active ? 'success' : 'default'">
            {{ record.is_active ? tf('Active', 'Active') : tf('Inactive', 'Inactive') }}
          </a-tag>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space>
            <a-tooltip :title="$t('Edit')">
              <a-button type="text" size="small" @click="$router.push(`/whatsapp/templates/${record.id}/edit`)">
                <template #icon><EditOutlined style="color: #52c41a" /></template>
              </a-button>
            </a-tooltip>
            <a-tooltip :title="$t('Del')">
              <a-button type="text" size="small" danger @click="crud.remove(record)">
                <template #icon><DeleteOutlined /></template>
              </a-button>
            </a-tooltip>
          </a-space>
        </template>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
/**
 * Port of legacy whatsapp/templates/index.vue.
 *   GET whatsapp/templates?page&per_page&search → {templates, total, variables}
 *   DELETE whatsapp/templates/{id}   (apiResource)
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import DataTable from '../../components/DataTable.vue';
import { useCrudTable } from '../../composables/useCrudTable';
import { t as tf } from '../../i18n';

const { t } = useI18n();
let crud;
crud = useCrudTable('whatsapp/templates', {
  sortField: 'id',
  sortType: 'desc',
  params: () => ({ per_page: crud ? crud.limit.value : 25 }),
  select: p => ({ rows: p?.templates || [], total: Number(p?.total) || 0 }),
});
crud.fetchRows();

const columns = computed(() => [
  { title: t('Name'), dataIndex: 'name', key: 'name' },
  { title: tf('Key', 'Key'), dataIndex: 'key', key: 'key' },
  { title: tf('Language', 'Language'), dataIndex: 'language', key: 'language', width: 110 },
  { title: t('Status'), key: 'is_active', width: 110, align: 'center' },
  { title: t('Action'), key: 'actions', width: 110, align: 'center' },
]);
</script>
