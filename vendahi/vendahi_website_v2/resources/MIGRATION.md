# Stocky SaaS — Vue 3 + Ant Design migration plan

Front-end only. **No backend, controller, API or route file changes** — the plan
works inside the existing `Route::get('/{vue?}') → view('layouts.master')`
catch-all (web.php + tenant_web.php), which already serves any admin path,
including a `/next/*` prefix. Blade *views* under `resources/views` are treated
as front-end and may change; `routes/*` and `app/*` may not.

Reference implementation: `D:\laragon\www\stocky` commit `2d3b2b9 migrate to
vue3 & ant design` (+ follow-ups `26d9c59`, `9388131`, `83ec6ab`, `12728a4`)
and its `resources/MIGRATION.md`. Stocky finished as: Vue 3.5 + Ant Design Vue
4 + Pinia + vue-router 4 + vue-i18n 11, built by Vite 6 into `public/js/` with
a content-hashed manifest, served by `next.blade.php`; legacy app deleted.

Builds are never run automatically — the repo owner builds.

## Status

| Phase | State | Notes |
|---|---|---|
| 1 — Foundation | **done (2026-08-29), unbuilt** | `vue3-app/` package, `/next/*` shell, dashboard, plan gating, limit-reached modal, uploadUrl, legacy redirect guard |
| 2 — Simple CRUD | **done (2026-08-29), unbuilt** | 23 pages ported, 25 legacy paths in `migratedRoutes.json`. Adapted: Categories gained `show_in_store` (SaaS); Currencies lost the set-default switch and Payment Methods the active toggle (endpoints only exist in stocky); Brands via `uploadUrl()`. KB groups + hospital departments wait for their modules. |
| 3 — Reports | **done (2026-08-29), unbuilt** | 62 pages (60 reports + ProfitReport + 3D dashboard) incl. `ai_reports` and `sales_3d_dashboard` — decision 6 closed: stocky migrated both later, so they are ported here. 71 routes, 26 → 97 migrated paths (six `:id` detail reports use the new pattern matching in `config/migrated.js`). `resources/scripts/check-contracts.cjs` verified every endpoint against `tenant_api.php` and every `rowsKey` against the controller payload — all resolve. Menu generator now resolves `:to="{ name }"` (20 report links the sidebar declares by route name), `{ path, query }` links and `v-for` links: 252 leaves. |
| 4 — People & modules | **done (2026-08-29), unbuilt** | 175 routes / 151 pages ported in one scripted pass (`scripts/port-routes.cjs`): people, users, permissions, HRM, hospital, school, MRP, marketing, service, projects, tasks, recruit, fleet, accounting v2, commissions, meetings, assets, bookings, contracts, real estate, KB, kitchen, store, expenses, deposits, damages, promotions, subscriptions, customer-display setup, real-time counter. Dropped (no API here): stocky's asset workspace (dashboard/assignments/maintenance/transfers/depreciation/reports/details), supplier details page, KB groups + article editor (KB is read-only here; groups via `groups/for-filter`), 12 store pages (coupons, flash sales, menus, pages, popups, reviews, returns, quote requests, shipping, tax rates, payment gateway). Adapted: no `check_phone_duplicate` probe; supplier opening balance goes in the update payload; store order confirm = plain PATCH (no confirm-options), no invoice PDF; payment-proof review card stays dormant (payload never carries proofs). Links into still-legacy sales/quotations go through `goTo()`. |
| 5 — Documents | **done (2026-08-29), unbuilt** | 33 routes / 25 pages: sales, purchases, quotations, sale & purchase returns, adjustments, transfers, shipments, imports, document archive (+ `BatchAllocator`, `SerialPicker`, `PayDueModal`, return-payment modals, `lineCalc`). All contracts resolve (`batches_for_*`, `payment/returns_*` verified). **SaaS features stocky's forms lacked, now implemented:** promo codes + auto-applied promotions on the sale form (`promotions/applicable` / `validate_code`, `promotion_*` payload keys; `computeTotals` gained `promotionDiscount`), purchase extra charges + free-text custom fields (System Settings → Defaults toggles shipped by `purchases/create|edit`, included in `GrandTotal`), and entity custom fields on the customer/supplier forms via the new `components/CustomFieldsForm.vue` (`custom-fields`, `custom-field-values`). Detail pages use `uploadUrl('settings', logo)`. Line endings of `src/` normalized to LF (stocky files were CRLF). |
| 6 — Products | **done (2026-08-29), unbuilt** | 12 routes / 11 pages: list, form (stocky's `ProductForm` now has full single/variant/combo/pack parity — nothing left in legacy), details, count stock, batches, serial numbers (+ details), barcode, imports, opening stock. Dropped (no API here): vehicle fitment (page + drawer), storefront Q&A card on details, label-design persistence (kept per browser in localStorage instead) and direct network printing on the barcode page, and the storefront-only form fields this `ProductsController` ignores (size guide, shipping methods, related products, labels, tags, FAQ, returnable/classified, `preorder_always`). Added the SaaS FBR fields (`hs_code`, `fbr_uom`, shown when `fbr_enabled`). Images via `uploadUrl('products'|'count_stock', …)`. |
| 7 — Settings | **done (2026-08-29), unbuilt** | 25 stocky settings pages ported (system settings, POS settings + receipt designer, payment gateway, mail/SMS/email templates, custom fields, languages + translations editor, webhooks, backup, appearance, PWA, login devices, system health, module settings, QuickBooks, ZATCA, WooCommerce). **9 fresh pages written for SaaS-only features**: WhatsApp (settings, message logs, templates list + form — `apiResource whatsapp/templates`), FBR e-invoicing (config/documents/logs/guide), Shopify integration (one tabbed page + `composables/useShopifySync.js` replacing stocky's 5-page Shopify suite, which needs endpoints this API lacks), webhook delivery + incoming logs, and the tenant update page (`get_version_info`, `update/preflight|progress`, `one_click_update`). Dropped (no API): mobile-app settings, business-module toggles, and the System Settings sections invoice-PDF designer / module toggles / sidebar manager / demo data / direct network printing. Backup save spoofs `_method=put` like legacy. WhatsApp exists only in the legacy *vertical* sidebar layout, so it is added through `config/menuExtra.js` (merged in `stores/menu.js`). Contract checker now understands `Route::apiResource` and `::class` controllers. |
| 8 — SaaS-only pages | **done (2026-08-29), unbuilt** | Fresh: subscription billing (current plan with usage/features + cancel/resume/cancel-upgrade, change plan, checkout with online gateways + offline bank-transfer proof upload, payment success/failed result page incl. PayPal capture + pending polling, history + invoices with PDF download and retry) — 5 components / 7 routes on the `billing/*` API; support centre (tickets list with stat filters, create with attachments, thread detail with reply + 12 s polling) on `support/tickets`; 403 page. Ported from stocky: profile (same `Get_user_profile` / `update_user_profile` / `update_user_password` contract). Already covered earlier: subscription products (Phase 4), `ai_reports` + 3D dashboard (Phase 3). The legacy dashboard has no plan widgets, so no diff there. `two_factor_verify.vue` is an empty file in legacy — nothing to port. Gateways redirect to the legacy `/app/billing/success|failed?payment_id=…` URLs; the legacy guard forwards them with the query intact. |
| 9 — POS | **done (2026-08-30), unbuilt** | stocky `pages/pos/PosPage.vue` (19,457 lines) + `pos-compat/` (308 files: Bootstrap 4 + legacy skin SCSS verbatim, `bv`/`vee`/`vselect` shims, `posCompatMixin`, keyboard shortcuts, `ModernPaymentModal`, `PosReturnModal`, offline sync, cash drawer) as fullscreen `/pos` outside `AdminLayout`, perm `Pos_view`. 3-way diff result: stocky's Vue 3 POS already carries multi-currency + promotions, so the SaaS-only delta was FBR receipt QR, tenant print font, tenant upload paths (`$imgUrl`), promotion context → payment modal — applied by `scratchpad/patch-pos.cjs` with asserted anchors. Stocky-only extras kept (previous dues, phone-duplicate check, cashier + print in Today Sales, per-item tax rows). `/app/pos` is **not** mapped yet — flip after the parity pass (see Phase 9). `old_pos.vue` dropped. |
| 10 — Secondary bundles | **done (2026-08-30), unbuilt** | Three more Vite configs in `vue3-app/` (`npm run build` runs all four): **portal** — stocky's Vue 3 portal taken verbatim (this repo's legacy portal is byte-identical to stocky's legacy one); its `portal/translations` + `portal/locale` endpoints do not exist here, so the 4 `lang/*/portal.php` files are bundled (`resources/scripts/gen-portal-messages.cjs` → `src/portal/messages.js`, 287 keys × en/fr/es/ar) and the choice lives in localStorage; pages whose API this backend never had (quotations, appointments, contracts, help/KB, delete-account — the legacy portal already 404ed on them) are gated off in `src/portal/modules.js`. **Customer display** — stocky's Vue 3 build verbatim (legacy identical). **Storefront** — Alpine source untouched except the CSS import; Tailwind build moved from Mix to `vite.storefront.config.js` (runs from the project root so the Tailwind `content` globs resolve). Blade loaders (`portal`, `customer_display`, `layouts/store`) point at `public/js-next/{portal,customer-display,storefront}/`; `webpack.mix.js` now builds only the legacy admin; dead `login.js` deleted. `check-contracts` reads `routes/portal.php` too; `check-keys` validates `src/portal` against the bundled messages. |
| 11 — Cutover | **done (2026-08-30), unbuilt** | Non-destructive: the legacy Vue 2 source, `package.json`/lock, `webpack.mix.js`, the old `master`/`next` layouts and the migration-only scripts are **parked in `old_resources/`** (owner's choice, nothing deleted); `vue3-app/` folded into `resources/src` + project root (four `vite.*.config.js`, `scripts/`, Vue 3 `package.json`). `master.blade.php` is the Vue 3 loader for every admin path; router base `/` with in-app redirects for legacy `/app/…` (via `migratedRoutes.json`, `/app/pos → /pos` added) and `/next/…`; bundles build into `public/js/` (+ `public/css/storefront.css`), `swWarm` follows. Legacy `public/js/*.min.js` left in place — `npm run build` cleans `public/js` first. |

Gotcha: Git Bash rewrites arguments that look like POSIX paths
(`--prefix /app/settings` → `C:/Program Files/Git/app/settings`). Prefix
`MSYS_NO_PATHCONV=1` to node commands that take legacy paths.

Patch-script gotcha (bit me twice): with `String.replace(re, () => text)` the
replacement is literal — `$1` is NOT expanded. Use a string replacement (or
build the text from the match) when the pattern captures.

Phase 4 toolkit: `scripts/plan-phase.cjs --all` (pairs legacy paths with
stocky pages), `scripts/port-routes.cjs --prefix … --map extra.json` (copies
pages + import closure, splices routes, extends the migrated map). Legacy
`/edit/:id`-style paths are mapped by hand (the scratch `phase4-map.json` is
now folded into `migratedRoutes.json`).

Phase 3 additions to the toolkit: `node resources/scripts/check-contracts.cjs <dir>`
(endpoint + payload-key check, read-only against the backend),
`node resources/scripts/check-keys.cjs | node resources/scripts/pull-keys.cjs --from-check`
(bulk key pull). `config/migrated.js` is now the shared lookup for BOTH apps
(pattern keys like `/app/reports/detail_customer/:id`, query strings preserved).

Porting recipe used in Phase 2 (repeatable): copy the stocky page → diff
legacy `v-model` fields vs. new `v-model:value` fields → check every endpoint
against `routes/tenant_api.php` and the controller's payload keys against the
page's `rowsKey` → replace `/images/` with `uploadUrl()` → route + JSON entry →
`npm run check-keys` (pull missing keys from stocky's seeders into
`i18n/fallback.js`) → SFC compile check.

### Working with the new app

```
npm install                 # project root (single node_modules since the cutover)
npm run build               # all four bundles → public/js/ (admin has .vite/manifest.json)
npm run dev                 # build --watch
npm run check-keys          # every $t() key exists in the seeder or a bundled fallback
node resources/scripts/gen-icons.cjs  # validate/regenerate resources/src/config/menuIcons.js against lucide 1.x
node resources/scripts/pull-keys.cjs KEY [KEY…]   # copy keys (en/fr/es/ar) from stocky's seeders into resources/src/i18n/fallback.js
node resources/scripts/check-contracts.cjs [files]   # endpoints vs routes/tenant_api.php, rowsKey vs controller payloads
node resources/scripts/gen-portal-messages.cjs   # regenerate resources/src/portal/messages.js from stocky's lang/*/portal.php
npm run build:portal | build:customer-display | build:storefront   # the secondary bundles (build = all four)
```

The legacy bundle must be rebuilt too whenever `resources/src/router.js` or
`vue3-app/src/config/migratedRoutes.json` changes (`npm run production` at the
root): that JSON is imported by the legacy router, which redirects every
migrated `/app/...` path to `/next/...`.

Layout of `vue3-app/src/` mirrors the stocky reference (`pages/`, `components/`,
`composables/`, `lib/`, `stores/`, `config/`, `i18n/`, `router/`, `layouts/`).
SaaS-only pieces, none of which exist in stocky:

- `stores/plan.js` — `window.__planSummary`; sidebars hide entries whose
  `feature` (from the legacy `planFeature()` gates) is off.
- `lib/http.js` — `403 {status:'limit_reached'}` → `lib/limitReached.js` →
  `components/LimitReachedModal.vue`; 401/419/409-revoked → hard logout;
  `X-Pdf-Locale` header.
- `lib/uploadUrl.js` — `window.__uploadPath` (tenant folder) for every image.
- `lib/nav.js` — `goTo('/app/…')` routes a legacy path to the Vue 3 page when
  migrated, else into the legacy SPA. Use it for cross-module links until cutover.
- `i18n/fallback.js` — bundled strings for keys the seeder lacks (DB wins).
- `stores/pendingWork.js` — falls back to the `notifs` quantity-alert count
  (no `/pending-work` endpoint here). No `today_summary` either: the topbar's
  Today's Summary drawer was dropped.
- `resources/views/layouts/next.blade.php` — the Vue 3 loader, included by
  `master.blade.php` for `/next/*`; carries the SaaS partials and globals.

---

## 0. Inventory (as of 2026-08-29)

| | Legacy (this repo) | Stocky Vue 3 (reference) |
|---|---|---|
| Stack | Vue 2.7, Bootstrap-Vue, vue-good-table, Vuex 3, vue-router 3 (history), vue-i18n 8, vee-validate 3, Laravel Mix/webpack | Vue 3.5, Ant Design Vue 4, Pinia, vue-router 4, vue-i18n 11, Vite 6 |
| Admin pages | 421 `.vue` under `resources/src/views/app`, 322 router paths | 358 pages, 287 migrated legacy paths |
| Router file | `resources/src/router.js` (3,291 lines) | `router/index.js` (855 lines, all lazy) |
| Entry bundles | `main.js`, `portal.js`, `customer-display.js`, `storefront.js` (Alpine), `login.js` (**loaded by no Blade view — dead**) | admin, portal, customer-display, storefront — one Vite config each |
| Biggest files | `pos.vue` 17,860 · `old_pos.vue` 10,720 · `system_settings.vue` 7,333 · `Create/Edit_permission.vue` 4,650 · `Add/Edit_product.vue` 4,600 · `index_sale.vue` 3,643 · `Sidebar.vue` 3,031 · `dashboard.vue` 2,869 · `AI_Reports.vue` 2,536 | POS ported 1:1 via `pos-compat/` shims |

### Legacy library usage (files) → Vue 3 replacement

| Legacy | Files | Replacement (exists in stocky unless marked NEW) |
|---|---|---|
| `<b-button>` / `<b-card>` / `<b-form-*>` / `<b-modal>` / `<b-badge>` / `<b-alert>` / `<b-tabs>` / `<b-table>` | 311 / 287 / 270 / 124 / 108 / 61 / 32 / 32 | `a-button` / `a-card` / `a-form` + rules / `a-modal` / `a-tag` + `StatusTag.vue` / `a-alert` / `a-tabs` / `a-table` |
| `vue-good-table` | 188 | `components/DataTable.vue` + `composables/useCrudTable.js` (same `page,SortField,SortType,search,limit` contract) |
| `<v-select>` | 185 | `a-select` (`show-search`, `filter-option`) |
| `<breadcumb>` | 318 | `components/PageHeader.vue` |
| `<lucide-icon>` | 355 | `@ant-design/icons-vue` for new markup; `pos-compat/LucideIcon.vue` kept for the 1:1 POS |
| `metaInfo` (vue-meta) | 305 | document title set by the router guard from `config/menu.js` |
| `nprogress` | 261 | `lib/progress.js` |
| `$store` / `map*` (Vuex) | 102 / 153 | Pinia: `stores/auth.js`, `ui.js`, `menu.js`, `datatable.js`, **NEW `plan.js`** |
| `$swal` | 101 | `Modal.confirm` + `message` (wrapped in `useCrudTable`) |
| `vue2-daterange-picker` / `vuejs-datepicker` / `moment` | 35 / 7 / 43 | `components/DateRangePicker.vue` / `a-date-picker` / `dayjs` |
| `vue-apexcharts` / `vue-echarts` | 31 / 2 | `vue3-apexcharts` (async) / `echarts` direct |
| `<vue-excel-xlsx>` (ExcelExport) | 47 | `lib/exporters.js` (xlsx + jspdf) |
| `Vue.filter` | 46 | `composables/useFormat.js` |
| `$root.$emit` / `Fire` bus | 67 | local emitter; POS uses `pos-compat` emitter |
| `vue-html-to-paper` / `vue-easy-print` | 6 / 3 | `lib/posReceiptHtml.js` + `window.print` |
| `vue-barcode` | 6 | `components/BarcodeSvg.vue` (jsbarcode) |
| `vuedraggable` 2 / `vue-tags-input` / `clock-picker` / `vue-perfect-scrollbar` | 3 / 3 / 2 / 7 | `vuedraggable@4` / `a-select mode="tags"` / `a-time-picker` / native overflow |
| `qrcode-scanner` (global component) | 19 | `pos-compat/QrcodeScanner.js` |
| `vee-validate` 3 | rules in forms | `a-form` `:rules` (computed, for language switches) |

### Module coverage — what can be ported from stocky vs. built fresh

"Port" = copy the stocky Vue 3 page, then diff it against the legacy SaaS page
for divergence (extra fields, plan gating, tenant upload paths, endpoint
differences). "Fresh" = no stocky counterpart.

| Module (legacy pages) | Source | Notes |
|---|---|---|
| reports (66) | Port (stocky: 61) | verify each contract against this repo's `ReportsController`s; `ai_reports` + `sales_3d_dashboard` decided below |
| settings (52) | Port (41) + fresh | fresh: whatsapp (6), fbr, zatca, module/update settings, quickbooks — check stocky first, several exist |
| people (16) + user_management (1) | Port (13) | customers/suppliers/users, ledger, ecommerce lists, imports |
| hrm (16) | Port (12) | |
| hospital (16), school (13), fleet (8), recruit (8), realestate (5), service (8), projects (8), marketing (9), meeting (6), commissions (6), assets (6), bookings (6), contracts (5), tasks (5), knowledge_base (3), kitchen (1) | Port | stocky has all of these |
| mrp (13) | Port (stocky `manufacturing` 12) | naming differs; same endpoints — diff carefully |
| accounting_v2 (8) + accounts (3) | Port (stocky `accounting/v2` 9) | |
| store (13) | Port (stocky 23 — superset) | drop stocky-only pages (shopify etc.) unless the SaaS API has them |
| products (15), serial_numbers (3) | Port (9 + 2) | Add/Edit_product 4.6k lines — variants/combos were still legacy-linked in stocky; must be finished here (no legacy after cutover) |
| sales (8), purchases (6), quotations (5), sale_return (5), purchase_return (5), adjustment (5), transfers (5), documents (4), expenses (5), deposits (5), damage (4), promotions (3) | Port | multi-line forms share `lib/lineCalc.js`, `BatchAllocator`, `SerialPicker` |
| permissions (Create/Edit 4.6k each) | Port (stocky 2) | |
| dashboard (2,869) | Port + diff | SaaS plan widgets |
| **billing (8)** | **Fresh** | current plan, change plan, checkout, success/failed, history, invoices — Stripe/Flutterwave (v3 only) |
| **support (4)** | **Fresh** | tickets list/create/detail; `plugins/support.i18n.js` bundled strings |
| **subscription_product (4)** | **Fresh** | |
| **whatsapp (6)** | **Fresh** (check stocky marketing/whatsapp templates) | |
| profile, two_factor_verify, real_time_sales_counter, customer-display setup, NotAuthorize, notFound | Port / trivial | |
| **pos.vue (17,860)** | Port stocky `pages/pos/PosPage.vue` + `pos-compat/`, then 3-way diff | see Phase 9 |
| old_pos.vue (10,720) | **Drop** (decision needed) | not routed as the main POS |

---

## 1. Decisions to confirm before Phase 1

1. **Strangler-fig at `/next/*`, no route change.** `layouts/master.blade.php`
   branches: `request()->is('next', 'next/*')` → `@include('layouts.next')`
   (the Vue 3 loader, ported from stocky's `next.blade.php`), else the legacy
   markup. At cutover the branch is removed and the Vue 3 loader is all that
   remains; router base switches from `/next` to `/`.
2. **Separate package during migration: `vue3-app/`** at repo root with its own
   `package.json` + `vite.config.js` (Vue 2 and Vue 3 cannot share one
   `node_modules`; stocky did the same and folded it into `resources/src` at
   the end). Vite `outDir: public/js-next/` (base `/js-next/`) — Laravel Mix's
   `CleanWebpackPlugin` wipes `public/js/*` on every legacy build, so the two
   outputs must not share a folder.
3. **Page rewrite, not codemod.** Pages are re-authored on Ant Design (as in
   stocky); no `@vue/compat`, no Bootstrap-Vue port. The only 1:1 copy is the
   POS (below).
4. **POS stays visually identical** (stocky rule: 1:1 port, no Ant restyle).
5. **Drop `old_pos.vue`** and the dead `login.js` entry.
6. **`ai_reports` and `sales_3d_dashboard`**: stocky left them in legacy. Here
   there is no legacy after cutover — port them last (Phase 8) or drop; owner
   decides.
7. Storefront (`storefront.js`, Alpine + Tailwind) is **out of scope** — it is
   not Vue; it only moves from Mix to a Vite config at cutover.

---

## 2. Phases

Each phase ends buildable and shippable; the sidebar of the new app is the one
navigation surface (`config/menu.js` `MIGRATED_ROUTES` sends un-migrated entries
back to the legacy path, exactly as stocky did). The legacy `Sidebar.vue` /
`VerticalSidebar.vue` get the mirror: entries present in `MIGRATED_ROUTES`
become plain `<a href="/next/...">` links, so both apps agree on who owns a page.

### Phase 1 — Foundation (port from stocky, adapt for SaaS)

Copy and adapt: `main.js`, `App.vue` (ConfigProvider theme/RTL/locale, session
keepalive), `layouts/AdminLayout.vue`, `components/{AppTopbar,SidebarMenu,
SidebarFlat,SidebarLarge,Customizer,DataTable,PageHeader,StatusTag,
DateRangePicker,RichTextEditor,TranslatableInput,ImportDropzone}.vue`,
`composables/{useCrudTable,useFormat}.js`, `lib/{http,progress,exporters,
priceFormat,upload,statusTags,statusColors,menuOrder}.js`, `stores/{auth,ui,
menu,datatable}.js`, `i18n/index.js`, `config/{permissions,modules,menu,
menuIcons,menuItemIcons,bootstrapIcons}.js`, `assets/theme.css`, `router/index.js`
skeleton (`lazy()` wrapper, auth guard, permission meta, title from menu,
`router.onError` chunk reload), `pages/{NotFound,Ping,Dashboard}.vue`.

SaaS-specific work in this phase (none of it exists in stocky):

- `config/menu.js` regenerated from **this repo's** `Sidebar.vue` (labels,
  permissions, plan-feature gates) — not copied from stocky.
- **`stores/plan.js`** (NEW): reads `window.__planSummary`
  (`has_plan`, `features`, limits) — replaces `Sidebar.vue:2940`; sidebar and
  pages gate on it.
- **`lib/http.js`**: on `403` with `data.status === 'limit_reached'` open the
  upgrade modal instead of redirecting (legacy `main.js:284`). Keep the locale
  header the legacy interceptor sends for Blade PDFs.
- **`lib/uploadUrl.js`** (NEW): `uploadUrl('brands', file)` built from
  `window.__uploadPath` (legacy `$uploadPath`). Stocky pages hard-code
  `/images/<folder>/…` — every ported page must be rewritten to use this.
- `layouts/next.blade.php`: port of stocky's `next.blade.php` **plus** the SaaS
  partials `tenant-font`, `plan-upgrade-modal`, `subscription-reminder-banner`,
  `impersonation-banner`, the `window.__planSummary / __uploadPath / __appName /
  __pageTitleSuffix` script block, and the PWA manifest/meta from
  `master.blade.php`. Manifest-driven asset tags — **no `?v=` query**.
- Limit-reached modal and offline-sync overlay from legacy `App.vue` re-created
  as Ant components.

Exit: `/next/dashboard` renders behind auth with translations, permissions,
plan gating, dark/RTL, and a sidebar whose un-migrated entries jump to legacy.

### Phase 2 — Wave A: simple CRUD (~25 pages, port)

Brands, Categories, SubCategories, Units, Currencies, Warehouses (+ rack
locations tab), expense/deposit/asset/recruit/realestate/checklist categories,
departments, designations, office shifts, holidays, leave types, technicians,
KB groups, payment methods, accounts.

Purpose: prove `DataTable` + `useCrudTable` + modal forms + `uploadUrl()`
against the tenant API before scaling. Establish the page template.

### Phase 3 — Wave B: reports (66 pages, port)

Port stocky's 61 report pages + `ReportPage/ReportTab/ReportChart`; re-verify
every endpoint contract against this repo's controllers (the stocky cheatsheet
is the starting point, not the truth). Export path (xlsx/pdf) verified once,
early. `warehouse_report` as the tabbed page. `ai_reports` /
`sales_3d_dashboard` deferred to Phase 8.

### Phase 4 — Wave C: people, HRM and vertical modules (~170 pages, port + diff)

People (16), HRM (16), hospital (16), school (13), MRP (13), marketing (9),
service (8), projects (8), recruit (8), fleet (8), accounting_v2 + accounts
(11), commissions (6), meeting (6), assets (6), bookings (6), contracts (5),
tasks (5), realestate (5), KB (3), kitchen (1), store (13), permissions (2),
expenses/deposits/damages/promotions (17). Largest single item: the
permission editor (2 × 4.6k lines).

### Phase 5 — Wave D: documents (~45 pages, port + diff)

Sales, purchases, quotations, sale/purchase returns, adjustments, transfers:
list + details + create/edit; payments (4 lists); shipment; documents;
import_sales / import_purchases; `lib/lineCalc.js`, `BatchAllocator`,
`SerialPicker`, `PayDueModal`, `ReturnPayments*`. Stocky still deferred packs
selector, points UI and multi-payment lines — those must land here.

### Phase 6 — Wave E: products (18 pages, port + finish)

List, details, count stock, batches, serial numbers, barcode, imports,
opening-stock import, **Add/Edit product including variants and combos**
(stocky sent those to legacy — no legacy here).

### Phase 7 — Wave F: settings (52 pages, port + fresh)

`system_settings` (7.3k lines → tabbed Ant page), POS settings, receipt
designer, payment gateways (tenant-level; Flutterwave v3 rule), custom fields,
translations editor, module / update settings, mail / SMS / email templates,
webhooks + logs, login devices, system health, backup, appearance, ZATCA, FBR,
QuickBooks, WooCommerce, Shopify, **WhatsApp (6, fresh)**.

### Phase 8 — Wave S: SaaS-only pages (~25 pages, fresh)

Billing (8), support tickets (4), subscription products (4), profile, 2FA
verify, real-time sales counter, customer-display setup, dashboard diff
(plan widgets), then `ai_reports` / 3D dashboard per decision 6.

### Phase 9 — POS (1:1 port) — done 2026-08-30

**What landed**

- `src/pages/pos/PosPage.vue` — stocky's Vue 3 POS, copied verbatim, plus
  `src/pos-compat/` (308 files, 1.6 MB of vendored legacy SCSS:
  `bootstrap-legacy`, `globals-legacy`, `animate-legacy` — `_globals.scss`
  imports animate, so all three stay). Route: top-level `/pos` (outside
  `AdminLayout`, the page owns its 100vh layout), `permission: 'Pos_view'`
  (exists in `PermissionsSeeder` / `SalePolicy`; the legacy route had no
  route-level check, the page and the API enforce it).
- Deps added: `vue-select` (POS customer/product pickers), `jsbarcode`
  (already needed since Phase 5 by `components/BarcodeSvg.vue` — 9 pages —
  but never declared; fixed here). `qz-tray` is NOT needed: the cash drawer
  loads QZ from the jsDelivr CDN at runtime (`pos-compat/cashDrawerQz.js`);
  the unused `lib/qzPrint.js` was removed.
- `lib/` synced with stocky (`receiptFont`, `posReceiptHtml`, `labelRaster`,
  …) — the closure copier only followed single-quoted imports; POS uses
  double quotes. `scripts/port-routes.cjs` now follows both.

**3-way diff — what the SaaS POS has that stocky's does not**

stocky legacy `pos.vue` (f8e372a, 17,575 lines) → this repo's `pos.vue`
(17,860) is 90 hunks, but stocky's *Vue 3* POS already re-implements
multi-currency and promotions, so after excluding those the SaaS-only delta
is five things, applied by `scratchpad/patch-pos.cjs` (every replacement
asserts its match count, 5 receipt layouts included):

| SaaS behaviour | Where in the port |
|---|---|
| FBR Digital Invoicing (Pakistan): `fbr_invoice_number` from `sale_print_html` / offline receipt, QR block after ZATCA in all 5 receipt layouts, `renderFbrQrPos()`, watcher, `awaitQrReady()` waits for it | `PosPage.vue` |
| Tenant dashboard font inside print popups/iframes (`window.__tenantFont` / `__tenantFontStack` from `partials/tenant-font`, which `next.blade.php` includes) | `lib/receiptFont.js` → `tenantFontHeadTags()`, emitted before stocky's per-receipt font so an explicit receipt font still wins; iframe print also waits on `fonts.ready` |
| Tenant upload folder for logos/product images (`$imgUrl('settings' \| 'products', file)`) | `posCompatMixin` → `this.$imgUrl = uploadUrl`; zero `'/images/'` literals remain |
| Promotion context forwarded through the modern payment modal so `pos/create_pos` records `PromotionUsage` | `promotionContextForCheckout` computed + `:promotion-context` prop; `pos-compat/ModernPaymentModal.vue` spreads it into the payload |
| Customer-display broadcast must never redirect on failure | already `.catch()`-ed in stocky's port; `lib/http.js` has no 404 redirect anyway |

Kept from stocky (newer than this repo's legacy POS; harmless on this API,
`check-contracts` resolves every endpoint): previous-dues / net-balance
rows, debounced phone-duplicate check on quick-add customer, cashier name +
"print today's sales", per-item tax rows (`show_items_tax`). `PosReturnModal`
differed only by an import path.

**Not mapped yet — the parity gate**

`/app/pos` is deliberately absent from `migratedRoutes.json`, so the legacy
sidebar still opens the Vue 2 POS while `/next/pos` is reachable directly
for a side-by-side pass. To flip, add `"/app/pos": "/pos"` to
`vue3-app/src/config/migratedRoutes.json` and rebuild both bundles (the
legacy guard reads the same file). `/app/pos/not_authorize` already maps to
`/not-authorized`.

**Parity checklist for the browser pass** (cannot be done statically):
customer/product search + barcode scan, held orders, multi-payment modal,
promotions auto-apply, multi-currency switch, receipt print (5 layouts, FBR
+ ZATCA QR, tenant font), offline draft + sync, cash register open/close,
keyboard shortcuts, customer display broadcast, returns modal.

### Phase 10 — Secondary bundles — done 2026-08-30

Each bundle has its own Vite config in `vue3-app/` and its own npm script;
`npm run build` runs all four. Outputs stay under `public/js-next/` until
cutover (Mix cleans `public/js/*` on every legacy build):

| Bundle | Source | Config / script | Output → loaded by |
|---|---|---|---|
| Admin | `src/main.js` | `vite.config.js` / `build:admin` | `public/js-next/` (manifest) → `layouts/next.blade.php` |
| Client portal | `src/portal/main.js` | `vite.portal.config.js` / `build:portal` | `public/js-next/portal/app.{js,css}` → `portal.blade.php` |
| Customer display | `src/customer-display/main.js` | `vite.customer-display.config.js` / `build:customer-display` | `public/js-next/customer-display/app.{js,css}` → `customer_display.blade.php` |
| Storefront (Alpine) | `resources/src/storefront.js` (legacy tree, unchanged) | `vite.storefront.config.js` / `build:storefront` (runs from the project root) | `public/js-next/storefront/storefront.{min.js,css}` → `layouts/store.blade.php` |

Fixed file names + `?v=filemtime` are safe for the three small bundles because
they have no lazy chunks (`inlineDynamicImports`) — the admin entry is the only
one that must stay query-free.

**Portal.** This repo's legacy portal (22 files) is byte-identical to stocky's
legacy portal, so stocky's Vue 3 port was taken as-is. Two SaaS adaptations:

- *i18n without a backend.* stocky fetches `resources/lang/{locale}/portal.php`
  through `GET /api/portal/translations/{locale}` and persists the choice with
  `POST /api/portal/locale`; neither the endpoints nor the language files exist
  here. The four PHP files are parsed into `src/portal/messages.js` by
  `resources/scripts/gen-portal-messages.cjs` (287 keys, en/fr/es/ar), `src/portal/i18n.js`
  reads them from the bundle, the choice is stored in localStorage
  (`portal_language`), and `portal.blade.php` injects `window.__PORTAL_LOCALE__`
  as the starting language. The language switcher works unchanged.
- *Modules this backend never had.* `routes/portal.php` declares only auth,
  dashboard, invoices (+PDF), payments, statement and profile/password. The
  legacy portal nonetheless routed quotations, appointments, contracts, the
  help centre and an account-deletion card — all of which 404ed. They are
  switched off in `src/portal/modules.js` (nav entries hidden, routes redirect
  to the dashboard, danger card hidden); flipping a flag re-enables a module
  the day its API exists. The pages themselves are kept verbatim.

**Customer display.** Legacy `CustomerDisplay.vue` is identical to stocky's,
so stocky's Vue 3 `customer-display/` (Echo + polling fallback on
`pos/customer-display/last-cart`, translations from `translations/{locale}`)
is used verbatim. The Blade view already injects a tenant-aware
`window.__APP_LOGO__`.

**Storefront.** Alpine + Tailwind, not Vue — deliberately untouched. The only
source change is the `import '../css/storefront.css'` line stocky also added,
so Vite emits the Tailwind CSS next to the IIFE bundle. The root
`tailwind.config.js` is reused; its `content` globs resolve from the cwd, hence
the `cd ..` in the npm script.

**Legacy build.** `webpack.mix.js` now compiles only `resources/src/main.js`
(the Vue 2 admin, still needed until Phase 11); the `login.js` entry was dead
(no view loaded it) and is deleted. `resources/src/portal.js`,
`customer-display.js`, `resources/src/portal/` and
`views/app/pages/customer/CustomerDisplay.vue` are now unreferenced and go
with the rest of the legacy tree at cutover.

**Toolkit.** `check-contracts.cjs` also parses `routes/portal.php` (its
`api/portal` prefix normalised to the `/api` base the SPAs use);
`check-keys.cjs` validates `src/portal` against the bundled messages instead
of the seeder.

### Phase 11 — Cutover — done 2026-08-30 (non-destructive)

The owner chose to **keep** the legacy tree rather than delete it. Layout now:

| Was | Now |
|---|---|
| `resources/src/` (Vue 2 admin, portal, customer display) | `old_resources/src/` — untouched, not built, not loaded by any view |
| `package.json`, `package-lock.json`, `webpack.mix.js` (Mix) | `old_resources/` |
| `resources/views/layouts/master.blade.php` (Vue 2) + `next.blade.php` | `old_resources/views/layouts/` |
| `vue3-app/scripts/{gen-menu,plan-phase,port-routes}.cjs` (migration-only) | `old_resources/scripts/` |
| `vue3-app/src/` | `resources/src/` (+ `storefront.js`, the live Alpine entry, copied back) |
| `vue3-app/vite.*.config.js`, `vue3-app/package.json`, `vue3-app/scripts/` | project root: `vite.config.js`, `vite.portal.config.js`, `vite.customer-display.config.js`, `vite.storefront.config.js`, `package.json`, `scripts/` |

What changed in the app:

- **`master.blade.php` is the Vue 3 loader** for every admin path (the
  `request()->is('next/*')` branch is gone). Manifest at
  `public/js/.vite/manifest.json`, base `/js/`, still no `?v=` on the entry.
- **Router base `/`** plus two redirect routes so nothing external breaks:
  `/app/:path*` → `resolveMigratedPath()` (every legacy path, query kept —
  bookmarks, e-mails, gateway callbacks like `/app/billing/success?payment_id=`)
  or the not-found page; `/next/:path*` → same path without the prefix.
  `lib/nav.js` no longer falls back to `window.location`. `/app/pos → /pos`
  is now mapped (there is no Vue 2 POS to fall back to).
- **Outputs**: admin `public/js/` (hashed, manifest), portal
  `public/js/portal/`, customer display `public/js/customer-display/`,
  storefront `public/js/storefront.min.js` + `public/css/storefront.css` —
  the same paths the legacy Blade views always used. `npm run build` runs
  `clean:js` first (like stocky), so the parked legacy bundles in
  `public/js/*.min.js` disappear on the first build. `swWarm` reads
  `/js/.vite/manifest.json`.
- **Toolkit** at `scripts/`: `check-keys`, `check-contracts`, `pull-keys`,
  `gen-portal-messages`, `gen-icons`. `config/menu.js` / `menuIcons.js` are
  now hand-maintained (their generator read the parked legacy sidebar).

Not done here (owner's call): `version.txt` bump / changelog entry, and
deleting `old_resources/` once the Vue 3 build has been verified in a browser
(`git rm -r old_resources`). The git history also still has the pre-migration
tree at `462a111`.

**First build after cutover** (root, one node_modules now):

```
npm install
npm run build        # clean public/js, then admin + customer display + portal + storefront
```

### Post-cutover fixes — 2026-09-12 (subscription / billing verification)

Found by a headless-browser sweep of all 264 sidebar paths plus a rolled-back
transaction walking trial / expiring / expired / cancelled / limit / feature
states through the HTTP kernel:

- **SPA billing pages moved from `/billing/*` to `/subscription/*`.** The
  tenant web catch-all (`routes/tenant_web.php`) excludes the `billing` prefix
  because `/billing/*` is the Blade billing area; a full page load of
  `/billing/current-plan` was a Laravel 404 and `/billing/checkout|history|
  success|failed` fell through to the Blade pages. `migratedRoutes.json` keeps
  the legacy `/app/billing/*` keys (menu, LimitReachedModal, gateway return
  URLs) pointing at the new paths; an in-app `billing/*` → `subscription/*`
  redirect covers stale pushes.
- **`lib/http.js` now handles all three SaaS 403s**: `limit_reached` (modal,
  as before), `feature_unavailable` (same modal titled "Upgrade Required" —
  legacy sent GET loads to its 403 page, the Blade partial shows an upgrade
  modal), `subscription_required` (full navigation to the Blade
  `/billing/plans`, the only area reachable once the subscription lapses).
- `billing_view` added to `config/permissions.js` (role editor could not grant
  it); billing routes carry `meta.ownerBypass` so user #1 passes the guard
  like `BillingApiController::authorizeBilling` already allows.
- Full route sweep (all 377 router entries, real ids where the dev tenant has
  rows): `pages/products/ProductDetails.vue` had a literal `$1` left by a
  patch script (page crashed with "$1 is not defined") — removed;
  `/customer-display/setup` moved to `/settings/customer-display` (same
  catch-all exclusion as billing — `customer-display` is the Blade public
  screen route); `PermissionForm.vue` refuses role #1 up front because
  `roles/1/edit` answers HTTP 401 and `lib/http.js` treats every 401 as an
  expired session (logout). Backend: `get_version_info` route points at a
  method `UpdateController` no longer has (update page shows "v—").
- Backend findings (reported, NOT changed — backend is out of scope):
  `EnsureActiveSubscription` ignores `cancelled` rows, so a cancelled-but-paid
  subscription is locked out immediately although the cancel flow promises
  access until `ends_at`; report queries reference a missing `sales.seller_id`
  column / `seller` relation; payment lists crash on a payment whose method was
  deleted; the reminder banner is off unless the super-admin enables the
  "banner" channel (default is email only).

---

## 3. Rules carried over from stocky (each learned the hard way)

0. Every ad-hoc call to a list endpoint sends `page, SortField, SortType,
   search, limit` — controllers feed `SortField` into `orderBy()` unchecked.
1. **No `?v=` query** on the module script; assets are content-hashed.
2. **No `manualChunks`.**
3. **All HTTP through `lib/http.js`** (Passport cookie guard needs
   `X-XSRF-TOKEN` on GETs too).
4. Titles come from `config/menu.js`, not filenames or permissions.
5. Declare rows explicitly (`rowsKey` / `select`).
6. Never hide a panel when data is empty — show an empty state.
7. `columns` / `rules` are `computed()` so they re-render on language change.
8. `a-menu` items: `icon` is a function, `label` a plain string.

SaaS additions:

9. **Never hard-code `/images/…`** — always `uploadUrl()`.
10. Plan gating lives in `stores/plan.js` + `menu.js` `feature` keys; the 403
    `limit_reached` handler is global — pages never special-case it.
11. `url()` not `asset()` in Blade for tenant assets (multi-domain).
12. Do not run `npm run build`; the owner builds.

## 4. Per-phase verification

- `npm run check-keys` (`vue3-app/resources/scripts/check-keys.cjs`) on every new page:
  every `$t()` key exists in `database/seeders/translations/en.php` or in
  `src/i18n/fallback.js` (add it there when the seeder lacks it — no backend).
- Browser pass through the `verify` skill (tenant domain, Passport cookie
  login) — list, sort, search, paginate, create, edit, delete, export.
- Side-by-side with the legacy page for data parity before the sidebar flips
  the entry to `/next/...`.
