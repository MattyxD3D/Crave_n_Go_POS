<template>
  <div class="backdrop" @click.self="emit('close')">
    <div class="modal-card" @keyup.esc.stop>
      <!-- Header -->
      <div class="modal-header">
        <div>
          <h2 class="modal-title">Customize: {{ product.name }}</h2>
          <p class="base-price">Base price: ₱{{ Number(product.price).toFixed(2) }}</p>
        </div>
        <button class="close-btn" @click="emit('close')" aria-label="Close">✕</button>
      </div>

      <div class="divider" />

      <!-- Add-ons List -->
      <div class="addons-section">
        <p class="section-label">Add-ons:</p>
        <label
          v-for="ao in product.add_ons"
          :key="ao.id"
          class="addon-row"
          :class="{ selected: selectedAddOnIds.has(ao.id) }"
        >
          <input
            type="checkbox"
            class="addon-checkbox"
            :checked="selectedAddOnIds.has(ao.id)"
            @change="toggleAddOn(ao.id)"
          />
          <span class="addon-name">{{ ao.name }}</span>
          <span class="addon-price">+₱{{ Number(ao.price).toFixed(2) }}</span>
        </label>

        <p v-if="!product.add_ons || product.add_ons.length === 0" class="no-addons">
          No add-ons available
        </p>
      </div>

      <div class="divider" />

      <!-- Footer -->
      <div class="modal-footer">
        <div class="total-summary">
          <span class="total-label">Selected total:</span>
          <span class="total-breakdown">
            ₱{{ Number(product.price).toFixed(2) }}
            <template v-if="addOnTotal > 0"> + ₱{{ addOnTotal.toFixed(2) }}</template>
            = <strong>₱{{ totalPrice.toFixed(2) }}</strong>
          </span>
        </div>
        <button class="add-btn" @click="handleConfirm">Add to Cart</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['confirm', 'close'])

// ── State ──────────────────────────────────────────────────────────────────────
const selectedAddOnIds = ref(new Set())

// ── Toggle ─────────────────────────────────────────────────────────────────────
function toggleAddOn(id) {
  const next = new Set(selectedAddOnIds.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  selectedAddOnIds.value = next
}

// ── Computed ───────────────────────────────────────────────────────────────────
const selectedAddOns = computed(() =>
  (props.product.add_ons || []).filter(ao => selectedAddOnIds.value.has(ao.id))
)

const addOnTotal = computed(() =>
  selectedAddOns.value.reduce((sum, ao) => sum + Number(ao.price), 0)
)

const totalPrice = computed(() => Number(props.product.price) + addOnTotal.value)

// ── Actions ────────────────────────────────────────────────────────────────────
function handleConfirm() {
  emit('confirm', { product: props.product, selectedAddOns: selectedAddOns.value })
}

// ── Keyboard ───────────────────────────────────────────────────────────────────
function onKeyUp(e) {
  if (e.key === 'Escape') emit('close')
}

onMounted(() => document.addEventListener('keyup', onKeyUp))
onUnmounted(() => document.removeEventListener('keyup', onKeyUp))
</script>

<style scoped>
.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  width: 100%;
  max-width: 500px;
  display: flex;
  flex-direction: column;
  gap: 0;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1.25rem 1.5rem 1rem;
  gap: 1rem;
}

.modal-title {
  margin: 0 0 0.25rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text);
}

.base-price {
  margin: 0;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-size: 1rem;
  cursor: pointer;
  min-width: var(--touch-min);
  min-height: var(--touch-min);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  flex-shrink: 0;
  transition: color 0.15s, background 0.15s;
}

.close-btn:hover {
  color: var(--color-danger);
  background: rgba(239, 68, 68, 0.1);
}

.divider {
  height: 1px;
  background: var(--color-border);
  margin: 0;
}

/* ── Add-ons ───────────────────────────────────────────────────────────────── */
.addons-section {
  padding: 1rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 320px;
  overflow-y: auto;
}

.section-label {
  margin: 0 0 0.5rem;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.addon-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  cursor: pointer;
  background: var(--color-surface-2);
  transition: border-color 0.15s, background 0.15s;
  user-select: none;
  min-height: var(--touch-min);
}

.addon-row:hover {
  border-color: var(--color-primary);
}

.addon-row.selected {
  border-color: var(--color-primary);
  background: rgba(232, 93, 4, 0.08);
}

.addon-checkbox {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  accent-color: var(--color-primary);
  cursor: pointer;
}

.addon-name {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-text);
}

.addon-price {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-primary);
}

.no-addons {
  margin: 0;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  text-align: center;
  padding: 1rem 0;
}

/* ── Footer ────────────────────────────────────────────────────────────────── */
.modal-footer {
  padding: 1rem 1.5rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.total-summary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.total-label {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.total-breakdown {
  font-size: 0.9rem;
  color: var(--color-text);
}

.total-breakdown strong {
  color: var(--color-primary);
  font-size: 1rem;
}

.add-btn {
  width: 100%;
  padding: 0.9rem;
  background: var(--color-primary);
  color: white;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  min-height: var(--touch-min);
  transition: background 0.15s, transform 0.1s;
}

.add-btn:hover {
  background: var(--color-primary-dark);
}

.add-btn:active {
  transform: scale(0.98);
}
</style>
