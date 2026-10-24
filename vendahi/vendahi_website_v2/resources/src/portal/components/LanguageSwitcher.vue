<template>
  <label class="pc-lang" :title="$t('language')">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="10"/>
      <path d="M2 12h20"/>
      <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
    </svg>
    <select
      class="pc-lang-select"
      :value="current"
      :disabled="busy"
      :aria-label="$t('language')"
      @change="onChange"
    >
      <option v-for="(label, code) in locales" :key="code" :value="code">{{ label }}</option>
    </select>
  </label>
</template>

<script>
import { SUPPORTED_LOCALES, setLocale, i18n } from '../i18n';

/**
 * Portal language picker. Shown in the signed-in header and on the guest
 * (login / set-password) pages. Native <select> so it stays keyboard- and
 * touch-friendly without extra dependencies.
 */
export default {
  name: 'LanguageSwitcher',
  data() {
    return { locales: SUPPORTED_LOCALES, busy: false };
  },
  computed: {
    current() {
      return i18n.global.locale.value;
    },
  },
  methods: {
    async onChange(e) {
      const next = e && e.target ? e.target.value : '';
      if (!next || next === this.current || this.busy) return;
      this.busy = true;
      try {
        await setLocale(next);
      } finally {
        this.busy = false;
      }
    },
  },
};
</script>

<style scoped>
.pc-lang {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.55rem;
  border: 1px solid var(--pc-border);
  border-radius: 999px;
  background: var(--pc-surface);
  color: var(--pc-text-muted);
  font-size: 0.82rem;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}
.pc-lang:hover { border-color: var(--pc-border-strong); color: var(--pc-text); }
.pc-lang-select {
  border: none;
  background: transparent;
  font: inherit;
  color: var(--pc-text);
  cursor: pointer;
  outline: none;
  max-width: 120px;
}
.pc-lang-select:disabled { opacity: 0.6; cursor: wait; }
</style>
