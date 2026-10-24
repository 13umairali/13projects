<template>
  <div class="page">
    <PageHeader :title="$t('Modules')" :breadcrumb="[$t('Settings'), $t('Modules')]" />
    <a-tabs v-model:activeKey="activeTab">
      <a-tab-pane key="modules" :tab="$t('Modules')">
        <ModuleToggles />
      </a-tab-pane>
      <a-tab-pane key="features" :tab="$t('Features')">
        <FeatureToggles />
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script setup>
/**
 * Settings → Modules (ported from the stocky reference's business-modules
 * page). The Modules tab switches optional business modules on/off for the
 * whole tenant (config/modules.js keys, persisted in settings.module_flags);
 * the Features tab exposes the optional-capability switches through the
 * dedicated feature_settings endpoint so saving here never clobbers the rest
 * of the settings row.
 */
import { ref } from 'vue';
import PageHeader from '../../components/PageHeader.vue';
import ModuleToggles from './ModuleToggles.vue';
import FeatureToggles from './FeatureToggles.vue';

const activeTab = ref('modules');
</script>
