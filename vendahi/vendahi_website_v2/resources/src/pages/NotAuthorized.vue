<template>
  <div class="page">
    <a-result status="403" :title="$t('Error')" :sub-title="$t('you_are_not_authorized')">
      <template #extra>
        <a-button type="primary" @click="$router.push('/dashboard')">{{ $t('Go_back_to_home') }}</a-button>
      </template>
    </a-result>
  </div>
</template>
