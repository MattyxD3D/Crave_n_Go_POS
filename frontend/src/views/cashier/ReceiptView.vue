<template>
  <div class="receipt-overlay">
    <div class="receipt-card">

      <!-- Checker strip -->
      <div class="receipt-checker"></div>

      <!-- Brand -->
      <div class="receipt-brand">
        <span class="brand-icon">🍗</span>
        <div>
          <p class="brand-name">Crave N' Go</p>
          <p class="brand-tagline">Thank you for your order!</p>
        </div>
      </div>

      <!-- Pending sync badge -->
      <div v-if="order.status === 'pending_sync'" class="sync-badge">
        ⏳ Saved offline — will sync when connected
      </div>

      <!-- Order number (big) -->
      <div class="order-number-block">
        <p class="order-number-label">ORDER</p>
        <p class="order-number">#{{ order.order_number }}</p>
        <div class="order-meta-pills">
          <span class="meta-pill type-pill">{{ formatOrderType(order.order_type) }}</span>
          <span class="meta-pill method-pill">{{ order.payment_method === 'cash' ? '💵 Cash' : '📱 GCash' }}</span>
        </div>
        <p class="order-date">{{ formatDate(order.created_at) }}</p>
      </div>

      <div class="receipt-dashed" />

      <!-- Line items -->
      <div class="line-items">
        <div
          v-for="(item, index) in resolvedItems"
          :key="index"
          class="line-item"
        >
          <div class="item-main">
            <span class="item-qty-name">{{ item.quantity }}× {{ item.name }}</span>
            <span class="item-price">₱{{ (Number(item.unit_price) * item.quantity).toFixed(0) }}</span>
          </div>
          <div
            v-for="ao in (item.add_ons || [])"
            :key="ao.id"
            class="addon-line"
          >
            <span class="addon-name">+ {{ ao.name }}</span>
            <span class="addon-price">₱{{ (Number(ao.price) * item.quantity).toFixed(0) }}</span>
          </div>
          <div
            v-for="ao in (item.addOns || [])"
            :key="'api-' + ao.id"
            class="addon-line"
          >
            <span class="addon-name">+ {{ ao.name }}</span>
            <span class="addon-price">₱{{ (Number(ao.price) * item.quantity).toFixed(0) }}</span>
          </div>
        </div>
      </div>

      <div class="receipt-dashed" />

      <!-- Totals -->
      <div class="totals-section">
        <div class="total-row">
          <span class="total-label">TOTAL</span>
          <span class="total-amount">₱{{ Number(order.total).toFixed(0) }}</span>
        </div>
        <template v-if="order.payment_method === 'cash'">
          <div class="sub-row">
            <span>Cash Tendered</span>
            <span>₱{{ Number(order.amount_tendered).toFixed(0) }}</span>
          </div>
          <div class="sub-row change-row">
            <span>Change</span>
            <span>₱{{ Number(order.change_amount ?? 0).toFixed(0) }}</span>
          </div>
        </template>
      </div>

      <div class="receipt-dashed" />

      <!-- Status -->
      <div class="status-row">
        <span v-if="order.status !== 'pending_sync'" class="status-success">
          ✓ &nbsp;Payment Successful
        </span>
        <span v-else class="status-pending">
          ⏳ &nbsp;Pending sync
        </span>
      </div>

      <!-- New Order -->
      <button class="new-order-btn" @click="emit('close')">
        🛒 &nbsp;New Order
      </button>

      <!-- Bottom checker -->
      <div class="receipt-checker"></div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  order: { type: Object, required: true }
})

const emit = defineEmits(['close'])

const resolvedItems = computed(() => {
  const raw = props.order.orderItems || props.order.items || []
  return raw.map(item => {
    const name = item.name || item.product?.name || item.combo?.name || 'Item'
    const unit_price = item.unit_price ?? item.price ?? item.product?.price ?? 0
    const quantity = item.quantity ?? 1
    const add_ons = item.add_ons || item.addOns || []
    return { name, unit_price, quantity, add_ons }
  })
})

function formatOrderType(type) {
  if (type === 'dine_in') return '🪑 Dine-in'
  if (type === 'takeout') return '🥡 Takeout'
  return type ?? ''
}

function formatDate(dateStr) {
  if (!dateStr) return new Date().toLocaleString()
  try { return new Date(dateStr).toLocaleString() } catch { return new Date().toLocaleString() }
}
</script>

<style scoped>
/* ── Overlay ───────────────────────────────────────────────────────────────── */
.receipt-overlay {
  position: fixed;
  inset: 0;
  background: rgba(28, 25, 23, 0.6);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1rem;
}

/* ── Card — styled like a thermal receipt ──────────────────────────────────── */
.receipt-card {
  background: #fffdf7;
  color: #1c1917;
  border-radius: var(--radius);
  max-width: 400px;
  width: 100%;
  font-family: 'Courier New', Courier, monospace;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
  display: flex;
  flex-direction: column;
  gap: 0;
  overflow: hidden;
}

/* ── Checker strip ─────────────────────────────────────────────────────────── */
.receipt-checker {
  height: 14px;
  background-image: repeating-conic-gradient(
    #b91c1c 0% 25%,
    #fffdf7 0% 50%
  ) 0 0 / 14px 14px;
  flex-shrink: 0;
}

/* ── Brand ─────────────────────────────────────────────────────────────────── */
.receipt-brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem 0.75rem;
}

.brand-icon { font-size: 2rem; line-height: 1; }

.brand-name {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.4rem;
  color: #b91c1c;
  margin: 0;
  line-height: 1.1;
}

.brand-tagline {
  font-size: 0.72rem;
  color: #78716c;
  margin: 0;
  font-family: sans-serif;
}

/* ── Sync badge ────────────────────────────────────────────────────────────── */
.sync-badge {
  margin: 0 1.5rem;
  text-align: center;
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
  border-radius: 6px;
  padding: 0.4rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 700;
  font-family: sans-serif;
}

/* ── Order number block ────────────────────────────────────────────────────── */
.order-number-block {
  text-align: center;
  padding: 0.75rem 1.5rem 1rem;
}

.order-number-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  color: #78716c;
  text-transform: uppercase;
  font-family: sans-serif;
  margin: 0 0 0.1rem;
}

.order-number {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 2.75rem;
  color: #b91c1c;
  line-height: 1;
  margin: 0 0 0.6rem;
}

.order-meta-pills {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-bottom: 0.4rem;
}

.meta-pill {
  font-size: 0.72rem;
  font-weight: 700;
  font-family: sans-serif;
  border-radius: 999px;
  padding: 0.2rem 0.6rem;
}

.type-pill { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.method-pill { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

.order-date {
  font-size: 0.72rem;
  color: #78716c;
  font-family: sans-serif;
  margin: 0;
}

/* ── Dashed divider ────────────────────────────────────────────────────────── */
.receipt-dashed {
  border: none;
  border-top: 1.5px dashed #d6d3d1;
  margin: 0 1.5rem;
}

/* ── Line items ────────────────────────────────────────────────────────────── */
.line-items {
  padding: 0.75rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.line-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.item-main {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.item-qty-name {
  font-size: 0.875rem;
  font-weight: 600;
  flex: 1;
}

.item-price {
  font-size: 0.875rem;
  font-weight: 700;
  white-space: nowrap;
}

.addon-line {
  display: flex;
  justify-content: space-between;
  padding-left: 1.25rem;
}

.addon-name, .addon-price {
  font-size: 0.75rem;
  color: #78716c;
}

/* ── Totals ────────────────────────────────────────────────────────────────── */
.totals-section {
  padding: 0.75rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 0.25rem;
}

.total-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-family: sans-serif;
  color: #78716c;
}

.total-amount {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 2rem;
  color: #1c1917;
  line-height: 1;
}

.sub-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  color: #57534e;
}

.change-row {
  font-weight: 700;
  color: #15803d;
}

/* ── Status ────────────────────────────────────────────────────────────────── */
.status-row {
  text-align: center;
  padding: 0.5rem 1.5rem;
}

.status-success {
  font-size: 0.95rem;
  font-weight: 700;
  font-family: sans-serif;
  color: #15803d;
}

.status-pending {
  font-size: 0.875rem;
  font-weight: 700;
  font-family: sans-serif;
  color: #b45309;
}

/* ── New Order button ──────────────────────────────────────────────────────── */
.new-order-btn {
  margin: 0.5rem 1.5rem 1.25rem;
  width: calc(100% - 3rem);
  padding: 0.9rem;
  background: var(--color-primary);
  color: white;
  font-size: 1rem;
  font-weight: 700;
  font-family: sans-serif;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  min-height: 48px;
  box-shadow: 0 4px 14px rgba(185, 28, 28, 0.3);
  transition: background 0.15s, transform 0.1s;
}

.new-order-btn:hover { background: #991b1b; }
.new-order-btn:active { transform: scale(0.98); }
</style>
