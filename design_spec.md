# Crave N' Go — Design Spec

> For use with Claude Design / Figma / any UI tool to finalize look and feel.

---

## 1. Brand Story & Business Context

**Crave N' Go** is a Filipino small business born from simple observation, passion, and community spirit.

The owner spent months studying his neighborhood before deciding to sell cooked meals and snacks. To ensure quality, he and the team attended seminars and training specifically for **flavored boneless chicken and chicken wings** — because they wanted food people would keep coming back for, not just once. They even sell the **same chicken sauces used in-store** so customers can bring the flavor home.

> *"We want people to enjoy delicious snacks and meals without spending too much, while also giving them a good and satisfying experience every time they buy from us."*

**Brand values:**
- Affordable but never cheap-feeling
- Quality you can taste (they trained for it)
- Community-first — not a chain, a neighbor's stall
- Something worth coming back to

---

## 2. Visual Identity — From the Physical Menu

The physical menu gives a strong direction for the digital design. It uses a **warm retro Filipino fast food aesthetic**:

| Element | Physical Menu Style |
|---|---|
| Background | Warm cream / off-white (`#fdf6e3` range) |
| Primary accent | Deep red / brick red (`#b02020` range) |
| Borders | Checkerboard pattern in red + white |
| Typography | Bold display font (brush/handwritten for headings), clean sans for prices |
| Mood | Fun, nostalgic, approachable — like a neighborhood carinderia that leveled up |
| Food photos | Real food photography, close-up, warm lighting |

**For the POS app, this translates to:**
- Warm background tones (not cold dark mode — or offer both)
- Red as the primary brand color (not orange — match the physical branding)
- Bold, fun category headers
- Food card images are important — customers associate the photo with the item
- The checkered motif can appear as a subtle divider, border accent, or header decoration

> **Design decision needed:** The current app code uses a dark mode (`#0f0f1a` bg, orange accent). The physical menu is warm/light with red accent. The final design should pick one direction — see Open Questions section.

---

## 3. Actual Menu (Real Products & Prices)

### Siomai

| Item | Steam | Fried |
|---|---|---|
| Pork | ₱50 | ₱55 |
| Beef | ₱50 | ₱55 |
| Chicken | ₱50 | ₱55 |
| Dynamite | ₱50 | ₱55 |
| Japanese | ₱50 | ₱55 |
| Sharksfin | ₱50 | — |
| Chinese Kikiam | ₱50 | — |

*Note: Siomai has a Steam/Fried variant — this is an add-on/modifier, not a separate product.*

---

### Everyday Meals

All served with Java Rice and Egg.

| Item | Price |
|---|---|
| Beef Tapa | ₱95 |
| Pork Tocino | ₱95 |
| Pork Sisig | ₱95 |
| Corned Beef | ₱95 |
| Cheezy Hungarian | ₱85 |
| Breakfast Longganisa | ₱85 |
| Pork Ham | ₱85 |
| Crispy Bacon | ₱85 |
| Hotdog Bacon Wrap | ₱85 |

---

### Boneless Chicken

Flavors available: **Honey BBQ, Garlic Butter, Japanese BBQ, Spicy BBQ, Yangnyeom, Fireball Buffalo**

| Set | Price | Includes |
|---|---|---|
| Solo Sulit Meal | ₱95 | 2 pcs, 1 flavor |
| Ka-Share Set | ₱170 | 4 pcs, 1 flavor |
| Small Barkada Combo | ₱250 | 6 pcs, 2 flavors |
| Solid Barkada Set | ₱470 | 12 pcs, 3 flavors |

---

### Chicken Wings

Same flavors as Boneless Chicken.

| Set | Price | Includes |
|---|---|---|
| Quick Bite Meal | ₱85 | 2 pcs, 1 flavor |
| Share Ko'to Set | ₱160 | 4 pcs, 2 flavors |
| Chickahan Combo | ₱240 | 6 pcs, 2 flavors |
| Barkada Wings | ₱470 | 12 pcs, 3 flavors |

---

### Other Mains

| Item | Price |
|---|---|
| Fried Whole Chicken | ₱220 |
| Crispy Liempo | ₱120 |

---

### French Fries

| Size | Price |
|---|---|
| Small | ₱35 |
| Large | ₱55 |

Flavors: Cheese, BBQ, Sour Cream

---

### Add-ons

| Item | Price |
|---|---|
| Java Rice | ₱20 |
| Plain Rice | ₱15 |
| Egg | ₱15 |

---

### Beverages

| Item | Price |
|---|---|
| Coke | ₱20 |
| Sprite | ₱20 |
| Royal | ₱20 |
| Mountain Dew | ₱20 |
| Cobra | ₱20 |
| Sting | ₱20 |
| Bottled Water (500mL) | ₱15 |
| Bottled Water (1 Liter) | ₱25 |

---

## 4. Menu Categories (for POS navigation)

These are the tabs the cashier sees on the Order Screen:

| # | Category | Icon idea |
|---|---|---|
| 1 | Siomai | 🥟 |
| 2 | Everyday Meals | 🍳 |
| 3 | Boneless Chicken | 🍗 |
| 4 | Chicken Wings | 🔥 |
| 5 | Whole Chicken | 🐔 |
| 6 | Crispy Liempo | 🥩 |
| 7 | French Fries | 🍟 |
| 8 | Beverages | 🥤 |
| 9 | Add-ons | ➕ |

---

## 5. Special UI Considerations from the Real Menu

### Flavor Selection (Boneless Chicken & Wings)
Boneless Chicken and Wings sets allow **multiple flavors depending on the set** (1, 2, or 3 flavors). The POS needs a **flavor picker modal** that:
- Shows how many flavors the customer can pick (based on which set they ordered)
- Lists the 6 flavors as selectable chips/buttons
- Disables further selection once the limit is reached

**6 flavors:** Honey BBQ · Garlic Butter · Japanese BBQ · Spicy BBQ · Yangnyeom · Fireball Buffalo

### Siomai Cooking Style
Siomai items need a **Steam / Fried** variant selector — same price difference (+₱5 for fried). This is not a free-form add-on but a required choice before adding to cart.

### Fries Flavor
French Fries have a flavor modifier (Cheese / BBQ / Sour Cream) — required choice before adding to cart.

### Set Meals Include Rice + Egg
Everyday Meals come with Java Rice and Egg. The receipt/cart should note this so the cashier knows what to prepare. It's not a selectable add-on — it's included.

---

## 6. What Is This App?

**Crave N' Go POS** is a tablet-first Point of Sale system for the Crave N' Go store. It runs as a **Progressive Web App (PWA)** in a tablet browser — no app store install needed. Works fully offline, syncs when internet returns.

**Two user types:**
- **Cashier** — takes orders, processes payment, shows digital receipts
- **Admin/Manager** — manages menu, inventory, views sales reports

---

## 7. Tech Stack (for reference)

| Layer | Tech |
|---|---|
| Frontend | Vue 3 + Vite PWA |
| State | Pinia |
| Local DB (offline) | Dexie.js (IndexedDB) |
| Backend API | Laravel 11 REST API |
| Auth | Laravel Sanctum (token-based) |
| Payment | Cash + GCash/GrabPay (QR display) |
| Target device | Tablet browser (landscape, ~1024×768 min) |

---

## 8. Current Code Design Tokens

Already in `style.css`. The new design should confirm or replace these.

| Token | Current Value | Role |
|---|---|---|
| `--color-primary` | `#e85d04` | CTA buttons, active states |
| `--color-primary-dark` | `#c44d03` | Hover / pressed |
| `--color-bg` | `#0f0f1a` | Page background |
| `--color-surface` | `#1a1a2e` | Cards, panels |
| `--color-surface-2` | `#22223b` | Input backgrounds |
| `--color-border` | `#2d2d4a` | Dividers |
| `--color-text` | `#f0f0f5` | Primary text |
| `--color-text-muted` | `#8888aa` | Labels, secondary |
| `--color-success` | `#22c55e` | Paid, online, in stock |
| `--color-danger` | `#ef4444` | Cancel, error, low stock |
| `--color-warning` | `#f59e0b` | Low stock warning |
| `--radius` | `12px` | Cards, modals |
| `--radius-sm` | `8px` | Inputs, small buttons |
| `--touch-min` | `44px` | Min touch target height |

**Current font:** Inter

---

## 9. Screen Inventory

### 9.1 Login Screen
- Single centered card
- Logo / brand name at top
- Email + password fields
- Login button (primary CTA)
- No self-registration — accounts created by admin

---

### 9.2 Cashier — Order Screen (MAIN SCREEN)

Split-panel layout, landscape tablet:

```
┌───────────────────────────────────────────────────────────┐
│  [Logo] Crave N' Go   [Dine-in] [Takeout]     🟢 Online  │
├─────────────────────────┬─────────────────────────────────┤
│  Category tabs          │  CART                           │
│  [Siomai][Everyday]...  │                                 │
│  ────────────────────   │  1x Solo Sulit Meal     ₱95     │
│  Product grid (2–3 col) │    Flavor: Honey BBQ            │
│                         │  1x Java Rice           ₱20     │
│  ┌──────┐  ┌──────┐    │  ─────────────────────────────  │
│  │ IMG  │  │ IMG  │    │  TOTAL              ₱115.00      │
│  │ Name │  │ Name │    │                                  │
│  │ ₱95  │  │ ₱85  │    │  [     PLACE ORDER     ]        │
│  └──────┘  └──────┘    │                                  │
└─────────────────────────┴─────────────────────────────────┘
```

**Interactions:**
- Tap product → flavor/variant picker modal if needed → add to cart
- Cart rows: name, selected flavors/variants, `+` / `−` / `×`
- `Dine-in / Takeout` toggle in header
- Online/Offline indicator (green dot = online, red = offline)

---

### 9.3 Flavor / Variant Picker Modal

Triggered when tapping Boneless Chicken, Wings, Siomai, or Fries.

**Boneless / Wings:**
- Show set name + how many flavors allowed (e.g., "Pick 2 flavors")
- 6 flavor chips — tap to select, auto-disables at limit
- Quantity stepper
- `Add to Cart` button

**Siomai:**
- Required: Steam or Fried (radio / toggle)
- Quantity stepper
- `Add to Cart` button

**Fries:**
- Required: Cheese / BBQ / Sour Cream (radio or chip)
- Size already known from which product was tapped
- `Add to Cart` button

---

### 9.4 Add-on Modal (Rice, Egg, etc.)
For products where add-ons are optional (e.g., adding extra rice to a wings order):
- Checkbox list of available add-ons with prices
- `Add to Cart` button

---

### 9.5 Payment Modal
Triggered by `PLACE ORDER`.

**Step 1 — Select payment method:**
- Cash
- GCash / GrabPay

**Step 2a — Cash:**
- Display order total
- Large numpad or input for "Amount tendered"
- Live computed change
- `Confirm Payment` button

**Step 2b — GCash/GrabPay:**
- Display order total
- Show merchant QR code (static image)
- `Mark as Paid` button

---

### 9.6 Receipt Screen
Full screen after successful payment.

- `ORDER #XXXX` — large and prominent
- Date + time
- Cashier name
- Order type (Dine-in / Takeout)
- Itemized list: product, flavor/variant, qty, price
- Total
- Payment method + amount paid + change (if cash)
- `New Order` button → resets cart, back to Order Screen

---

### 9.7 Admin — Layout
Sidebar navigation with:
- Products
- Categories
- Combos / Sets
- Add-ons & Flavors
- Inventory
- Reports
- Logout

---

### 9.8 Admin — Product List
- Grid or table: Image, Name, Category, Price, Stock, Available toggle, Edit/Delete
- `+ Add Product` button
- Availability toggle per product (hides/shows on cashier screen)

**Product form fields:**
- Name, Category (dropdown), Price (₱), Description, Image upload, Stock qty, Is Available toggle

---

### 9.9 Admin — Category List
- List: Icon, Name, Sort Order, Edit/Delete
- `+ Add Category`

---

### 9.10 Admin — Inventory View
- Table: Product, Category, Stock, Status (OK / Low / Out)
- Low stock rows highlighted
- `Adjust Stock` per row → modal: add qty or set qty + reason
- Inventory log: timestamp, product, change, reason, order ref

---

### 9.11 Admin — Reports

1. **Daily Summary** — KPI cards: Revenue, Orders, Avg Order Value + date picker
2. **Top Selling Items** — ranked table with qty sold and revenue
3. **Hourly Breakdown** — bar chart by hour, toggle revenue vs order count

---

## 10. Component Patterns

| Component | Notes |
|---|---|
| Primary Button | Brand red (or orange), full width where applicable, min 44px height |
| Secondary Button | Outlined |
| Danger Button | Red |
| Flavor Chip | Selectable pill, highlight when selected, disabled when limit reached |
| Product Card | Image (required), name, price, touch target covers whole card |
| Cart Row | Item name + flavor line + qty controls + price |
| Modal | Centered overlay, branded header |
| Toggle | Dine-in/Takeout in header, product availability in admin |
| Status Dot | Online (green) / Offline (red) — always visible on cashier screen |
| Toast | Auto-dismiss, bottom of screen |

---

## 11. User Flows

### Cashier — Boneless Chicken Order
```
Order Screen → [Boneless Chicken] tab → tap "Solo Sulit Meal ₱95"
  → Flavor Modal: pick 1 flavor (e.g. Yangnyeom)
  → Add to Cart → cart shows item + flavor
  → (optionally add Java Rice ₱20 as add-on)
  → PLACE ORDER → Cash/GCash → Receipt → New Order
```

### Cashier — Offline
```
Tablet goes offline → red dot shows
Place orders normally → saved to local DB
Tablet reconnects → orders sync in background → green dot
```

### Admin — Update Menu
```
Login → Products → Edit product → change price or toggle off
→ Save → cashier screen reflects change immediately
```

---

## 12. Open Design Questions

| # | Question | Why it matters |
|---|---|---|
| 1 | **Light (warm cream) or Dark mode?** The physical menu is warm/light — should the app match? | Sets the entire color palette |
| 2 | **Primary brand color: Red (physical menu) or Orange (current code)?** | Brand consistency vs current implementation |
| 3 | **Does Crave N' Go have a logo file?** | Needed for PWA icon, login screen, receipt header |
| 4 | **GCash QR — static merchant QR or dynamic per transaction?** | Changes payment modal complexity |
| 5 | **Order number format — daily reset (#001 each day) or global?** | Affects receipt design |
| 6 | **Category icons — emoji, Lucide SVG, or custom?** | Navigation tab style |
| 7 | **Admin nav — sidebar or top nav?** | Screen real estate on tablet |
| 8 | **Receipt — on-screen only, or add a Bluetooth printer option?** | Hardware scope |
| 9 | **Checkered border motif from physical menu — use it in the app?** | Brand authenticity vs clean digital UI |

---

## 13. API Surface

All endpoints require Bearer token auth (except login).

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/categories` | List categories |
| GET | `/api/products` | List products |
| POST | `/api/products` | Create product |
| PUT | `/api/products/{id}` | Update product |
| PATCH | `/api/products/{id}/toggle` | Toggle availability |
| GET | `/api/combos` | List combos |
| GET | `/api/add-ons` | List add-ons |
| POST | `/api/orders` | Place order |
| POST | `/api/orders/sync` | Bulk sync offline orders |
| PATCH | `/api/orders/{id}/cancel` | Cancel order |
| GET | `/api/inventory` | View stock levels |
| POST | `/api/inventory/{id}/adjust` | Adjust stock |
| GET | `/api/inventory/{id}/logs` | Inventory history |
| GET | `/api/reports/daily` | Daily sales summary |
| GET | `/api/reports/top-products` | Top selling items |
| GET | `/api/reports/hourly` | Hourly breakdown |

---

## 14. Constraints

- **Touch first** — 44px minimum tap target on all interactive elements
- **Landscape tablet** — primary viewport ~1024×768; portrait is fallback
- **No hover dependency** — interactions must work without hover
- **Fast cashier flow** — no heavy animations on the order path
- **Offline-first** — online/offline state always visible
- **Currency** — Philippine Peso (₱), whole numbers only
- **Language** — English (menu item names stay as-is from physical menu)
