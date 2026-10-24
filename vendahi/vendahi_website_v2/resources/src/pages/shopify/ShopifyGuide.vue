<template>
  <div class="guide">
    <p class="lead">
      <template v-if="store?.id">
        What this shop needs in order to keep itself in step. Everything here is specific to
        this store — the paths and ids are real, so the commands can be copied straight onto
        the server.
      </template>
      <template v-else>
        How a Shopify shop keeps itself in step with the ERP. Pick a store above to see its
        own state, or read it as it is — the cron line and the webhook URL are the same
        whichever store you connect.
      </template>
    </p>

    <!-- The choice, before either tab is opened: a tab nobody clicks is a
         feature nobody finds. -->
    <a-table
      size="small" :columns="wayColumns" :data-source="wayRows" row-key="what"
      :pagination="false" :scroll="{ x: 640 }" class="ways"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'what'"><strong>{{ record.what }}</strong></template>
      </template>
    </a-table>
    <p class="note ways-note">
      Most shops run both: the first keeps the shop current minute to minute, the second
      catches whatever the first missed — a webhook that never arrived, an edit made directly
      in the Shopify admin, a night when the site was down.
    </p>

    <!-- ---------------------------------------------- common ground -->
    <h3 class="heading">Both ways need this first</h3>

    <section class="step">
      <h4><span class="num">1</span> Shopify credentials</h4>
      <p>
        Since January 2026 Shopify no longer creates custom apps inside the admin, so there are
        two kinds of connection:
      </p>
      <ul class="plain">
        <li>
          <strong>Dev Dashboard app</strong> (any shop connected now) —
          <em>dev.shopify.com → Apps → Create app</em>. Grant the Admin API scopes for what
          this store syncs, install the app on the shop, then copy the <strong>Client ID</strong>
          and <strong>Client secret</strong> from the app's Settings page into
          <strong>Edit store → Connection</strong>. The ERP exchanges them for an access token
          itself and renews it before it expires every 24 hours. The client secret also signs
          webhooks, so nothing else is needed. The app and the shop must sit in the same
          Shopify organisation.
        </li>
        <li>
          <strong>Legacy custom app</strong> (created in the admin before 2026) — keeps working
          unchanged with its <strong>Admin API access token</strong> (<code>shpat_…</code>) and
          its <strong>API secret key</strong> as the webhook signing secret. Not the same value
          as the token, and getting them the wrong way round is the usual reason webhooks are
          refused.
        </li>
        <li v-if="store?.id">
          Webhook signing secret:
          <a-tag v-if="store.has_webhook_secret" color="success">stored</a-tag>
          <a-tag v-else color="warning">missing — deliveries will be refused</a-tag>
        </li>
      </ul>
      <a-table
        size="small" :columns="scopeColumns" :data-source="scopeRows" row-key="entity"
        :pagination="false" :scroll="{ x: 520 }" class="scope-table"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'entity'">
            {{ record.label }}
            <a-tag v-if="store?.id" :color="record.on ? 'success' : 'default'">
              {{ record.on ? 'On' : 'Off' }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'scopes'">
            <code class="scopes">{{ record.scopes }}</code>
          </template>
        </template>
      </a-table>
    </section>

    <section class="step">
      <h4><span class="num">2</span> Where data lands</h4>
      <p>
        <em>Edit store → Where data lands.</em> The ERP warehouse whose stock this shop sells,
        and the Shopify location that stock is written to. Inventory sync and order import both
        refuse to run without them.
      </p>
      <p v-if="store?.id">
        Currently: warehouse <strong>{{ store.warehouse_name || 'not set' }}</strong>,
        location <strong>{{ store.location_id || 'not set' }}</strong>
        <a-tag v-if="store.warehouse_id && store.location_id" color="success">ready</a-tag>
        <a-tag v-else color="error">incomplete</a-tag>
      </p>
    </section>

    <!-- ---------------------------------------------- the two ways -->
    <h3 class="heading">Then pick how it keeps in step</h3>

    <a-tabs v-model:activeKey="way" type="card" class="way-tabs">
      <!-- ------------------------------------------- real time -->
      <a-tab-pane key="realtime">
        <template #tab>
          <span>
            Right away
            <a-tag v-if="store?.id" :color="store.instant_stock_push ? 'success' : 'default'" class="tab-tag">
              {{ store.instant_stock_push ? 'stock live' : 'stock not live' }}
            </a-tag>
          </span>
        </template>

        <p class="pane-lead">
          Each change is sent as it happens, in seconds. Two halves, and they are separate
          switches: <strong>webhooks</strong> bring the shop's own events in, and
          <strong>instant stock push</strong> sends stock out.
        </p>

        <section class="step">
          <h4><span class="num">A</span> Webhooks — the shop → the ERP</h4>
          <p>
            Orders, order status changes, and product, customer and stock edits made in Shopify.
            Open the store and press <strong>Register in Shopify</strong> on its
            <a v-if="store?.id" class="link" @click="$emit('goto', 'webhooks')">Webhooks tab</a>
            <span v-else>Webhooks tab</span> — safe to press twice. Shopify posts to:
          </p>
          <div class="cmd">
            <code>{{ webhookUrl }}</code>
            <a-button size="small" @click="copy(webhookUrl, 'URL copied')">Copy</a-button>
          </div>
          <p class="note">
            The address must be reachable from the internet over HTTPS — Shopify will not deliver
            to <code>localhost</code> or a <code>.test</code> domain. Each delivery is recorded
            once, so Shopify's retries cannot create a duplicate order, and a failed one can be
            replayed. Deliveries are refused until the signing secret from step 1 is stored.
          </p>
        </section>

        <section class="step">
          <h4><span class="num">B</span> Instant stock push — the ERP → the shop</h4>
          <a-alert type="warning" show-icon class="callout" message="This is the setting people miss">
            <template #description>
              <p>
                A POS sale does <strong>not</strong> reach Shopify by itself. Left off, the shop
                only finds out on the next Inventory push step — and in that window it can sell a
                unit you have already sold at the till.
              </p>
              <p>
                Tick <strong>Push stock the moment it changes</strong> on
                <em>Edit store → Automatic sync</em>. That is the whole setup — no worker, no
                cron. Every sale, return and adjustment then sends a push of just those
                products, right after the sale is saved, and a twenty-line sale costs one push,
                not twenty. The till never waits for it.
              </p>
              <p v-if="store?.id" class="callout-state">
                This store:
                <a-tag :color="store.instant_stock_push ? 'success' : 'warning'">
                  {{ store.instant_stock_push ? 'on — stock pushed on change' : 'off — stock on the schedule only' }}
                </a-tag>
                <a v-if="canEdit && !store.instant_stock_push" class="link" @click="$emit('edit', 'auto')">Turn it on</a>
              </p>
            </template>
          </a-alert>
          <p class="note">
            Only products that already have a Shopify mapping, and only movements in this store's
            warehouse, are pushed. Bulk operations — deleting a product, resetting a warehouse —
            write stock without firing an event, which is one reason to keep the scheduled
            inventory step as well.
          </p>
        </section>

        <section class="step">
          <h4><span class="num">C</span> Nothing else to install</h4>
          <p class="note">
            Neither half needs a queue worker or a cron entry. A webhook coming in is handled in
            the request Shopify makes; a stock push going out runs in the same process that
            served the sale, <em>after</em> the response has gone back — so the till is never
            waiting on Shopify, and a shop that stops answering is given up on after ten seconds
            and left to the next scheduled inventory push.
          </p>
          <p class="note">
            One exception: a stock change made by a background task has no response to come
            after, so it is queued on <code>webhooks</code> instead.
            <template v-if="cron.managed">
              Your platform already runs a worker on that queue, so those go out on their own too.
            </template>
            <template v-else>
              If you want those to go out promptly too, drain that queue:
            </template>
          </p>
          <div v-if="!cron.managed" class="cmd">
            <code>{{ cron.queue }}</code>
            <a-button size="small" @click="copy(cron.queue, 'Cron line copied')">Copy</a-button>
          </div>
        </section>
      </a-tab-pane>

      <!-- ------------------------------------------- scheduled -->
      <a-tab-pane key="scheduled">
        <template #tab>
          <span>
            On a schedule
            <a-tag v-if="store?.id" :color="store.auto_sync ? 'success' : 'default'" class="tab-tag">
              {{ store.auto_sync ? 'on' : 'off' }}
            </a-tag>
          </span>
        </template>

        <p class="pane-lead">
          Everything is re-checked every so often, both ways. Slower than real time, but it is
          what pushes stock out on shops that do not use instant push, what catches a missed
          webhook, and the only way to move a whole catalogue.
        </p>

        <section v-if="cron.managed" class="step">
          <h4><span class="num">A</span> Nothing to install</h4>
          <p>
            The scheduler is run for you: every five minutes your platform asks each store
            whether any step of its plan is due, and runs the ones that are. There is no cron
            entry to add and no server to keep.
          </p>
          <p class="note">
            Five minutes is also the finest resolution a plan can use, because five minutes is
            the shortest step interval. Longer intervals are honoured exactly — a step set to
            30 minutes is checked six times and runs once.
          </p>
        </section>
        <section v-else class="step">
          <h4><span class="num">A</span> The cron entry</h4>
          <p>
            Nothing scheduled runs without it. One line, this module only — add it next to
            whatever else you have chosen to schedule:
          </p>
          <div class="cmd">
            <code>{{ cron.sync }}</code>
            <a-button size="small" @click="copy(cron.sync, 'Cron line copied')">Copy</a-button>
          </div>
          <p class="note">
            Every five minutes is the finest resolution a plan can use, because five minutes is
            the shortest step interval. A coarser entry simply becomes the floor — with
            <code>*/15</code>, a step set to 5 minutes runs every 15. On Windows use Task
            Scheduler: program <code>php</code>, arguments <code>artisan shopify:sync</code>,
            started in <code>{{ cron.base_path }}</code>.
          </p>
        </section>

        <section class="step">
          <h4><span class="num">B</span> Switch it on, and choose the plan</h4>
          <p>
            <em>Edit store → Automatic sync.</em> The switch decides whether the scheduler may
            act at all; the plan decides what it does. A <strong>step</strong> is one entity in one direction
            with its own interval — <em>Orders · Shopify → ERP every 15 min</em>,
            <em>Inventory · ERP → Shopify every 30 min</em>. The default interval is used by any
            step that leaves its own box empty.
          </p>
          <p class="note">
            Leave the plan empty and the store pulls orders and pushes stock at the default
            interval, which is what most shops want. Orders are pull-only: Shopify is where a
            customer places one.
          </p>
          <p v-if="store?.id">
            This store:
            <a-tag :color="store.auto_sync ? 'success' : 'default'">
              {{ store.auto_sync ? 'schedule on' : 'schedule off — manual only' }}
            </a-tag>
            <a v-if="canEdit" class="link" @click="$emit('edit', 'auto')">Change</a>
          </p>
        </section>

        <section class="step">
          <h4><span class="num">C</span> When a step actually runs</h4>
          <p class="note">
            A step is due when that same step last <em>started</em> longer ago than its interval,
            read from the run history. So a sync you start by hand postpones the next automatic
            one, and a step that keeps failing is retried on its interval rather than every tick.
            The store's Overview shows each step's last run, next due time, and the reason it is
            blocked when it is. Run the first catalogue push by hand from the Sync Centre —
            as a dry run first — before putting it on a schedule.
          </p>
        </section>
      </a-tab-pane>
    </a-tabs>

    <!-- ---------------------------------------------- reference -->
    <h3 class="heading">Reference</h3>

    <section class="step">
      <h4><span class="num">⚙</span> Where each setting lives</h4>
      <p class="note">
        All of these are on an existing store: <strong>Shopify → Stores</strong>, then the pencil
        (or <strong>Edit store</strong> on the store's own page).
      </p>
      <a-table
        size="small" :columns="settingColumns" :data-source="settingRows" row-key="setting"
        :pagination="false" :scroll="{ x: 560 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'where'"><strong>{{ record.where }}</strong></template>
        </template>
      </a-table>
    </section>

    <section class="step">
      <h4><span class="num">!</span> Product images</h4>
      <ul class="plain">
        <li>
          Pictures go out with the product on a <strong>products push</strong> — the main image
          first, then the gallery, then one per variant that has its own, up to fifty.
        </li>
        <li>
          A variant's picture is uploaded with the product and then <strong>assigned to that
          variant</strong>, which is how Shopify models it: a variant points at one of the
          product's images. A variant reusing the main image does not upload it twice.
        </li>
        <li>
          An image stored as a file is <strong>uploaded</strong> to Shopify, not linked, so it
          works even when this installation is not reachable from the internet. An image added
          as a pasted link is handed to Shopify as that URL, which must be publicly reachable.
        </li>
        <li>
          They are only re-sent when they actually change — a push that only alters a price
          leaves the shop's images alone, because sending them replaces the whole set.
        </li>
        <li>
          Removing an image in the ERP does <em>not</em> delete it in Shopify. Delete it there
          too, or the shop keeps showing it.
        </li>
      </ul>
    </section>

    <section v-if="!cron.managed" class="step">
      <h4><span class="num">⌘</span> Commands worth knowing</h4>
      <div v-for="item in commands" :key="item.cmd" class="cmd cmd--row">
        <div class="cmd-body">
          <code>{{ item.cmd }}</code>
          <span class="cmd-what">{{ item.what }}</span>
        </div>
        <a-button size="small" @click="copy(item.cmd, 'Command copied')">Copy</a-button>
      </div>
    </section>

    <p class="note foot">
      Every sync writes to <strong>Shopify → Logs</strong>, and each store's Overview shows when
      each step last ran, when it is next due, and what is blocking it when something is. That is
      where to look first when a sync did not do what you expected.
    </p>
  </div>
</template>

<script setup>
/**
 * The setup guide, shared by the Shopify → Guide page and the Setup guide tab
 * on a store. One copy, so the two cannot drift; what changes between them is
 * only whether a store is in hand — with one, the guide reports that store's
 * actual state next to each step.
 *
 * Laid out as the module actually works: what both ways need, then a tab per
 * way, because their setup differs (a queue worker versus a cron entry) and
 * reading the steps for the path you are not taking is how people end up
 * configuring neither. The comparison table stays outside the tabs so the
 * choice is visible without opening either one.
 */
import { ref, computed } from 'vue';
import { message } from 'ant-design-vue';
import { ENTITIES } from './shopifyOptions';

const props = defineProps({
  /** The store this guide is about, or null for the generic version. */
  store: { type: Object, default: null },
  /**
   * Scheduling facts from the server. `managed` means the platform runs the
   * scheduler and the queue itself, which is the hosted case — the guide then
   * says what happens instead of what to install, because a tenant has no
   * crontab to add a line to.
   */
  cron: { type: Object, default: () => ({ managed: false, base_path: '', sync: '', queue: '' }) },
  webhookUrl: { type: String, default: '' },
  /** Whether to offer the "Change these" shortcut into the edit form. */
  canEdit: { type: Boolean, default: false },
});

defineEmits(['goto', 'edit']);

// Real time first: it is the half people do not know is there.
const way = ref('realtime');

const wayColumns = [
  { title: '', key: 'what', dataIndex: 'what', width: 130 },
  { title: 'Right away', dataIndex: 'realtime', key: 'realtime' },
  { title: 'On a schedule', dataIndex: 'scheduled', key: 'scheduled' },
];

const wayRows = [
  {
    what: 'Covers',
    realtime: 'Orders and edits made in Shopify; stock leaving the ERP (a POS sale, a return, an adjustment)',
    scheduled: 'Everything, both ways — including catalogue pushes and anything the fast path missed',
  },
  { what: 'Speed', realtime: 'Seconds', scheduled: "The step's interval (5 minutes to 7 days)" },
  {
    what: 'Needs',
    realtime: 'A public HTTPS address and the signing secret for incoming webhooks. Outgoing stock needs nothing at all',
    scheduled: 'The scheduler, every five minutes',
  },
  {
    what: 'Switched on at',
    realtime: 'Webhooks tab → Register in Shopify · Automatic sync → Push stock the moment it changes',
    scheduled: 'Automatic sync → Sync automatically on a schedule, plus the plan',
  },
];

/**
 * The Admin API scopes each entity needs, next to whether this store actually
 * syncs it — so the list to grant in Shopify is the one on screen, not a
 * generic table to filter mentally.
 */
const SCOPES = {
  products: 'read_products, write_products',
  inventory: 'read_inventory, write_inventory, read_locations',
  customers: 'read_customers, write_customers',
  orders: 'read_orders (read_all_orders for orders over 60 days old)',
  collections: 'read_products, write_products',
  fulfillments: 'read/write_merchant_managed_fulfillment_orders, read/write_assigned_fulfillment_orders',
};

const scopeColumns = [
  { title: 'Entity', key: 'entity', width: 150 },
  { title: 'Admin API scopes', key: 'scopes' },
];

const scopeRows = computed(() => ENTITIES.map(e => ({
  entity: e.value,
  label: e.label,
  on: !!props.store?.[`sync_${e.value}`],
  scopes: SCOPES[e.value] || '',
})));

const settingColumns = [
  { title: 'Setting', dataIndex: 'setting', key: 'setting', width: 230 },
  { title: 'Tab in Edit store', key: 'where', width: 170 },
  { title: '', dataIndex: 'what', key: 'what' },
];

const settingRows = [
  { setting: 'Client ID + secret or access token, signing secret, API version', where: 'Connection', what: 'The credentials from the Shopify app (Dev Dashboard or legacy custom app).' },
  { setting: 'ERP warehouse, Shopify location', where: 'Where data lands', what: 'Which stock this shop sells, and where orders are filed.' },
  { setting: 'Price sent to Shopify', where: 'Where data lands', what: 'Retail, wholesale or minimum price.' },
  { setting: 'Create missing products / customers', where: 'Where data lands', what: 'Whether a pull may create ERP records it cannot match.' },
  { setting: 'Which entities sync at all', where: 'What to sync', what: 'Off here means off everywhere — manual, scheduled and webhook.' },
  { setting: 'Sync automatically on a schedule', where: 'Automatic sync', what: 'The scheduler switch.' },
  { setting: 'Push stock the moment it changes', where: 'Automatic sync', what: 'Instant stock push, queued on a sale or adjustment.' },
  { setting: 'Default interval, and the plan of steps', where: 'Automatic sync', what: 'What runs by itself, which way, and how often.' },
];

const commands = computed(() => {
  const id = props.store?.id;

  return [
    { cmd: 'php artisan shopify:sync --list', what: 'Every store: what would run, and when. Changes nothing.' },
    { cmd: `php artisan shopify:sync --store=${id || '<id>'} --force`, what: "One store's whole plan now, ignoring the clock." },
    { cmd: `php artisan shopify:sync --store=${id || '<id>'} --entity=products --direction=push --only-unsynced`, what: 'First catalogue push: only products not yet linked.' },
    { cmd: `php artisan shopify:sync --store=${id || '<id>'} --entity=orders --direction=pull --since=2026-01-01`, what: 'Backfill orders from a date.' },
    { cmd: `php artisan shopify:sync --store=${id || '<id>'} --entity=inventory --direction=push --dry-run`, what: 'Walk it and report, writing nothing anywhere.' },
  ];
});

function copy(text, label = 'Copied') {
  if (!text) return;
  navigator.clipboard?.writeText(text)
    .then(() => message.success(label))
    // No clipboard (insecure origin, locked-down browser): show the text so it
    // can still be selected by hand rather than failing silently.
    .catch(() => message.info(text));
}
</script>

<style scoped>
.guide {
  max-width: 900px;
}
.lead {
  font-size: 13px;
  opacity: 0.7;
  margin-bottom: 14px;
}
.heading {
  margin: 22px 0 12px;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  opacity: 0.55;
}
.ways {
  margin-bottom: 8px;
}
.ways-note {
  margin-bottom: 4px;
}
.way-tabs {
  margin-top: -4px;
}
.tab-tag {
  margin-inline-start: 6px;
  font-size: 10.5px;
  line-height: 16px;
}
.pane-lead {
  margin: 4px 0 16px;
  font-size: 13px;
  line-height: 1.6;
  opacity: 0.8;
}
.step {
  padding: 0 0 18px;
  margin-bottom: 18px;
  border-bottom: 1px solid rgba(128, 128, 128, 0.16);
}
.step:last-of-type {
  border-bottom: 0;
  margin-bottom: 0;
}
.step h4 {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 0 0 8px;
  font-size: 14px;
  font-weight: 600;
}
.num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(109, 40, 217, 0.14);
  font-size: 12px;
  flex: none;
}
.step p {
  margin: 0 0 8px;
  font-size: 13px;
  line-height: 1.6;
}
.plain {
  margin: 0 0 10px;
  padding-inline-start: 18px;
  font-size: 13px;
  line-height: 1.75;
}
.note {
  font-size: 12px;
  opacity: 0.68;
  line-height: 1.6;
}
.note.foot {
  margin-top: 4px;
}
.step code,
.note code {
  padding: 2px 6px;
  border-radius: 5px;
  background: rgba(128, 128, 128, 0.14);
  font-size: 11.5px;
  word-break: break-all;
}
.cmd {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin: 8px 0 10px;
  padding: 8px 10px;
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 10px;
}
.cmd code {
  flex: 1 1 320px;
  background: none;
  padding: 0;
  font-size: 12px;
}
.cmd--row {
  align-items: flex-start;
}
.cmd-body {
  display: flex;
  flex-direction: column;
  gap: 3px;
  flex: 1 1 320px;
  min-width: 0;
}
.cmd-what {
  font-size: 11.5px;
  opacity: 0.6;
}
.scope-table {
  margin-top: 10px;
}
.scopes {
  font-size: 11px;
  background: none;
  padding: 0;
}
.link {
  color: #5f9e3f;
  cursor: pointer;
}
.callout {
  margin: 4px 0 12px;
}
.callout :deep(p) {
  margin: 0 0 6px;
  font-size: 12.5px;
  line-height: 1.6;
}
.callout :deep(p:last-child) {
  margin-bottom: 0;
}
.callout-state {
  margin-top: 8px;
}
</style>
