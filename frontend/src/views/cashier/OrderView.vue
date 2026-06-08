<template>
  <div class="order-view">

    <!-- ── Header ── -->
    <header class="header-bar">
      <div class="header-brand">
        <div class="brand-badge">🍗</div>
        <span class="brand-name">Crave N' Go</span>
      </div>

      <div class="order-type-toggle">
        <button
          class="type-btn"
          :class="{ active: cartStore.orderType === 'dine_in' }"
          @click="cartStore.orderType = 'dine_in'"
        >
          🪑 Dine-in
        </button>
        <button
          class="type-btn"
          :class="{ active: cartStore.orderType === 'takeout' }"
          @click="cartStore.orderType = 'takeout'"
        >
          🥡 Takeout
        </button>
      </div>

      <div class="header-spacer" />

      <!-- Sync status: shows pending count or syncing spinner -->
      <div v-if="syncState.syncing.value" class="sync-indicator syncing">
        <span class="sync-spinner"></span>
        <span>Syncing…</span>
      </div>
      <div v-else-if="syncState.pending.value > 0" class="sync-indicator pending">
        <span>⏳ {{ syncState.pending.value }} order{{ syncState.pending.value !== 1 ? 's' : '' }} queued</span>
      </div>

      <div class="online-indicator" :class="{ offline: !isOnline }">
        <span class="status-dot" />
        <span>{{ isOnline ? 'Online' : 'Offline' }}</span>
      </div>

      <button class="logout-btn" @click="handleLogout">Logout</button>
    </header>

    <!-- Thin red accent under header -->
    <div class="header-accent"></div>

    <!-- ── Main Split ── -->
    <main class="main-area">

      <!-- Left: Menu Panel -->
      <section class="menu-panel">

        <!-- Category Tabs -->
        <div class="category-tabs">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="tab-btn"
            :class="{ active: activeCategoryId === cat.id }"
            @click="selectedCategoryId = cat.id"
          >
            <span class="tab-icon">{{ categoryIcon(cat.name) }}</span>
            {{ cat.name }}
          </button>
          <button
            class="tab-btn"
            :class="{ active: selectedCategoryId === 'combos' }"
            @click="selectedCategoryId = 'combos'"
          >
            <span class="tab-icon">🎁</span> Combos
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="state-placeholder">
          <span class="state-emoji">⏳</span>
          <p>Loading menu…</p>
        </div>

        <!-- Product Grid -->
        <div v-else-if="!showingCombos" class="product-grid">
          <button
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-card"
            @click="handleProductTap(product)"
          >
            <div class="product-img-wrap">
              <img
                v-if="product.image_path"
                :src="product.image_path"
                :alt="product.name"
                class="product-img"
              />
              <span v-else class="product-img-placeholder">
                {{ productIcon(product) }}
              </span>
            </div>
            <div class="product-info">
              <p class="product-name">{{ product.name }}</p>
              <p class="product-price">₱{{ Number(product.price).toFixed(0) }}</p>
            </div>
          </button>

          <div v-if="filteredProducts.length === 0" class="state-placeholder">
            <span class="state-emoji">🍽️</span>
            <p>No items in this category</p>
          </div>
        </div>

        <!-- Combos Grid -->
        <div v-else class="product-grid">
          <button
            v-for="combo in combos"
            :key="combo.id"
            class="product-card"
            @click="handleComboTap(combo)"
          >
            <div class="product-img-wrap">
              <img
                v-if="combo.image_url"
                :src="combo.image_url"
                :alt="combo.name"
                class="product-img"
              />
              <span v-else class="product-img-placeholder">🎁</span>
            </div>
            <div class="product-info">
              <p class="product-name">{{ combo.name }}</p>
              <p class="product-price">₱{{ Number(combo.price).toFixed(0) }}</p>
            </div>
          </button>

          <div v-if="combos.length === 0" class="state-placeholder">
            <span class="state-emoji">🎁</span>
            <p>No combos available</p>
          </div>
        </div>
      </section>

      <!-- Right: Cart Panel -->
      <aside class="cart-panel">

        <div class="cart-header">
          <h2 class="cart-title">Current Order</h2>
          <span v-if="cartStore.itemCount > 0" class="cart-badge">
            {{ cartStore.itemCount }}
          </span>
          <span v-else class="cart-subtitle">Nothing added yet</span>
        </div>

        <!-- Cart Items -->
        <div class="cart-items">
          <div v-if="cartStore.items.length === 0" class="cart-empty">
            <span class="cart-empty-icon">🛒</span>
            <p>Tap items on the left<br>to add them here</p>
          </div>

          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="cart-item"
          >
            <div class="cart-item-row">
              <div class="cart-item-info">
                <p class="cart-item-name">{{ item.name }}</p>
                <p class="cart-item-meta">₱{{ Number(item.unit_price).toFixed(0) }} each</p>
                <!-- Add-ons -->
                <div v-if="item.add_ons && item.add_ons.length > 0" class="cart-addons">
                  <span
                    v-for="ao in item.add_ons"
                    :key="ao.id"
                    class="addon-tag"
                  >+ {{ ao.name }} <span class="addon-price">₱{{ Number(ao.price).toFixed(0) }}</span></span>
                </div>
              </div>
              <button class="remove-btn" @click="cartStore.removeItem(item.id)" aria-label="Remove">
                ✕
              </button>
            </div>

            <div class="cart-item-footer">
              <div class="qty-control">
                <button
                  class="qty-btn"
                  @click="cartStore.updateQty(item.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1"
                >−</button>
                <span class="qty-value">{{ item.quantity }}</span>
                <button
                  class="qty-btn"
                  @click="cartStore.updateQty(item.id, item.quantity + 1)"
                >+</button>
              </div>
              <span class="cart-item-subtotal">₱{{ itemSubtotal(item).toFixed(0) }}</span>
            </div>

            <div class="item-divider" />
          </div>
        </div>

        <!-- Cart Footer -->
        <div class="cart-footer">
          <div class="total-row">
            <span class="total-label">TOTAL</span>
            <span class="total-amount">₱{{ cartStore.total.toFixed(0) }}</span>
          </div>
          <button
            class="place-order-btn"
            :disabled="cartStore.items.length === 0"
            @click="showPaymentModal = true"
          >
            🧾 &nbsp;Place Order
          </button>
        </div>
      </aside>
    </main>

    <!-- ── Modals ── -->
    <AddOnModal
      v-if="showAddOnModal"
      :product="selectedProduct"
      @confirm="onAddOnConfirm"
      @close="showAddOnModal = false"
    />

    <PaymentModal
      v-if="showPaymentModal"
      @close="showPaymentModal = false"
      @success="onOrderSuccess"
    />

    <ReceiptView
      v-if="showReceipt"
      :order="completedOrder"
      @close="showReceipt = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { apiGet } from '@/services/api'
import db from '@/db/index'
import { seedCategories, seedProducts, seedCombos } from '@/db/seed'
import { syncState, syncOfflineOrders, refreshPendingCount } from '@/services/sync'
import AddOnModal from '@/components/cashier/AddOnModal.vue'
import PaymentModal from '@/components/cashier/PaymentModal.vue'
import ReceiptView from '@/views/cashier/ReceiptView.vue'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

// ── Data ──────────────────────────────────────────────────────────────────────
const categories = ref([])
const allProducts = ref([])
const combos = ref([])
const loading = ref(false)

// ── Category icon mapping ─────────────────────────────────────────────────────
const ICONS = {
  'siomai': '🥟',
  'everyday meals': '🍳',
  'boneless chicken': '🍗',
  'chicken meals': '🍗',
  'chicken wings': '🔥',
  'wings': '🔥',
  'fried whole chicken': '🐔',
  'crispy liempo': '🥩',
  'french fries': '🍟',
  'snacks': '🍟',
  'beverages': '🥤',
  'add-ons': '➕',
  'add ons': '➕',
}
function categoryIcon(name = '') {
  return ICONS[name?.toLowerCase()] ?? '🍽️'
}
function productIcon(product) {
  if (product.category?.name) return categoryIcon(product.category.name)
  const cat = categories.value.find(c => c.id === product.category_id)
  return categoryIcon(cat?.name)
}

// ── Category selection ────────────────────────────────────────────────────────
const selectedCategoryId = ref(null)

const activeCategoryId = computed(() => {
  if (selectedCategoryId.value !== null && selectedCategoryId.value !== 'combos') {
    return selectedCategoryId.value
  }
  if (selectedCategoryId.value === null && categories.value.length) {
    return categories.value[0]?.id
  }
  return null
})

const filteredProducts = computed(() => {
  if (selectedCategoryId.value === 'combos') return []
  const catId = activeCategoryId.value
  if (!catId) return []
  return allProducts.value.filter(
    p => p.category_id === catId && p.is_available && p.is_active
  )
})

const showingCombos = computed(() => selectedCategoryId.value === 'combos')

// ── Online/offline ─────────────────────────────────────────────────────────────
const isOnline = ref(navigator.onLine)
function handleOnline()  { isOnline.value = true;  syncOfflineOrders() }
function handleOffline() { isOnline.value = false; refreshPendingCount() }

// ── Modal state ───────────────────────────────────────────────────────────────
const showAddOnModal = ref(false)
const selectedProduct = ref(null)
const showPaymentModal = ref(false)
const showReceipt = ref(false)
const completedOrder = ref(null)

// ── Data loading ──────────────────────────────────────────────────────────────
async function loadMenuData() {
  loading.value = true
  try {
    const [catRes, prodRes, comboRes] = await Promise.all([
      apiGet('/categories'),
      apiGet('/products'),
      apiGet('/combos')
    ])
    categories.value = catRes.data.data
    allProducts.value = prodRes.data.data
    combos.value = comboRes.data.data
    // Cache in background — never block or crash the UI
    Promise.all([
      db.categories.bulkPut(categories.value),
      db.products.bulkPut(allProducts.value),
      db.combos.bulkPut(combos.value),
    ]).catch(() => {})
  } catch {
    categories.value = await db.categories.where('is_active').equals(1).toArray()
    allProducts.value = await db.products.where('is_active').equals(1).toArray()
    combos.value = await db.combos.where('is_active').equals(1).toArray()
    if (!categories.value.length && !allProducts.value.length) {
      await Promise.all([
        db.categories.bulkPut(seedCategories),
        db.products.bulkPut(seedProducts),
        db.combos.bulkPut(seedCombos),
      ]).catch(() => {})
      categories.value = seedCategories.filter(c => c.is_active)
      allProducts.value = seedProducts.filter(p => p.is_active)
      combos.value = seedCombos.filter(c => c.is_active)
    }
  } finally {
    loading.value = false
  }
}

// ── Item tap handlers ─────────────────────────────────────────────────────────
function handleProductTap(product) {
  if (product.add_ons && product.add_ons.length > 0) {
    selectedProduct.value = product
    showAddOnModal.value = true
  } else {
    cartStore.addItem({
      product_id: product.id,
      name: product.name,
      unit_price: product.price,
      quantity: 1,
      add_ons: []
    })
  }
}

function handleComboTap(combo) {
  cartStore.addItem({
    combo_id: combo.id,
    name: combo.name,
    unit_price: combo.price,
    quantity: 1,
    add_ons: []
  })
}

// ── Modal callbacks ───────────────────────────────────────────────────────────
function onAddOnConfirm({ product, selectedAddOns }) {
  cartStore.addItem({
    product_id: product.id,
    name: product.name,
    unit_price: product.price,
    quantity: 1,
    add_ons: selectedAddOns
  })
  showAddOnModal.value = false
}

function onOrderSuccess(order) {
  cartStore.clearCart()
  completedOrder.value = order
  showPaymentModal.value = false
  showReceipt.value = true
}

// ── Cart helpers ──────────────────────────────────────────────────────────────
function itemSubtotal(item) {
  const addOnTotal = (item.add_ons || []).reduce((s, a) => s + Number(a.price), 0)
  return (Number(item.unit_price) + addOnTotal) * item.quantity
}

// ── Auth ──────────────────────────────────────────────────────────────────────
async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  window.addEventListener('online', handleOnline)
  window.addEventListener('offline', handleOffline)
  await loadMenuData()
  refreshPendingCount()
})

onUnmounted(() => {
  window.removeEventListener('online', handleOnline)
  window.removeEventListener('offline', handleOffline)
})
</script>

<style scoped>
/* ── Shell ─────────────────────────────────────────────────────────────────── */
.order-view {
  display: grid;
  grid-template-rows: auto 4px 1fr;
  height: 100vh;
  overflow: hidden;
  background: var(--color-bg);
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.header-bar {
  background: var(--color-surface);
  padding: 0 1.25rem;
  height: 62px;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1.5px solid var(--color-border);
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(28,25,23,0.06);
}

.header-accent {
  background: repeating-conic-gradient(
    var(--color-check-a) 0% 25%,
    var(--color-check-b) 0% 50%
  ) 0 0 / 8px 8px;
  flex-shrink: 0;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.brand-badge {
  width: 36px;
  height: 36px;
  background: var(--color-primary);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.brand-name {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.25rem;
  color: var(--color-primary);
  white-space: nowrap;
}

/* ── Order type toggle ─────────────────────────────────────────────────────── */
.order-type-toggle {
  display: flex;
  gap: 0.25rem;
  background: var(--color-surface-2);
  border: 1.5px solid var(--color-border);
  border-radius: 999px;
  padding: 3px;
}

.type-btn {
  padding: 0.3rem 1rem;
  border: none;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: var(--color-text-muted);
  transition: background 0.15s, color 0.15s;
  min-height: 36px;
  white-space: nowrap;
}

.type-btn.active {
  background: var(--color-primary);
  color: #fff;
  box-shadow: 0 2px 8px rgba(185,28,28,0.25);
}

.header-spacer { flex: 1; }

/* ── Sync indicator ────────────────────────────────────────────────────────── */
.sync-indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  font-weight: 600;
  border-radius: 999px;
  padding: 0.25rem 0.75rem;
}

.sync-indicator.syncing {
  background: #eff6ff;
  color: #1d4ed8;
}

.sync-indicator.pending {
  background: var(--color-warning-bg);
  color: var(--color-warning);
}

.sync-spinner {
  width: 12px;
  height: 12px;
  border: 2px solid rgba(29, 78, 216, 0.3);
  border-top-color: #1d4ed8;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ── Online indicator ──────────────────────────────────────────────────────── */
.online-indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-success);
  background: var(--color-success-bg);
  border-radius: 999px;
  padding: 0.25rem 0.75rem;
}

.online-indicator.offline {
  color: var(--color-danger);
  background: var(--color-danger-bg);
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

/* ── Logout ────────────────────────────────────────────────────────────────── */
.logout-btn {
  padding: 0.3rem 1rem;
  background: transparent;
  border: 1.5px solid var(--color-border);
  border-radius: 999px;
  color: var(--color-text-muted);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  min-height: 36px;
  transition: color 0.15s, border-color 0.15s;
}

.logout-btn:hover {
  color: var(--color-danger);
  border-color: var(--color-danger);
}

/* ── Main split ────────────────────────────────────────────────────────────── */
.main-area {
  display: grid;
  grid-template-columns: 60fr 40fr;
  overflow: hidden;
  min-height: 0;
}

/* ── Menu panel ────────────────────────────────────────────────────────────── */
.menu-panel {
  overflow-y: auto;
  padding: 1.25rem;
  background: var(--color-bg);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-height: 0;
}

/* ── Category tabs ─────────────────────────────────────────────────────────── */
.category-tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
  flex-shrink: 0;
  scrollbar-width: none;
}
.category-tabs::-webkit-scrollbar { display: none; }

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0 1.1rem;
  height: 44px;
  border: 2px solid var(--color-border);
  border-radius: 999px;
  background: var(--color-surface);
  color: var(--color-text-muted);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s, color 0.15s, border-color 0.15s, box-shadow 0.15s;
  box-shadow: 0 1px 3px rgba(28,25,23,0.06);
}

.tab-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: #fff;
  box-shadow: 0 4px 12px rgba(185,28,28,0.28);
}

.tab-btn:hover:not(.active) {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.tab-icon { font-size: 1rem; }

/* ── State placeholders ────────────────────────────────────────────────────── */
.state-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--color-text-muted);
  font-size: 0.95rem;
  gap: 0.75rem;
  grid-column: 1 / -1;
  text-align: center;
}

.state-emoji { font-size: 2.5rem; }

/* ── Product grid ──────────────────────────────────────────────────────────── */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
  gap: 0.875rem;
  align-content: start;
}

.product-card {
  background: var(--color-surface);
  border-radius: var(--radius);
  padding: 0;
  cursor: pointer;
  text-align: center;
  border: 2px solid var(--color-border);
  transition: border-color 0.15s, transform 0.1s, box-shadow 0.15s;
  user-select: none;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(28,25,23,0.07);
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 6px 20px rgba(185,28,28,0.15);
}

.product-card:active {
  transform: scale(0.96);
}

.product-img-wrap {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: var(--color-surface-2);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-bottom: 1.5px solid var(--color-border);
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-img-placeholder {
  font-size: 2.75rem;
  line-height: 1;
}

.product-info {
  padding: 0.6rem 0.5rem 0.75rem;
}

.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text);
  margin-bottom: 0.25rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-price {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--color-primary);
  font-family: 'Fredoka One', system-ui, sans-serif;
}

/* ── Cart panel ────────────────────────────────────────────────────────────── */
.cart-panel {
  background: var(--color-surface);
  display: flex;
  flex-direction: column;
  border-left: 1.5px solid var(--color-border);
  min-height: 0;
}

.cart-header {
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-shrink: 0;
  border-bottom: 1.5px solid var(--color-border);
  background: var(--color-surface);
}

.cart-title {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.1rem;
  color: var(--color-text);
  flex: 1;
}

.cart-badge {
  background: var(--color-primary);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 999px;
  padding: 0.15rem 0.6rem;
  min-width: 24px;
  text-align: center;
}

.cart-subtitle {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

/* ── Cart items list ───────────────────────────────────────────────────────── */
.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 1.25rem;
  min-height: 0;
}

.cart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--color-text-muted);
  gap: 0.75rem;
  padding: 3rem 1rem;
  text-align: center;
}

.cart-empty-icon { font-size: 2.5rem; opacity: 0.4; }
.cart-empty p { margin: 0; font-size: 0.9rem; line-height: 1.5; }

/* ── Cart item ─────────────────────────────────────────────────────────────── */
.cart-item { margin-bottom: 0.1rem; }

.cart-item-row {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.cart-item-info { flex: 1; min-width: 0; }

.cart-item-name {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--color-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cart-item-meta {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  margin-top: 0.1rem;
}

.cart-addons {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  margin-top: 0.3rem;
}

.addon-tag {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  background: var(--color-surface-2);
  border-radius: 4px;
  padding: 0.1rem 0.4rem;
  display: inline-block;
  width: fit-content;
}

.addon-price {
  color: var(--color-primary);
  font-weight: 700;
}

.remove-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  font-size: 0.8rem;
  padding: 0;
  min-width: 32px;
  min-height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
  margin-top: 2px;
}

.remove-btn:hover {
  color: var(--color-danger);
  background: var(--color-danger-bg);
}

/* ── Qty controls ──────────────────────────────────────────────────────────── */
.cart-item-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
  margin-bottom: 0.1rem;
}

.qty-control {
  display: flex;
  align-items: center;
  border: 1.5px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  background: var(--color-surface-2);
}

.qty-btn {
  background: transparent;
  border: none;
  color: var(--color-text);
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
  flex-shrink: 0;
  min-height: unset;
}

.qty-btn:hover:not(:disabled) { background: var(--color-border); }
.qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.qty-value {
  min-width: 30px;
  text-align: center;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-text);
}

.cart-item-subtotal {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--color-primary);
  font-family: 'Fredoka One', system-ui, sans-serif;
}

.item-divider {
  height: 1px;
  background: var(--color-border);
  margin: 0.65rem 0;
  opacity: 0.7;
}

/* ── Cart footer ───────────────────────────────────────────────────────────── */
.cart-footer {
  padding: 1rem 1.25rem 1.25rem;
  border-top: 1.5px solid var(--color-border);
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  background: var(--color-surface);
}

.total-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
}

.total-label {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.total-amount {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 2rem;
  color: var(--color-text);
  line-height: 1;
}

.place-order-btn {
  width: 100%;
  padding: 1rem;
  background: var(--color-primary);
  color: #fff;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  min-height: 56px;
  box-shadow: 0 6px 20px rgba(185,28,28,0.35);
  transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
}

.place-order-btn:hover:not(:disabled) {
  background: var(--color-primary-dark);
  box-shadow: 0 8px 24px rgba(185,28,28,0.45);
}

.place-order-btn:active:not(:disabled) { transform: scale(0.98); }

.place-order-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
  box-shadow: none;
}

/* ── Mobile Optimization ─────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .order-view {
    height: 100vh;
  }

  .header-bar {
    flex-wrap: wrap;
    height: auto;
    padding: 0.75rem 1rem;
    gap: 0.75rem;
  }

  .main-area {
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .menu-panel {
    flex: 1;
    min-height: 40vh;
  }

  .cart-panel {
    height: 45vh;
    border-left: none;
    border-top: 2px solid var(--color-border);
    flex-shrink: 0;
  }

  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }

  .cart-footer {
    padding: 0.75rem 1rem;
  }
  
  .total-amount {
    font-size: 1.5rem;
  }
  
  .place-order-btn {
    min-height: 48px;
    font-size: 1rem;
    padding: 0.75rem;
  }
}
</style>
