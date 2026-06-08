# FastFood POS System — Implementation Plan

## Overview

A tablet-optimized, offline-capable Point of Sale system for a generic fast food restaurant (Jollibee/McDonald's style). Built as a **Vue 3 PWA** (runs in any tablet browser) connected to a **Laravel 11 REST API**, with full offline support via IndexedDB and Background Sync.

---

## Decisions Summary

| Decision | Choice |
|---|---|
| Business type | Generic fast food (burgers, chicken, fries, drinks) |
| Store scale | Single store now, architected for multi-branch later |
| User roles | Admin/Manager + Cashier |
| Payment methods | Cash + GCash/GrabPay (QR-based) |
| Order types | Dine-in / Takeout |
| Menu features | Combos/sets + Add-ons/upgrades |
| Inventory tracking | Finished product-level (per SKU) |
| Receipts | On-screen digital receipt |
| Reports | Daily sales summary, Top-selling items, Hourly breakdown |
| Frontend | Vue 3 + Vite PWA (tablet browser) |
| Backend | Laravel 11 REST API |
| Hosting | Local dev → Cloud production |
| Discounts | None for MVP |

---

## Architecture

```
[Tablet Browser]
  Vue 3 PWA (Vite + vite-plugin-pwa)
  ├── Pinia (state management)
  ├── Dexie.js (IndexedDB wrapper — local DB)
  ├── Workbox (Service Worker — caching + sync)
  └── Vue Router

        ↕ HTTPS (online) / Queued Sync (offline)

[Laravel 11 API]
  ├── Laravel Sanctum (token auth)
  ├── REST API (JSON)
  └── MySQL / PostgreSQL

[Offline Strategy]
  ├── Menu/Products → cached on login, refreshed every 30 min
  ├── Orders → written to IndexedDB, synced when online
  └── Inventory → updated per transaction, synced after each order
```

---

## Database Models (Laravel)

### Core Tables

```
users               → id, name, email, password, role (admin|cashier), branch_id
categories          → id, name, icon, sort_order, is_active
products            → id, category_id, name, description, price, image, stock_qty,
                      is_available, is_active
combos              → id, name, description, price, image, is_active
combo_items         → id, combo_id, product_id, quantity
add_ons             → id, name, price, is_active
product_add_ons     → id, product_id, add_on_id  (pivot)

orders              → id, order_number, cashier_id, order_type (dine_in|takeout),
                      payment_method (cash|gcash), subtotal, total,
                      status (pending|paid|cancelled), synced_at,
                      created_at
order_items         → id, order_id, product_id, combo_id, quantity, unit_price,
                      subtotal, notes
order_item_add_ons  → id, order_item_id, add_on_id, price

inventory_logs      → id, product_id, change_qty, reason, order_id, created_at
```

---

## Feature Scope — MVP

### 🔐 Auth
- [x] Login with email + password (Sanctum token)
- [x] Role-based access: Admin vs Cashier
- [x] Offline token caching (token stored in IndexedDB, valid for session)

### 🍔 Menu Management (Admin)
- [x] Categories CRUD (with icon + sort order)
- [x] Products CRUD (name, price, image upload, stock qty, availability toggle)
- [x] Combos CRUD (bundle multiple products at a set price)
- [x] Add-ons management (e.g., Extra Cheese +₱20, Upsize +₱15)
- [x] Assign add-ons to products

### 📦 Inventory (Admin)
- [x] View current stock levels per product
- [x] Manual stock adjustment (add stock / set stock)
- [x] Low stock alerts (configurable threshold, shown in dashboard)
- [x] Inventory log (history of changes)

### 🛒 Cashier Order Screen (Tablet UI)
- [x] Browse menu by category (big touch-friendly buttons)
- [x] Add products / combos to cart
- [x] Add add-ons per item
- [x] Set order type: Dine-in / Takeout
- [x] View and edit cart (qty up/down, remove)
- [x] Place order → deducts stock
- [x] Payment flow: Cash (enter amount → compute change) / GCash (show QR)
- [x] On-screen digital receipt after payment
- [x] Order number display (auto-increment daily, e.g., #0042)

### 📊 Reports (Admin)
- [x] Daily sales summary (total revenue, total orders, average order value)
- [x] Top-selling items (by quantity and revenue)
- [x] Hourly sales breakdown (bar chart)

### 🔌 Offline Mode
- [x] Full cashier order flow works offline
- [x] Menu + products cached in IndexedDB on login
- [x] Orders saved locally → synced to server on reconnect
- [x] Inventory decremented locally → reconciled on sync
- [x] Online/offline status indicator in UI

---

## Offline Sync Strategy

**Approach: Event Sourcing (not state sync)**

Instead of syncing the final state of inventory, we sync **what happened**:
- "Sold 2x Cheeseburger" instead of "Stock = 43"

This prevents conflicts when multiple tablets are offline simultaneously.

```
Offline Order Flow:
  1. Cashier places order → saved to IndexedDB (synced: false)
  2. Stock decremented locally in IndexedDB
  3. IndexedDB queues a "sync-orders" Background Sync event
  4. When online: Service Worker flushes queue → POST /api/orders
  5. Laravel applies orders, adjusts real inventory
  6. Response marks local order as synced: true
```

---

## UI Layout (Tablet — Cashier View)

```
┌─────────────────────────────────────────────────────────┐
│  🍔 FastFood POS          Dine-in | Takeout    🟢 Online │
├──────────────────────┬──────────────────────────────────┤
│  [Burgers] [Chicken] │  CART                            │
│  [Fries]  [Drinks]   │  ─────────────────────────────   │
│                      │  1x Cheeseburger         ₱89     │
│  ┌──────┐ ┌──────┐   │  + Extra Cheese          ₱20     │
│  │ IMG  │ │ IMG  │   │  1x Large Fries           ₱69    │
│  │Burger│ │Chicken│  │  ─────────────────────────────   │
│  │ ₱89  │ │ ₱109 │   │  TOTAL              ₱178.00      │
│  └──────┘ └──────┘   │                                  │
│  ┌──────┐ ┌──────┐   │  [    PLACE ORDER    ]           │
│  │ IMG  │ │ IMG  │   │                                  │
│  │Fries │ │Combo │   │                                  │
│  │ ₱59  │ │ ₱199 │   │                                  │
│  └──────┘ └──────┘   │                                  │
└──────────────────────┴──────────────────────────────────┘
```

---

## Project Structure

```
fastfood-pos/
├── backend/                    ← Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ComboController.php
│   │   │   ├── AddOnController.php
│   │   │   ├── OrderController.php
│   │   │   ├── InventoryController.php
│   │   │   └── ReportController.php
│   │   └── Models/
│   ├── database/migrations/
│   └── routes/api.php
│
└── frontend/                   ← Vue 3 + Vite PWA
    ├── src/
    │   ├── components/
    │   ├── views/
    │   │   ├── cashier/        ← Order screen
    │   │   └── admin/          ← Dashboard, menu, inventory
    │   ├── stores/             ← Pinia stores
    │   ├── db/                 ← Dexie.js (IndexedDB)
    │   ├── services/           ← API calls + offline fallback
    │   └── sw/                 ← Service Worker logic
    └── vite.config.js
```

---

## Build Phases

### Phase 1 — Laravel API (Backend)
1. Laravel project setup + Sanctum auth
2. Migrations + seeders (categories, products, add-ons, combos)
3. API routes + controllers (CRUD for all models)
4. Orders API with inventory deduction
5. Reports API endpoints

### Phase 2 — Vue 3 Frontend Foundation
1. Vite PWA project setup
2. Router + Pinia + Dexie.js setup
3. Login screen + auth flow
4. Design system (colors, typography, touch-friendly components)

### Phase 3 — Cashier UI
1. Menu browsing (categories + products grid)
2. Cart sidebar
3. Combo + add-on selection modals
4. Order type toggle (Dine-in / Takeout)
5. Payment flow (Cash + GCash QR)
6. Digital receipt screen

### Phase 4 — Admin Dashboard
1. Product/Category CRUD
2. Combo + add-on management
3. Inventory view + stock adjustment
4. Reports (daily summary, top items, hourly chart)

### Phase 5 — Offline PWA
1. Service Worker + Workbox setup
2. Dexie.js schema (mirrors server tables)
3. Offline order flow + sync queue
4. Online/offline status indicator
5. Background sync on reconnect

---

## Verification Plan

### API Testing
- Laravel feature tests for all endpoints
- Sanctum auth guards verified per role

### Frontend Testing
- Manual tablet UI walkthrough (Chrome DevTools — tablet emulation)
- Offline simulation (DevTools → Network → Offline)
- Sync test: place order offline → go online → verify order appears in DB

### Build Check
- `npm run build` produces valid PWA manifest + service worker
- Lighthouse PWA audit score ≥ 90

---

## Open Questions

> [!NOTE]
> These are non-blocking for now but will need answers before Phase 5.

1. **QR Code for GCash** — Will each store have a static QR code (merchant QR), or do you need dynamic QR generation per transaction?
2. **Order number format** — Daily reset (e.g., #001 each day) or global auto-increment?
3. **Multi-device conflict** — If 2 cashier tablets go offline and both sell the last item in stock, how should the system handle this on sync? (Recommend: allow it and flag for manager review)
4. **Image hosting** — Store product images on the server (Laravel storage) or use a CDN (e.g., Cloudflare R2)?
