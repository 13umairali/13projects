# Version 1.0 - 15-04-2026


# Version 1.1  - 23-04-2026

+ Added Custom Domain feature allowing tenants to connect and use their own domains with full DNS verification, SSL support

+ Added secure Central Database Backup tab in Super Settings for creating, listing, downloading, and deleting SQL backups with protected storage and secure access controls.

+ Added Logs & System Health section for centralized monitoring of system errors and tenant infrastructure status.

+ Added Default Language setting for visitors in General Settings, allowing administrators to define the default display language shown to guests before login.

+ Added Pending Payments page for tracking, reviewing, and managing all unpaid or awaiting-confirmation transactions in one centralized view.

+ Fixed various minor issues and improved overall system stability and performance.

🧰 Bug Fixes & Enhancements

+ Updated documentation for better clarity and setup guidance.

+ Resolved multiple minor bugs to improve performance and reliability.


# Version 1.2  - 30-06-2026


## 🚀 Super Admin

### ✨ New Features

* Added **Default Branding** for newly created tenants.
* Added **Reserved Subdomains** management.
* Added **Automatic Subscription & Trial Expiry Reminders** for tenants.
* Added **Demo Tenant Generator**.
* Added a **Support System** between tenants and the Super Admin.
* Added **Mollie Payment Gateway** integration.
* Added an option to **Enable/Disable Languages** from the dashboard for the language selector.
* Added support for **Multilingual Hero Images**.
* Added support for **Custom Fonts** on frontend layouts.
* Added a dedicated **Terms & Conditions** page.


## 🚀 Tenant

### ✨ New Features

* Enhanced the application to be pharmacy-ready by adding batch management and expiry date tracking for products.

* Added Progressive Web App (PWA)

* Added the ability to manage Email and SMS templates per language.

* Added Cash Drawer Auto-Open Integration using QZ Tray.

* Add a new feature called "Direct Network Printing (ESC/POS)" to the POS system.

* Add a new feature in SMS Settings called "Custom SMS Gateway" that allows users to integrate any SMS provider using their own API details

* Improved POS performance for faster loading and smoother transactions

* Added System Health Dashboard

* Added drag & drop widget ordering on Dashboard .

* Added dashboard customization options for font size and font family.

* Added Real-time Sales Counter page with live auto-refresh

* Added Internal Location (Rack/Shelf/Location) feature and reporting support.

* Added a new toggle option in Online Store Settings to show or hide product prices, allowing prices to be visible only for logged-in users in the online store.

* Added Client Portal: clients can login to view invoices, payments, download PDFs

* Assets: added verification dates, due-assets list/page, daily notifications (email + in-app), configurable templates, and cron setup.

* Sales Commission: added commission programs, agents/brokers management, automatic invoice-linked calculations, receipts, and detailed reports.

* Added Knowledge Base module

* Added multi-image support for products with gallery and main image selection

* Added Stripe payment method integration ( Online Store)

* Added Cash on Delivery (COD) payment option in the online store

* Added multi-category and multi-subcategory support for products

* Implemented private account registration with referral code option and admin approval before account activation

* Added ability to change the theme color by selecting a color from the customization panel.

* Added Keyboard Shortcuts to POS

* Added new feature called Overselling Control .

* Added Auto Journal Entries (Accounting Integration).

* Implement a complete Webhooks system

* Added Opening Balance for Suppliers

* Sync Booking Calendar with Google Calendar

* Added 3D Sales Dashboard (Visual Charts)

* 3 Decimal Pricing Support — Enable product pricing with up to 3 decimal places for greater accuracy.

* Kitchen Order Management — Send and manage orders directly from POS to the kitchen workflow.

* POS Return Option — Process product returns directly from the POS interface.

* Advanced Barcode Support — Added support for GTIN, EAN, and other barcode formats, including synchronization with WooCommerce.

* Recruitment Module — Manage job openings, applicants, and hiring processes.

* Meeting Module — Schedule and manage meetings within the system.

* Built-in POS Calculator — Quick calculations directly inside the POS screen.

* Marketing Tools Module — Create and manage customer marketing campaigns.

* Real Estate Theme for Online Store — Launch a property-focused storefront with a dedicated real estate theme.

* Complete Serial Number / IMEI Tracking System

* Multiple Selling Units with Different Prices.


🧰 Bug Fixes & Enhancements

+ Updated documentation for better clarity and setup guidance.

+ Resolved multiple minor bugs to improve performance and reliability.


# Version 1.3  - 30-06-2026

## 🚀 Super Admin

### ✨ New Features

+ Added: Urdu language
+ Added: SMS Settings for the Super Admin panel
+ Added: SMS Templates section for the Super Admin panel
+ Added: Per-Plan Module Management — enable/disable any module per plan, ideal for custom private plans.

## 🚀 Tenant

### ✨ New Features

+ Added: Option to Show/Hide Categories in the Online Store.
+ Added: ZATCA Phase 2 integration.
+ Added: Shopify integration with two-way synchronization.
+ Added: Promotions feature with full sales integration, enabling promotional discounts and offers to be managed and automatically applied during transactions.


🧰 Bug Fixes & Enhancements

+ Updated documentation for better clarity and setup guidance.

+ Resolved multiple minor bugs to improve performance and reliability.



# Version 1.4

## 🚀 Super Admin

### ✨ New Features

* Added **Razorpay** payment gateway
* Added **PayFast** payment gateway
* Added **XPay** payment gateway
* Added **LigdiCash** payment gateway
* Added **WaafiPay** payment gateway
* Added **Dodo Payments** payment gateway
* Added **bKash** payment gateway
* Added **SSLCommerz** payment gateway
* Added **Paymob** payment gateway
* Added **Moyasar** payment gateway
* Added **MyFatoorah** payment gateway
* Added a **Blog Module** for improved SEO
* Added **Login as User / User Impersonation** for SaaS administrators
* Added an option to **enable or disable the default landing page**

---

## 🚀 Tenant

### ✨ New Features & Improvements

* Migrated the frontend to **Vue 3 + Ant Design**
* Added **Products Sold Summary** report
* Added **Pakistan FBR Digital Invoicing**
* Added **Multi-Currency Support**
* Added **Google Fonts Integration**
* Added **Document Archive**
* Added **Manufacturing (MRP)**
* Added **Project Management**
* Added **Booking Management**
* Added **Fleet Management**
* Added **Hospital Management**
* Added **School Management**
* Added new **Profit Reports**, including:

  * Profit by Product
  * Profit by Category
  * Profit by Unit
  * Profit by Customer
  * Profit by Warehouse
  * And more
* Added **Today's Summary** drawer to the header navbar
* Added **Promotions** section
* Added **tenant-authored Knowledge Base articles**, on an *Our Articles* tab beside the read-only *Help Center*, with their own groups, drafts and internal-only flag
* Added **Multi-Sidebar** support
* Optimized **Dark Mode**
* Optimized **RTL Support**
* Added **Zapier Integration**
* Added a new **Integrations Hub**
* Added new sidebar sections:

  * **Ecommerce Platforms**
  * **Integrations**

### 🔗 New Integrations

* Added **Salla** integration
* Added **PrestaShop** integration
* Added **Jumia** integration
* Added **Xero** integration
* Added **Google Sheets** integration
* Added **Mailchimp** integration
* Added **Slack Notifications**
* Added **Telegram Notifications**
* Added **Xero auto-sync** for new sales
* Added **Mailchimp auto-sync** for new customers
* Added **Nightly Google Sheets auto-export**
* Added automatic **keep-alive** for Salla and Xero connections
* Added **plan features and role permissions** for all new integrations

### 🖼️ Product Images by Link

* Added the ability to **paste image URLs** directly into the product form
* The product gallery now includes a **"Paste Image Link"** option next to the upload button
* You can now combine uploaded images with hosted image URLs from sources such as **Unsplash, CDNs, and supplier catalogs**

### 🛍️ Online Store

* Added a new **Electronics** theme for electronics, PC, and laptop stores
* Added a new **Toys & Baby** theme
* Added a new **Grocery & Supermarket** theme
* Redesigned the **Real Estate** theme
* Upgraded the **Default Online Store** theme
* Added **PayPal**, **Paystack** and **Flutterwave** to the online store checkout

  * Each tenant charges into its own merchant account, configured on Online Store → Payment Gateway
  * Hosted redirect flow: the order is created payment-pending and only marked paid after a server-side capture (PayPal) or verification (Paystack, Flutterwave) — a forged return can never settle an order
  * Server-to-server webhooks act as the backstop when the shopper's browser never makes it back, and record refunds issued from the gateway dashboard
* Added **Offline Payments** to the online store: **GCash**, **Bank Transfer** and **Cash on Pickup**

  * Each carries the account details, instructions and (for GCash) the QR code shown at checkout, on the thank-you page and on the order page
  * **GCash** and **Bank Transfer** ask the shopper for a screenshot, reference number and amount, which the admin approves or rejects on the order — approving is what marks the order paid
  * **Cash on Pickup** adds per-warehouse pickup branches (address, hours, contact); a collected order needs no delivery address and is charged no shipping
  * The orders list gains a **Proof pending** filter, and the pending-work bell counts proofs waiting to be verified

### ⚙️ Fixes & Improvements

* Fixed module toggles for **WooCommerce, Shopify, and Zapier** in the sidebar
* Fixed **background queue processing** for webhooks and notifications on shared hosting



## 🧰 Bug Fixes & Enhancements

* Updated all reports to the latest Stocky version

  * Improved the **Customers Report** with complete summary cards and revenue-concentration charts
  * Fixed issues in the **School, Serial Number, Service, and Commission Reports**
* Fixed **per-tenant cache isolation** on the file cache driver, where cached values were previously written to and read from different directories
* Updated the documentation with improved **clarity and setup guidance**
* Resolved multiple minor bugs to improve **performance, stability, and reliability**