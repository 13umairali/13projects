<template>
  <div class="page">
    <PageHeader
      title="Shopify guide"
      subtitle="Connecting a shop, what runs by itself, and where each setting lives"
      :breadcrumb="['Shopify', 'Guide']"
    >
      <template #actions>
        <a-select
          v-model:value="storeId" :options="storeOptions" style="min-width: 260px"
          placeholder="Show a store's own state" allow-clear
          :loading="loading" @change="loadStore"
        />
        <a-button v-if="storeId" @click="$router.push(`/shopify/stores/${storeId}`)">
          Open store
        </a-button>
        <a-button type="primary" @click="$router.push('/shopify/stores')">
          <template #icon><ShopOutlined /></template>
          Stores
        </a-button>
      </template>
    </PageHeader>

    <a-card size="small">
      <a-spin :spinning="loading">
        <a-alert
          v-if="!loading && !stores.length"
          type="info" show-icon style="margin-bottom: 16px"
          message="No shop is connected yet"
          description="Everything below still applies — connect one from Shopify → Stores, then come back here and pick it above to see its own state alongside each step."
        />
        <ShopifyGuide :store="store" :cron="cron" :webhook-url="webhookUrl" />
      </a-spin>
    </a-card>
  </div>
</template>

<script setup>
/**
 * The guide as a page of its own, so it can be read before any store exists —
 * the same component the store's own Setup guide tab renders, with the store
 * optional. Picking one fills in its real state without leaving the page.
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ShopOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import ShopifyGuide from './ShopifyGuide.vue';
import http from '../../lib/http';

const route = useRoute();

const loading = ref(false);
const stores = ref([]);
const store = ref(null);
const storeId = ref(route.query.store_id ? Number(route.query.store_id) : undefined);
const cron = ref({ managed: false, base_path: '', sync: '', queue: '' });
const webhookUrl = ref('');

const storeOptions = computed(() => stores.value.map(s => ({ value: s.id, label: s.label })));

/**
 * The picked store's full settings. The meta list carries only enough to
 * choose from, and the guide reports on things it does not include (the
 * signing secret, the automatic plan), so the store is fetched in full.
 */
async function loadStore(id) {
  if (!id) {
    store.value = null;

    return;
  }

  try {
    const res = await http.get(`shopify/stores/${id}`);
    store.value = res?.store || null;
  } catch (e) {
    store.value = null;
  }
}

onMounted(async () => {
  loading.value = true;
  try {
    const meta = await http.get('shopify/meta');
    stores.value = meta?.stores || [];
    cron.value = meta?.cron || cron.value;
    webhookUrl.value = meta?.webhook_url || '';

    // Deep-linked with ?store_id=, or the only shop there is.
    if (!storeId.value && stores.value.length === 1) storeId.value = stores.value[0].id;
    if (storeId.value) await loadStore(storeId.value);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.page {
  padding-bottom: 20px;
}
</style>
