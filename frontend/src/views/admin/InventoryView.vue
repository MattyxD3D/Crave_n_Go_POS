<template>
  <div class="a-page">
    <div class="a-page-header">
      <h1 class="a-page-title">Inventory</h1>
    </div>

    <div v-if="error" class="a-alert-error">{{ error }}</div>

    <div class="a-stats-row">
      <div class="a-stat-card primary">
        <div class="a-stat-label">Total Products</div>
        <div class="a-stat-value">{{ inventory.length }}</div>
      </div>
      <div class="a-stat-card warning">
        <div class="a-stat-label">Low Stock</div>
        <div class="a-stat-value">{{ lowStockCount }}</div>
      </div>
      <div class="a-stat-card danger">
        <div class="a-stat-label">Out of Stock</div>
        <div class="a-stat-value">{{ outOfStockCount }}</div>
      </div>
    </div>

    <div class="a-table-wrap">
      <div v-if="loading" class="a-table-loading">Loading…</div>
      <table v-else class="a-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Threshold</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="inventory.length === 0">
            <td colspan="6" class="empty-row">No products found.</td>
          </tr>
          <tr
            v-for="item in inventory"
            :key="item.id"
            :class="{ 'row-out': item.stock_qty === 0, 'row-low': item.is_low_stock && item.stock_qty > 0 }"
          >
            <td class="td-name">{{ item.name }}</td>
            <td class="td-muted">{{ item.category }}</td>
            <td><span :class="stockClass(item)">{{ item.stock_qty }}</span></td>
            <td class="td-muted">{{ item.low_stock_threshold }}</td>
            <td>
              <span v-if="item.stock_qty === 0"    class="a-badge a-badge-danger">Out of Stock</span>
              <span v-else-if="item.is_low_stock"  class="a-badge a-badge-warning">Low Stock</span>
              <span v-else                          class="a-badge a-badge-success">OK</span>
            </td>
            <td class="td-actions">
              <button class="a-btn a-btn-secondary a-btn-sm" @click="openAdjust(item)">Adjust</button>
              <button class="a-btn a-btn-secondary a-btn-sm" @click="openLogs(item)">Logs</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Adjust Modal -->
    <div v-if="adjustModal.show" class="a-modal-overlay" @click.self="closeAdjust">
      <div class="a-modal-card">
        <div class="a-modal-accent"></div>
        <div class="a-modal-body">
          <div class="a-modal-header">
            <h3 class="a-modal-title">Adjust Stock</h3>
            <button class="a-btn-icon" @click="closeAdjust">✕</button>
          </div>
          <p class="product-name-label">{{ adjustModal.product?.name }}</p>
          <div class="current-stock-box">
            Current stock: <strong>{{ adjustModal.product?.stock_qty }}</strong>
          </div>
          <form @submit.prevent="saveAdjust" class="a-form">
            <div class="a-form-group">
              <label class="a-form-label">Change Qty <span class="a-required">*</span></label>
              <input
                v-model.number="adjustForm.change_qty"
                type="number"
                placeholder="e.g. +50 to add, -5 to remove"
                required
              />
              <div class="a-form-hint">
                New stock will be: <strong>{{ Math.max(0, (adjustModal.product?.stock_qty ?? 0) + (adjustForm.change_qty || 0)) }}</strong>
              </div>
            </div>
            <div class="a-form-group">
              <label class="a-form-label">Reason <span class="a-required">*</span></label>
              <input
                v-model="adjustForm.reason"
                type="text"
                placeholder="e.g. Restock, Damaged, Correction"
                required
              />
            </div>
            <div v-if="adjustModal.error" class="a-alert-error">{{ adjustModal.error }}</div>
            <div class="a-modal-footer">
              <button type="button" class="a-btn a-btn-secondary" @click="closeAdjust">Cancel</button>
              <button type="submit" class="a-btn a-btn-primary" :disabled="adjustModal.saving">
                {{ adjustModal.saving ? 'Saving…' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Logs Modal -->
    <div v-if="logsModal.show" class="a-modal-overlay" @click.self="closeLogs">
      <div class="a-modal-card wide">
        <div class="a-modal-accent"></div>
        <div class="a-modal-body">
          <div class="a-modal-header">
            <h3 class="a-modal-title">Inventory Logs — {{ logsModal.product?.name }}</h3>
            <button class="a-btn-icon" @click="closeLogs">✕</button>
          </div>
          <div v-if="logsModal.loading" class="a-table-loading">Loading…</div>
          <div v-else-if="logsModal.logs.length === 0" class="empty-row">No logs yet.</div>
          <table v-else class="a-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Change</th>
                <th>Before</th>
                <th>After</th>
                <th>Reason</th>
                <th>By</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logsModal.logs" :key="log.id">
                <td class="td-muted">{{ formatDate(log.created_at) }}</td>
                <td :class="log.change_qty >= 0 ? 'td-pos' : 'td-neg'">
                  {{ log.change_qty >= 0 ? '+' : '' }}{{ log.change_qty }}
                </td>
                <td class="td-muted">{{ log.previous_qty }}</td>
                <td>{{ log.new_qty }}</td>
                <td>{{ log.reason }}</td>
                <td class="td-muted">{{ log.user?.name ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiGet, apiPost } from '@/services/api'
import { seedProducts, seedCategories } from '@/db/seed'

const inventory = ref([])
const loading = ref(false)
const error = ref('')

const adjustModal = ref({ show: false, product: null, saving: false, error: '' })
const adjustForm = ref({ change_qty: 0, reason: '' })

const logsModal = ref({ show: false, product: null, loading: false, logs: [] })

const lowStockCount = computed(() => inventory.value.filter(i => i.is_low_stock && i.stock_qty > 0).length)
const outOfStockCount = computed(() => inventory.value.filter(i => i.stock_qty === 0).length)

function stockClass(item) {
  if (item.stock_qty === 0) return 'stock-out'
  if (item.is_low_stock) return 'stock-low'
  return 'stock-ok'
}

async function fetchInventory() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiGet('/inventory')
    inventory.value = res.data?.data ?? []
  } catch {
    const catMap = Object.fromEntries(seedCategories.map(c => [c.id, c.name]))
    inventory.value = seedProducts.map(p => ({
      ...p,
      category: catMap[p.category_id] ?? '—',
      low_stock_threshold: 5,
      is_low_stock: p.stock_qty <= 5,
    }))
  } finally {
    loading.value = false
  }
}

function openAdjust(item) {
  adjustModal.value = { show: true, product: item, saving: false, error: '' }
  adjustForm.value = { change_qty: 0, reason: '' }
}

function closeAdjust() {
  adjustModal.value.show = false
}

async function saveAdjust() {
  adjustModal.value.saving = true
  adjustModal.value.error = ''
  try {
    await apiPost(`/inventory/${adjustModal.value.product.id}/adjust`, adjustForm.value)
    closeAdjust()
    await fetchInventory()
  } catch (e) {
    adjustModal.value.error = e?.response?.data?.message || 'Failed to adjust stock.'
  } finally {
    adjustModal.value.saving = false
  }
}

async function openLogs(item) {
  logsModal.value = { show: true, product: item, loading: true, logs: [] }
  try {
    const res = await apiGet(`/inventory/${item.id}/logs`)
    logsModal.value.logs = res.data?.data?.data ?? res.data?.data ?? []
  } catch {
    logsModal.value.logs = []
  } finally {
    logsModal.value.loading = false
  }
}

function closeLogs() {
  logsModal.value.show = false
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-PH', { dateStyle: 'short', timeStyle: 'short' })
}

onMounted(fetchInventory)
</script>

<style scoped>
.row-low td { background: rgba(180,83,9,0.05); }
.row-out td { background: rgba(185,28,28,0.05); }
.stock-ok  { color: var(--color-success); font-weight: 700; }
.stock-low { color: var(--color-warning); font-weight: 700; }
.stock-out { color: var(--color-danger);  font-weight: 700; }
.td-pos { color: var(--color-success); font-weight: 700; }
.td-neg { color: var(--color-danger);  font-weight: 700; }
.product-name-label { font-weight: 700; font-size: 1rem; color: var(--color-text); margin-bottom: 0.75rem; }
.current-stock-box {
  background: var(--color-surface-2);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.6rem 1rem;
  margin-bottom: 1.25rem;
  font-size: 0.9rem;
  color: var(--color-text-muted);
}
.a-modal-card.wide { max-width: 680px; }
</style>
