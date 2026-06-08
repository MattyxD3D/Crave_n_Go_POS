<template>
  <div class="backdrop" @click.self="emit('close')">
    <div class="modal-card">

      <!-- Red accent bar -->
      <div class="card-accent"></div>

      <!-- Header -->
      <div class="modal-header">
        <div class="modal-title-row">
          <span class="modal-emoji">💳</span>
          <h2 class="modal-title">Payment</h2>
        </div>
        <button class="close-btn" @click="emit('close')" aria-label="Close">✕</button>
      </div>

      <!-- Order Summary -->
      <div class="summary-section">
        <p class="section-label">ORDER SUMMARY</p>
        <div class="summary-items">
          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="summary-item"
          >
            <span class="summary-item-name">{{ item.quantity }}× {{ item.name }}</span>
            <span class="summary-item-price">₱{{ itemSubtotal(item).toFixed(0) }}</span>
          </div>
        </div>
        <div class="summary-total-row">
          <span class="summary-total-label">TOTAL</span>
          <span class="summary-total-amount">₱{{ cartStore.total.toFixed(0) }}</span>
        </div>
      </div>

      <div class="divider" />

      <!-- Payment Method -->
      <div class="method-section">
        <p class="section-label">Payment Method</p>
        <div class="method-buttons">
          <button
            class="method-btn"
            :class="{ active: paymentMethod === 'cash' }"
            @click="paymentMethod = 'cash'"
          >
            <span class="method-icon">💵</span>
            <span class="method-label">Cash</span>
          </button>
          <button
            class="method-btn"
            :class="{ active: paymentMethod === 'gcash' }"
            @click="paymentMethod = 'gcash'"
          >
            <span class="method-icon">📱</span>
            <span class="method-label">GCash</span>
          </button>
        </div>
      </div>

      <!-- Cash flow -->
      <div v-if="paymentMethod === 'cash'" class="cash-section">
        <div class="cash-field">
          <label class="field-label" for="amount-tendered">Amount Tendered</label>
          <div class="peso-input-wrap">
            <span class="peso-sign">₱</span>
            <input
              id="amount-tendered"
              v-model="amountTendered"
              type="number"
              min="0"
              step="1"
              class="amount-input"
              placeholder="0"
              @focus="$event.target.select()"
            />
          </div>
        </div>

        <div v-if="change !== null" class="change-box" :class="{ short: change < 0 }">
          <span class="change-label">{{ change < 0 ? 'Insufficient' : 'Change' }}</span>
          <span class="change-amount">₱{{ Math.abs(change).toFixed(0) }}</span>
        </div>

        <p v-if="cashError" class="error-msg">⚠ {{ cashError }}</p>
      </div>

      <!-- GCash flow -->
      <div v-if="paymentMethod === 'gcash'" class="gcash-section">
        <div class="gcash-amount-pill">
          Scan to pay &nbsp;<strong>₱{{ cartStore.total.toFixed(0) }}</strong>
        </div>
        <div class="qr-box">
          <div class="qr-inner">
            <!-- Placeholder QR pattern -->
            <span class="qr-icon">▦</span>
          </div>
          <p class="qr-note">Show merchant QR code here</p>
        </div>
        <p class="gcash-hint">Ask the customer to scan and confirm, then tap <strong>Confirm Payment</strong>.</p>
      </div>

      <!-- Confirm -->
      <div class="modal-footer">
        <button
          class="confirm-btn"
          :disabled="loading"
          @click="handleConfirm"
        >
          <span v-if="loading" class="spinner" />
          <template v-else>
            {{ paymentMethod === 'cash' ? '✓ Confirm Payment' : '✓ Mark as Paid' }}
          </template>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useCartStore } from '@/stores/cart'
import { apiPost } from '@/services/api'
import db from '@/db/index'

const emit = defineEmits(['success', 'close'])

const cartStore = useCartStore()

const paymentMethod = ref('cash')
const amountTendered = ref('')
const loading = ref(false)
const cashError = ref('')

const change = computed(() => {
  if (!amountTendered.value) return null
  const tendered = parseFloat(amountTendered.value)
  if (isNaN(tendered)) return null
  return Math.max(0, tendered - cartStore.total)
})

function itemSubtotal(item) {
  const addOnTotal = (item.add_ons || []).reduce((s, a) => s + Number(a.price), 0)
  return (Number(item.unit_price) + addOnTotal) * item.quantity
}

async function handleConfirm() {
  cashError.value = ''

  if (paymentMethod.value === 'cash') {
    const tendered = parseFloat(amountTendered.value)
    if (!amountTendered.value || isNaN(tendered) || tendered < cartStore.total) {
      cashError.value = `Amount must be at least ₱${cartStore.total.toFixed(0)}`
      return
    }
  }

  loading.value = true

  const tenderedValue = paymentMethod.value === 'cash'
    ? parseFloat(amountTendered.value)
    : null

  const body = {
    order_type: cartStore.orderType,
    payment_method: paymentMethod.value,
    amount_tendered: tenderedValue,
    items: cartStore.items.map(item => ({
      product_id: item.product_id || null,
      combo_id: item.combo_id || null,
      quantity: item.quantity,
      add_on_ids: (item.add_ons || []).map(a => a.id)
    }))
  }

  try {
    const response = await apiPost('/orders', body)
    emit('success', response.data.data)
  } catch {
    try {
      const offline_id = typeof crypto.randomUUID === 'function'
        ? crypto.randomUUID()
        : `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`
      await db.offline_queue.add({
        offline_id,
        order_type: cartStore.orderType,
        payment_method: paymentMethod.value,
        amount_tendered: tenderedValue,
        total: cartStore.total,
        items: cartStore.items.map(item => ({
          product_id: item.product_id || null,
          combo_id: item.combo_id || null,
          name: item.name,
          unit_price: item.unit_price,
          add_ons: (item.add_ons || []).map(a => ({ id: a.id, name: a.name, price: a.price })),
          quantity: item.quantity,
          add_on_ids: (item.add_ons || []).map(a => a.id)
        })),
        created_at: new Date().toISOString(),
        synced: 0
      })

      const fakeOrder = {
        order_number: 'OFFLINE-' + Date.now(),
        order_type: cartStore.orderType,
        payment_method: paymentMethod.value,
        total: cartStore.total,
        amount_tendered: tenderedValue,
        change_amount: change.value,
        status: 'pending_sync',
        orderItems: cartStore.items.map(item => ({ ...item }))
      }

      emit('success', fakeOrder)
    } catch (dbError) {
      cashError.value = 'Failed to save order. Please try again.'
      console.error('Offline queue error:', dbError)
    }
  } finally {
    loading.value = false
  }
}

function onKeyUp(e) {
  if (e.key === 'Escape') emit('close')
}

onMounted(() => document.addEventListener('keyup', onKeyUp))
onUnmounted(() => document.removeEventListener('keyup', onKeyUp))
</script>

<style scoped>
/* ── Backdrop ──────────────────────────────────────────────────────────────── */
.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(28, 25, 23, 0.55);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

/* ── Card ──────────────────────────────────────────────────────────────────── */
.modal-card {
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius);
  width: 100%;
  max-width: 520px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  max-height: 90vh;
  overflow-y: auto;
}

.card-accent {
  height: 5px;
  background: var(--color-primary);
  flex-shrink: 0;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem 1rem;
}

.modal-title-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-emoji { font-size: 1.25rem; }

.modal-title {
  margin: 0;
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.35rem;
  font-weight: 400;
  color: var(--color-text);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-size: 1rem;
  cursor: pointer;
  min-width: 40px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: color 0.15s, background 0.15s;
}

.close-btn:hover {
  color: var(--color-danger);
  background: var(--color-danger-bg);
}

.divider {
  height: 1.5px;
  background: var(--color-border);
  flex-shrink: 0;
}

/* ── Summary ───────────────────────────────────────────────────────────────── */
.summary-section {
  padding: 1rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.section-label {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: var(--color-text-muted);
  text-transform: uppercase;
  margin-bottom: 0.25rem;
}

.summary-items {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
}

.summary-item-name { color: var(--color-text); }
.summary-item-price { font-weight: 700; color: var(--color-primary); }

.summary-total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  border-top: 1.5px solid var(--color-border);
  padding-top: 0.6rem;
  margin-top: 0.25rem;
}

.summary-total-label {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.summary-total-amount {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.75rem;
  color: var(--color-text);
  line-height: 1;
}

/* ── Method ────────────────────────────────────────────────────────────────── */
.method-section {
  padding: 1rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.method-buttons {
  display: flex;
  gap: 0.75rem;
}

.method-btn {
  flex: 1;
  padding: 1rem;
  background: var(--color-bg);
  border: 2px solid var(--color-border);
  border-radius: var(--radius);
  color: var(--color-text-muted);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  min-height: 72px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  transition: border-color 0.15s, color 0.15s, background 0.15s, box-shadow 0.15s;
}

.method-btn.active {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-surface);
  box-shadow: 0 4px 16px rgba(185, 28, 28, 0.15);
}

.method-btn:hover:not(.active) {
  border-color: var(--color-primary);
  color: var(--color-text);
}

.method-icon { font-size: 1.5rem; }
.method-label { font-size: 0.9rem; font-weight: 700; }

/* ── Cash ──────────────────────────────────────────────────────────────────── */
.cash-section {
  padding: 0.5rem 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.cash-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--color-text-muted);
}

.peso-input-wrap {
  display: flex;
  align-items: center;
  background: var(--color-bg);
  border: 2px solid var(--color-border);
  border-radius: var(--radius-sm);
  overflow: hidden;
  transition: border-color 0.15s;
}

.peso-input-wrap:focus-within {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.1);
}

.peso-sign {
  padding: 0 0.5rem 0 1rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text-muted);
  flex-shrink: 0;
}

.amount-input {
  flex: 1;
  padding: 0.85rem 1rem 0.85rem 0;
  background: transparent;
  border: none;
  outline: none;
  color: var(--color-text);
  font-size: 1.5rem;
  font-weight: 800;
  font-family: 'Fredoka One', system-ui, sans-serif;
  min-width: 0;
  width: 100%;
}

.amount-input::placeholder { color: var(--color-border); font-weight: 400; font-family: inherit; font-size: 1.25rem; }
.amount-input::-webkit-outer-spin-button,
.amount-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.amount-input[type=number] { -moz-appearance: textfield; }

/* Change display */
.change-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--color-success-bg);
  border: 1.5px solid var(--color-success);
  border-radius: var(--radius-sm);
  padding: 0.75rem 1rem;
}

.change-box.short {
  background: var(--color-danger-bg);
  border-color: var(--color-danger);
}

.change-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--color-success);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.change-box.short .change-label { color: var(--color-danger); }

.change-amount {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.5rem;
  color: var(--color-success);
  line-height: 1;
}

.change-box.short .change-amount { color: var(--color-danger); }

.error-msg {
  font-size: 0.83rem;
  color: var(--color-danger);
  background: var(--color-danger-bg);
  border: 1.5px solid var(--color-primary-light);
  border-radius: var(--radius-sm);
  padding: 0.5rem 0.75rem;
  margin: 0;
}

/* ── GCash ─────────────────────────────────────────────────────────────────── */
.gcash-section {
  padding: 0.5rem 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.gcash-amount-pill {
  background: var(--color-primary);
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 999px;
  padding: 0.4rem 1.25rem;
}

.qr-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.qr-inner {
  width: 180px;
  height: 180px;
  background: var(--color-surface-2);
  border: 2px dashed var(--color-border);
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-icon {
  font-size: 5rem;
  color: var(--color-text-muted);
  opacity: 0.4;
}

.qr-note {
  font-size: 0.78rem;
  color: var(--color-text-muted);
  font-style: italic;
  margin: 0;
}

.gcash-hint {
  font-size: 0.82rem;
  color: var(--color-text-muted);
  text-align: center;
  margin: 0;
  max-width: 320px;
  line-height: 1.5;
}

/* ── Footer ────────────────────────────────────────────────────────────────── */
.modal-footer {
  padding: 0.75rem 1.5rem 1.5rem;
}

.confirm-btn {
  width: 100%;
  padding: 1rem;
  background: var(--color-primary);
  color: white;
  font-size: 1.05rem;
  font-weight: 700;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  min-height: 54px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 6px 20px rgba(185, 28, 28, 0.35);
  transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
  letter-spacing: 0.02em;
}

.confirm-btn:hover:not(:disabled) {
  background: var(--color-primary-dark);
  box-shadow: 0 8px 24px rgba(185, 28, 28, 0.45);
}

.confirm-btn:active:not(:disabled) { transform: scale(0.98); }
.confirm-btn:disabled { opacity: 0.55; cursor: not-allowed; box-shadow: none; }

.spinner {
  width: 20px;
  height: 20px;
  border: 2.5px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.65s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
