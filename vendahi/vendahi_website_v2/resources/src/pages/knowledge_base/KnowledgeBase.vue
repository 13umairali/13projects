<template>
  <div class="page">
    <PageHeader :title="$t('Knowledge_Base')" :breadcrumb="[$t('Knowledge_Base')]">
      <template #actions>
      </template>
    </PageHeader>

    <!-- Hero: centered search on the brand gradient. Searches whichever tab is open. -->
    <div class="kb-hero">
      <div class="kb-hero-inner">
        <a-typography-title :level="4" class="kb-hero-title">
          {{ tf('KB_Hero_Title', 'How can we help?') }}
        </a-typography-title>
        <a-typography-text class="kb-hero-subtitle">
          {{ tf('KB_Hero_Subtitle', 'Search guides and answers from your team’s knowledge base.') }}
        </a-typography-text>
        <a-input
          v-model:value="q"
          class="kb-hero-search"
          size="large"
          allow-clear
          :placeholder="$t('Search_this_table')"
          @input="onSearchInput"
        >
          <template #prefix><SearchOutlined /></template>
        </a-input>
      </div>
    </div>

    <!-- Help Center = the super admin's central articles (read-only).
         Our Articles = this tenant's own, written here. -->
    <a-tabs v-model:activeKey="tab" class="kb-tabs">
      <a-tab-pane key="help" :tab="tf('KB_Tab_Help', 'Help Center')" />
      <a-tab-pane key="team" :tab="tf('KB_Tab_Team', 'Our Articles')" />
      <template v-if="tab === 'team' && canManage" #rightExtra>
        <a-space>
          <a-button @click="openGroups">
            <template #icon><FolderOutlined /></template>
            {{ tf('KB_Manage_Groups', 'Manage Groups') }}
          </a-button>
          <a-button type="primary" @click="openCreate">
            <template #icon><PlusOutlined /></template>
            {{ $t('Create') }}
          </a-button>
        </a-space>
      </template>
    </a-tabs>

    <!-- Group filter chips with live counts, per tab. -->
    <div class="kb-chips">
      <button
        type="button"
        class="kb-chip"
        :class="{ active: groupId === undefined }"
        @click="selectGroup(undefined)"
      >
        {{ $t('All_Groups') }}
        <span class="kb-chip-count">{{ totalAll }}</span>
      </button>
      <button
        v-for="g in groups"
        :key="g.id"
        type="button"
        class="kb-chip"
        :class="{ active: groupId === g.id }"
        @click="selectGroup(groupId === g.id ? undefined : g.id)"
      >
        {{ g.name }}
        <span class="kb-chip-count">{{ countByGroup[g.id] || 0 }}</span>
      </button>
    </div>

    <div v-if="loading" style="display: flex; justify-content: center; padding: 96px 0">
      <a-spin size="large" />
    </div>

    <a-card v-else-if="!articles.length" class="kb-empty">
      <a-empty :description="null">
        <template #image><ReadOutlined class="kb-empty-icon" /></template>
        <a-typography-title :level="5" style="margin: 0 0 4px">
          {{ q || groupId ? $t('NodataAvailable') : tf('KB_Empty_Title', 'No articles yet') }}
        </a-typography-title>
        <a-typography-text type="secondary">
          {{ emptyHint }}
        </a-typography-text>
        <div v-if="tab === 'team' && canManage && !q && !groupId" style="margin-top: 16px">
          <a-button type="primary" @click="openCreate">
            <template #icon><PlusOutlined /></template>
            {{ $t('Create') }}
          </a-button>
        </div>
      </a-empty>
    </a-card>

    <a-row v-else :gutter="[16, 16]">
      <a-col v-for="article in articles" :key="article.id" :xs="24" :md="12" :xl="8">
        <a-card
          class="kb-card"
          role="link"
          tabindex="0"
          @click="openArticle(article)"
          @keydown.enter="openArticle(article)"
        >
          <div class="kb-card-head">
            <span class="kb-card-icon"><FileTextOutlined /></span>
            <a-space :size="4" wrap>
              <a-tag v-if="article.group?.name" :bordered="false">{{ article.group.name }}</a-tag>
              <a-tag v-if="Number(article.is_internal)" :bordered="false" color="warning">{{ $t('Internal') }}</a-tag>
              <a-tag v-if="!article.published_at" :bordered="false" color="default">
                {{ tf('Draft', 'Draft') }}
              </a-tag>
            </a-space>
          </div>
          <a-typography-text strong class="kb-card-title">{{ article.title }}</a-typography-text>
          <a-typography-paragraph
            type="secondary"
            :content="excerpt(article)"
            :ellipsis="{ rows: 2 }"
            class="kb-card-excerpt"
          />
          <div class="kb-card-foot">
            <a-typography-text type="secondary" style="font-size: 12px">
              <template v-if="article.published_at">{{ date(article.published_at) }}</template>
            </a-typography-text>
            <!-- Edit/delete live on the card itself; @click.stop keeps them from
                 opening the article behind them. -->
            <a-space v-if="tab === 'team' && canManage" :size="4">
              <a-button size="small" type="text" @click.stop="openEdit(article)">
                <template #icon><EditOutlined /></template>
              </a-button>
              <a-button size="small" type="text" danger @click.stop="confirmDelete(article)">
                <template #icon><DeleteOutlined /></template>
              </a-button>
            </a-space>
            <span v-else class="kb-card-arrow"><ArrowRightOutlined /></span>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <!-- Article editor (tenant articles only). -->
    <a-modal
      v-model:open="formOpen"
      :title="form.id ? $t('Edit') : $t('Create')"
      :confirm-loading="saving"
      width="960px"
      @ok="submit"
    >
      <a-form layout="vertical" style="margin-top: 12px">
        <a-row :gutter="16">
          <a-col :xs="24" :md="14">
            <a-form-item :label="$t('Name') + ' *'">
              <a-input v-model:value="form.title" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="10">
            <a-form-item :label="$t('Group') + ' *'">
              <a-space-compact style="display: flex">
                <a-select
                  v-model:value="form.knowledge_base_article_group_id"
                  style="flex: 1"
                  :options="groupOptions"
                  :placeholder="tf('KB_Pick_Group', 'Pick a group')"
                />
                <a-button @click="newGroupOpen = !newGroupOpen">
                  <template #icon><PlusOutlined /></template>
                </a-button>
              </a-space-compact>
              <!-- Articles cannot exist without a group, so a tenant starting
                   from zero needs a way out that is not "go elsewhere first". -->
              <div v-if="newGroupOpen" class="kb-newgroup">
                <a-input
                  v-model:value="newGroupName"
                  size="small"
                  :placeholder="tf('KB_New_Group', 'New group name')"
                  @press-enter="createGroupInline"
                />
                <a-button size="small" type="primary" :loading="groupSaving" @click="createGroupInline">
                  {{ $t('Save') }}
                </a-button>
              </div>
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('Content')">
          <RichTextEditor v-model="form.content" />
        </a-form-item>
        <a-row :gutter="16" align="middle">
          <a-col :xs="24" :md="8">
            <a-form-item :label="$t('Sort_Order')">
              <a-input-number v-model:value="form.sort_order" :min="0" style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="12" :md="8">
            <a-form-item :label="$t('Status')">
              <a-switch v-model:checked="form.published" />
              <span style="margin-inline-start: 8px">
                {{ form.published ? $t('Published') : tf('Draft', 'Draft') }}
              </span>
            </a-form-item>
          </a-col>
          <a-col :xs="12" :md="8">
            <a-form-item :label="$t('Internal')">
              <a-switch v-model:checked="form.is_internal" />
              <span style="margin-inline-start: 8px">
                {{ tf('KB_Internal_Hint', 'Hidden from staff without KB access') }}
              </span>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
    </a-modal>

    <!-- Group manager. -->
    <a-modal
      v-model:open="groupsOpen"
      :title="tf('KB_Manage_Groups', 'Manage Groups')"
      :footer="null"
      width="640px"
    >
      <div class="kb-newgroup" style="margin: 8px 0 16px">
        <a-input
          v-model:value="newGroupName"
          :placeholder="tf('KB_New_Group', 'New group name')"
          @press-enter="createGroupInline"
        />
        <a-button type="primary" :loading="groupSaving" @click="createGroupInline">
          <template #icon><PlusOutlined /></template>
          {{ $t('Create') }}
        </a-button>
      </div>
      <a-list :data-source="teamGroups" size="small" :locale="{ emptyText: $t('NodataAvailable') }">
        <template #renderItem="{ item }">
          <a-list-item>
            <a-input
              v-if="editingGroupId === item.id"
              v-model:value="editingGroupName"
              size="small"
              style="max-width: 280px"
              @press-enter="saveGroup(item)"
            />
            <span v-else>
              {{ item.name }}
              <a-tag :bordered="false" style="margin-inline-start: 8px">{{ item.articles_count || 0 }}</a-tag>
            </span>
            <template #actions>
              <template v-if="editingGroupId === item.id">
                <a-button size="small" type="primary" :loading="groupSaving" @click="saveGroup(item)">
                  {{ $t('Save') }}
                </a-button>
                <a-button size="small" @click="editingGroupId = null">{{ $t('Cancel') }}</a-button>
              </template>
              <template v-else>
                <a-button size="small" type="text" @click="startEditGroup(item)">
                  <template #icon><EditOutlined /></template>
                </a-button>
                <a-button size="small" type="text" danger @click="confirmDeleteGroup(item)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </template>
            </template>
          </a-list-item>
        </template>
      </a-list>
    </a-modal>
  </div>
</template>

<script setup>
/**
 * Two knowledge bases share this page, on separate tabs because their ids are
 * unrelated:
 *
 *   Help Center  GET knowledge-base/articles + knowledge-base/groups/for-filter
 *                — the super admin's central articles, read-only.
 *   Our Articles GET/POST/PUT/DELETE knowledge-base/team/{articles,groups}
 *                — this tenant's own, stored tenant-side, editable by anyone
 *                with knowledge_base_view (same permission the page needs).
 *
 * Both list endpoints take {per_page, page, q?, group_id?} and answer with a
 * Laravel paginator (`data.data`) or a plain array; search is debounced and
 * both filters stay server-side (legacy parity). Chip counts come from the
 * latest unfiltered load of the active tab.
 */
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { message, Modal } from 'ant-design-vue';
import {
  PlusOutlined, FolderOutlined, SearchOutlined, ReadOutlined, EditOutlined,
  DeleteOutlined, FileTextOutlined, ArrowRightOutlined,
} from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import RichTextEditor from '../../components/RichTextEditor.vue';
import { t as tf } from '../../i18n';
import { useFormat } from '../../composables/useFormat';
import { useAuthStore } from '../../stores/auth';
import http from '../../lib/http';

const { t } = useI18n();
const { date } = useFormat();
const router = useRouter();
const auth = useAuthStore();

// Reading and writing sit behind the same permission here (see the policies),
// so this only ever hides the buttons in the fail-open/demo case.
const canManage = computed(() => auth.can('knowledge_base_view'));

const tab = ref('help');
const loading = ref(true);
const q = ref('');

// Each tab keeps its own filter + result set so switching back is instant and
// a group id from one source can never be applied to the other.
const helpGroupId = ref(undefined);
const teamGroupId = ref(undefined);
const helpArticles = ref([]);
const teamArticles = ref([]);
const helpGroups = ref([]);
const teamGroups = ref([]);
const helpCounts = ref({});
const teamCounts = ref({});
const helpTotal = ref(0);
const teamTotal = ref(0);

const isTeam = computed(() => tab.value === 'team');
const articles = computed(() => (isTeam.value ? teamArticles.value : helpArticles.value));
const groups = computed(() => (isTeam.value ? teamGroups.value : helpGroups.value));
const countByGroup = computed(() => (isTeam.value ? teamCounts.value : helpCounts.value));
const totalAll = computed(() => (isTeam.value ? teamTotal.value : helpTotal.value));
const groupId = computed(() => (isTeam.value ? teamGroupId.value : helpGroupId.value));

const groupOptions = computed(() => teamGroups.value.map(g => ({ value: g.id, label: g.name })));

const emptyHint = computed(() => {
  if (q.value || groupId.value) return tf('KB_Empty_Filtered', 'Try a different search or group.');
  if (isTeam.value) return tf('KB_Empty_Hint', 'Write the first article so your team can find answers here.');
  return tf('KB_Empty_Help', 'No help articles have been published yet.');
});

function excerpt(article) {
  // Content is Quill HTML; strip tags for the card preview.
  const div = document.createElement('div');
  div.innerHTML = article.content || '';
  return (div.textContent || '').trim().slice(0, 180);
}

function openArticle(article) {
  router.push(isTeam.value ? `/kb/team-articles/${article.id}` : `/kb/articles/${article.id}`);
}

async function load() {
  loading.value = true;
  const team = isTeam.value;
  try {
    const params = { per_page: 100, page: 1 };
    if (q.value) params.q = q.value;
    if (groupId.value) params.group_id = groupId.value;
    const data = await http.get(team ? 'knowledge-base/team/articles' : 'knowledge-base/articles', params);
    const list = Array.isArray(data) ? data : data.data || [];
    if (team) teamArticles.value = list;
    else helpArticles.value = list;

    // Chip counts track the latest unfiltered result set.
    if (!groupId.value) {
      const counts = {};
      for (const a of list) {
        const gid = a.knowledge_base_article_group_id ?? a.group?.id;
        if (gid != null) counts[gid] = (counts[gid] || 0) + 1;
      }
      if (team) {
        teamTotal.value = list.length;
        teamCounts.value = counts;
      } else {
        helpTotal.value = list.length;
        helpCounts.value = counts;
      }
    }
  } catch (e) {
    if (team) teamArticles.value = [];
    else helpArticles.value = [];
  } finally {
    loading.value = false;
  }
}

function selectGroup(id) {
  if (isTeam.value) teamGroupId.value = id;
  else helpGroupId.value = id;
  load();
}

watch(tab, () => {
  load();
  if (isTeam.value && !teamGroups.value.length) loadTeamGroups();
});

let searchTimer = null;
function onSearchInput() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(load, 350);
}
onBeforeUnmount(() => clearTimeout(searchTimer));

async function loadHelpGroups() {
  try {
    // The central API exposes its categories read-only (articles are managed
    // by the super admin); only the tenant's own groups below are editable.
    const data = await http.get('knowledge-base/groups/for-filter');
    helpGroups.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) { /* filter stays empty */ }
}

async function loadTeamGroups() {
  try {
    const data = await http.get('knowledge-base/team/groups');
    teamGroups.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) { /* filter stays empty */ }
}

// ── Article editor ──

const formOpen = ref(false);
const saving = ref(false);
const emptyForm = () => ({
  id: null,
  title: '',
  knowledge_base_article_group_id: undefined,
  content: '',
  sort_order: 0,
  published: true,
  is_internal: false,
});
const form = ref(emptyForm());

function openCreate() {
  form.value = emptyForm();
  // One group? Pre-select it — the field is required and there is no choice.
  if (teamGroups.value.length === 1) {
    form.value.knowledge_base_article_group_id = teamGroups.value[0].id;
  }
  newGroupOpen.value = false;
  formOpen.value = true;
}

function openEdit(a) {
  form.value = {
    id: a.id,
    title: a.title,
    knowledge_base_article_group_id: a.knowledge_base_article_group_id ?? a.group?.id,
    content: a.content || '',
    sort_order: a.sort_order || 0,
    published: !!a.published_at,
    is_internal: !!a.is_internal,
  };
  newGroupOpen.value = false;
  formOpen.value = true;
}

async function submit() {
  if (!form.value.title) {
    message.warning(`${t('Name')} *`);
    return;
  }
  if (!form.value.knowledge_base_article_group_id) {
    message.warning(`${t('Group')} *`);
    return;
  }
  saving.value = true;
  const payload = {
    knowledge_base_article_group_id: form.value.knowledge_base_article_group_id,
    title: form.value.title,
    content: form.value.content || '',
    sort_order: form.value.sort_order || 0,
    published: form.value.published ? 1 : 0,
    is_internal: form.value.is_internal ? 1 : 0,
  };
  try {
    if (form.value.id) await http.put(`knowledge-base/team/articles/${form.value.id}`, payload);
    else await http.post('knowledge-base/team/articles', payload);
    message.success(form.value.id ? t('Successfully_Updated') : t('Successfully_Created'));
    formOpen.value = false;
    await Promise.all([load(), loadTeamGroups()]);
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    saving.value = false;
  }
}

function confirmDelete(a) {
  Modal.confirm({
    title: t('AreYouSure'),
    content: a.title,
    okText: t('Delete'),
    okType: 'danger',
    cancelText: t('Cancel'),
    async onOk() {
      try {
        await http.delete(`knowledge-base/team/articles/${a.id}`);
        message.success(t('Successfully_Deleted'));
        await Promise.all([load(), loadTeamGroups()]);
      } catch (e) {
        message.error(t('Failed'));
      }
    },
  });
}

// ── Groups ──

const groupsOpen = ref(false);
const groupSaving = ref(false);
const newGroupOpen = ref(false);
const newGroupName = ref('');
const editingGroupId = ref(null);
const editingGroupName = ref('');

function openGroups() {
  newGroupName.value = '';
  editingGroupId.value = null;
  groupsOpen.value = true;
  loadTeamGroups();
}

async function createGroupInline() {
  const name = newGroupName.value.trim();
  if (!name) return;
  groupSaving.value = true;
  try {
    const group = await http.post('knowledge-base/team/groups', { name });
    newGroupName.value = '';
    newGroupOpen.value = false;
    await loadTeamGroups();
    // Created from inside the article form: select it straight away.
    if (formOpen.value && group?.id) form.value.knowledge_base_article_group_id = group.id;
    message.success(t('Successfully_Created'));
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    groupSaving.value = false;
  }
}

function startEditGroup(g) {
  editingGroupId.value = g.id;
  editingGroupName.value = g.name;
}

async function saveGroup(g) {
  const name = editingGroupName.value.trim();
  if (!name) return;
  groupSaving.value = true;
  try {
    await http.put(`knowledge-base/team/groups/${g.id}`, { name });
    editingGroupId.value = null;
    await Promise.all([loadTeamGroups(), load()]);
    message.success(t('Successfully_Updated'));
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    groupSaving.value = false;
  }
}

function confirmDeleteGroup(g) {
  Modal.confirm({
    title: t('AreYouSure'),
    content: g.name,
    okText: t('Delete'),
    okType: 'danger',
    cancelText: t('Cancel'),
    async onOk() {
      try {
        await http.delete(`knowledge-base/team/groups/${g.id}`);
        message.success(t('Successfully_Deleted'));
        if (teamGroupId.value === g.id) teamGroupId.value = undefined;
        await Promise.all([loadTeamGroups(), load()]);
      } catch (e) {
        // The API refuses to drop a group that still holds articles, because
        // the article rows cannot survive without one.
        message.error(e?.data?.message || t('Failed'));
      }
    },
  });
}

onMounted(() => {
  load();
  loadHelpGroups();
});
</script>

<style scoped>
/* Brand accent is theme-fixed (colorPrimary #6d28d9); surfaces stay neutral
   rgba so antd's light/dark algorithm keeps owning the page background. */
.kb-hero {
  background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 60%, #a78bfa 100%);
  border-radius: 14px;
  padding: 36px 24px 40px;
  margin-bottom: 16px;
  text-align: center;
}
.kb-hero-inner {
  max-width: 560px;
  margin: 0 auto;
}
.kb-hero-title.ant-typography {
  color: #fff;
  margin: 0 0 4px;
}
.kb-hero-subtitle.ant-typography {
  color: rgba(255, 255, 255, 0.85);
  display: block;
  margin-bottom: 20px;
}
.kb-hero-search {
  border-radius: 10px;
}

.kb-tabs {
  margin-bottom: 4px;
}

.kb-newgroup {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.kb-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}
.kb-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid rgba(128, 128, 128, 0.3);
  background: transparent;
  color: inherit;
  border-radius: 999px;
  padding: 4px 12px;
  font-size: 13px;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s, background 0.2s;
}
.kb-chip:hover { border-color: #8b5cf6; color: #8b5cf6; }
.kb-chip:focus-visible { outline: 2px solid #8b5cf6; outline-offset: 2px; }
.kb-chip.active {
  background: #6d28d9;
  border-color: #6d28d9;
  color: #fff;
}
.kb-chip-count {
  font-size: 11px;
  opacity: 0.75;
  background: rgba(128, 128, 128, 0.18);
  border-radius: 999px;
  padding: 0 6px;
  line-height: 16px;
}
.kb-chip.active .kb-chip-count {
  background: rgba(255, 255, 255, 0.25);
  opacity: 1;
}

.kb-card {
  height: 100%;
  border-radius: 12px;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}
.kb-card :deep(.ant-card-body) {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
}
.kb-card:hover,
.kb-card:focus-visible {
  border-color: #8b5cf6;
  box-shadow: 0 6px 18px rgba(109, 40, 217, 0.14);
  transform: translateY(-2px);
  outline: none;
}
@media (prefers-reduced-motion: reduce) {
  .kb-card, .kb-chip { transition: none; }
  .kb-card:hover, .kb-card:focus-visible { transform: none; }
}
.kb-card-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
}
.kb-card-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 9px;
  flex: none;
  font-size: 16px;
  color: #6d28d9;
  background: rgba(109, 40, 217, 0.1);
}
.kb-card-title { font-size: 15px; }
.kb-card-excerpt.ant-typography {
  margin: 0;
  font-size: 13px;
  flex: 1;
}
.kb-card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.kb-card-arrow {
  color: #8b5cf6;
  opacity: 0;
  transform: translateX(-4px);
  transition: opacity 0.2s, transform 0.2s;
}
.kb-card:hover .kb-card-arrow,
.kb-card:focus-visible .kb-card-arrow {
  opacity: 1;
  transform: translateX(0);
}
[dir='rtl'] .kb-card-arrow { transform: translateX(4px) scaleX(-1); }
[dir='rtl'] .kb-card:hover .kb-card-arrow,
[dir='rtl'] .kb-card:focus-visible .kb-card-arrow { transform: translateX(0) scaleX(-1); }

.kb-empty { padding: 32px 0; text-align: center; }
.kb-empty-icon { font-size: 42px; color: rgba(128, 128, 128, 0.45); }
</style>
