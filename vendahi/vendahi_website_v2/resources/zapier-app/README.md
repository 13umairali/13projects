# Stocky SaaS — Zapier App

The Zapier Platform app definition for the Stocky SaaS Zapier integration.
The server side lives in the main application (`/api/zapier/*`, Settings → Zapier).

## What it provides

**Triggers (instant, REST hooks)** — New Sale, New Quotation, New Customer,
New Product, New Expense, New Payment Received, Low Stock Alert.

**Actions** — Create Customer, Create Product.
**Searches** — Find Customer, Find Product.

**Authentication** — the user enters their workspace URL
(`https://<tenant>.your-domain.com`) and the API key generated in
Settings → Zapier inside their workspace.

## Publishing (run under your Zapier developer account)

```bash
npm install -g zapier-platform-cli
zapier login
cd resources/zapier-app
npm install
zapier validate           # sanity-check the definition
zapier register "Stocky"  # first time only — creates the app on Zapier
zapier push               # upload this version
```

Then invite users from the Zapier developer dashboard, or submit for public
listing. Test by building a Zap: pick "Stocky" → a trigger → connect an
account (workspace URL + API key) → Zapier calls `/api/zapier/me`, then
subscribes hooks automatically.
