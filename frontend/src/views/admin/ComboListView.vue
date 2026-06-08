<template>
  <div class="page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">Combos</h1>
      <button class="btn-primary" @click="openAdd">+ Add Combo</button>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert-error">{{ error }}</div>

    <!-- Table -->
    <div class="table-container">
      <div v-if="loading" class="table-loading">Loading…</div>
      <table v-else>
        <thead>
          <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Items</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="combos.length === 0">
            <td colspan="5" class="empty-row">No combos found.</td>
          </tr>
          <tr v-for="combo in combos" :key="combo.id">
            <td class="td-name">{{ combo.name }}</td>
            <td>₱{{ Number(combo.price).toFixed(2) }}</td>
            <td>{{ comboItemCount(combo) }}</td>
            <td>
              <span :class="combo.is_active ? 'badge badge-success' : 'badge badge-muted'">
                {{ combo.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="td-actions">
              <button class="btn-secondary btn-sm" @click="openEdit(combo)">Edit</button>
              <button
                :class="combo.is_active ? 'btn-danger btn-sm' : 'btn-secondary btn-sm'"
                @click="toggleActive(combo)"
              >
                {{ combo.is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ editingCombo?.id ? 'Edit' : 'Add' }} Combo</h3>
          <button class="btn-icon" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="saveCombo" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Name <span class="required">*</span></label>
              <input v-model="form.name" type="text" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Price (₱)</label>
              <input v-model.number="form.price" type="number" step="0.01" min="0" class="form-input" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea v-model="form.description" class="form-input form-textarea" rows="2"></textarea>
          </div>

          <div class="form-group form-check">
            <label class="check-label">
              <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
              <span>Active</span>
            </label>
          </div>

          <!-- Items -->
          <div class="form-group">
            <label class="form-label">Products in Combo</label>

            <!-- Search filter -->
            <input
              v-model="productSearch"
              type="text"
              class="form-input search-input"
              placeholder="Search products…"
            />

            <div class="products-list">
              <div
                v-for="p in filteredProducts"
                :key="p.id"
                class="product-row"
              >
                <label class="check-label product-check">
                  <input
                    type="checkbox"
                    class="form-checkbox"
                    :checked="isInCombo(p.id)"
                    @change="toggleComboItem(p.id)"
                  />
                  <span class="product-check-name">{{ p.name }}</span>
                </label>
                <div v-if="isInCombo(p.id)" class="qty-control">
                  <label class="qty-label">Qty:</label>
                  <input
                    type="number"
                    min="1"
                    class="qty-input"
                    :value="getComboItemQty(p.id)"
                    @input="setComboItemQty(p.id, $event.target.value)"
                  />
                </div>
              </div>
              <div v-if="filteredProducts.length === 0" class="empty-row">No products match.</div>
            </div>
          </div>

          <div v-if="saveError" class="alert-error">{{ saveError }}</div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Saving…' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiGet, apiPost, apiPut } from '@/services/api'
import { seedCombos, seedProducts } from '@/db/seed'

const combos = ref([])
const products = ref([])
const loading = ref(false)
const error = ref('')
const saving = ref(false)
const saveError = ref('')
const showModal = ref(false)
const editingCombo = ref(null)
const productSearch = ref('')

const defaultForm = () => ({
  name: '',
  price: 0,
  description: '',
  is_active: true,
  items: [], // [{product_id, quantity}]
})
const form = ref(defaultForm())

const filteredProducts = computed(() => {
  const q = productSearch.value.toLowerCase()
  if (!q) return products.value
  return products.value.filter(p => p.name.toLowerCase().includes(q))
})

function comboItemCount(combo) {
  return combo.items?.length ?? combo.products?.length ?? 0
}

function isInCombo(productId) {
  return form.value.items.some(i => i.product_id === productId)
}

function getComboItemQty(productId) {
  return form.value.items.find(i => i.product_id === productId)?.quantity ?? 1
}

function toggleComboItem(productId) {
  const idx = form.value.items.findIndex(i => i.product_id === productId)
  if (idx >= 0) {
    form.value.items.splice(idx, 1)
  } else {
    form.value.items.push({ product_id: productId, quantity: 1 })
  }
}

function setComboItemQty(productId, value) {
  const item = form.value.items.find(i => i.product_id === productId)
  if (item) {
    item.quantity = Math.max(1, parseInt(value) || 1)
  }
}

async function fetchAll() {
  loading.value = true
  error.value = ''
  try {
    const [cRes, pRes] = await Promise.all([
      apiGet('/combos'),
      apiGet('/products'),
    ])
    combos.value = cRes.data?.data ?? cRes.data ?? []
    products.value = pRes.data?.data ?? pRes.data ?? []
  } catch {
    combos.value = seedCombos
    products.value = seedProducts
  } finally {
    loading.value = false
  }
}

function openAdd() {
  editingCombo.value = {}
  form.value = defaultForm()
  productSearch.value = ''
  saveError.value = ''
  showModal.value = true
}

function openEdit(combo) {
  editingCombo.value = combo
  const items = combo.items?.map(i => ({
    product_id: i.product_id ?? i.id,
    quantity: i.quantity ?? i.pivot?.quantity ?? 1,
  })) ?? []
  form.value = {
    name: combo.name,
    price: combo.price ?? 0,
    description: combo.description ?? '',
    is_active: !!combo.is_active,
    items,
  }
  productSearch.value = ''
  saveError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingCombo.value = null
}

async function saveCombo() {
  saving.value = true
  saveError.value = ''
  try {
    const payload = { ...form.value }
    if (editingCombo.value?.id) {
      await apiPut(`/combos/${editingCombo.value.id}`, payload)
    } else {
      await apiPost('/combos', payload)
    }
    closeModal()
    await fetchAll()
  } catch (e) {
    saveError.value = e?.response?.data?.message || 'Failed to save combo.'
  } finally {
    saving.value = false
  }
}

async function toggleActive(combo) {
  try {
    await apiPut(`/combos/${combo.id}`, { ...combo, is_active: !combo.is_active })
    await fetchAll()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to update combo.'
  }
}

onMounted(fetchAll)
</script>

<style scoped>
.page { padding: 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin: 0; }

.table-container { background: var(--color-surface); border-radius: var(--radius); border: 1px solid var(--color-border); overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
th { background: var(--color-surface-2); padding: 0.75rem 1rem; text-align: left; font-size: 0.875rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); }
td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--color-border); color: var(--color-text); font-size: 0.9375rem; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--color-surface-2); }

.td-name { font-weight: 600; }
.td-actions { display: flex; gap: 0.5rem; }
.empty-row { text-align: center; color: var(--color-text-muted); padding: 2rem 1rem; }
.table-loading { text-align: center; padding: 2rem; color: var(--color-text-muted); }

.btn-primary { background: var(--color-primary); color: white; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.9375rem; min-height: var(--touch-min); }
.btn-secondary { background: var(--color-surface-2); color: var(--color-text); padding: 0.5rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); cursor: pointer; font-size: 0.875rem; }
.btn-danger { background: var(--color-danger); color: white; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.875rem; }
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.8125rem; min-height: 32px; }
.btn-icon { background: none; border: none; color: var(--color-text-muted); font-size: 1.25rem; cursor: pointer; padding: 0.25rem; line-height: 1; }
.btn-icon:hover { color: var(--color-text); }

.badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-success { background: rgba(34,197,94,0.15); color: var(--color-success); }
.badge-muted { background: rgba(136,136,170,0.15); color: var(--color-text-muted); }

.alert-error { background: rgba(239,68,68,0.1); border: 1px solid var(--color-danger); color: var(--color-danger); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal-card { background: var(--color-surface); border-radius: var(--radius); padding: 1.5rem; width: 560px; max-width: 95vw; border: 1px solid var(--color-border); max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-header h3 { margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--color-text); }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
.modal-form { display: flex; flex-direction: column; }

.form-row { display: flex; gap: 1rem; }
.form-row .form-group { flex: 1; }
.form-check { display: flex; align-items: center; }
.form-group { margin-bottom: 1rem; }
.form-label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--color-text-muted); margin-bottom: 0.375rem; }
.required { color: var(--color-danger); }
.form-input { width: 100%; background: var(--color-surface-2); border: 1px solid var(--color-border); border-radius: 8px; padding: 0.5rem 0.75rem; color: var(--color-text); font-size: 0.9375rem; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--color-primary); }
.form-textarea { resize: vertical; min-height: 64px; font-family: inherit; }
.check-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--color-text); font-size: 0.9375rem; }
.form-checkbox { width: 1rem; height: 1rem; accent-color: var(--color-primary); cursor: pointer; }

.search-input { margin-bottom: 0.5rem; }
.products-list { background: var(--color-surface-2); border: 1px solid var(--color-border); border-radius: 8px; max-height: 220px; overflow-y: auto; }
.product-row { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--color-border); }
.product-row:last-child { border-bottom: none; }
.product-check { flex: 1; }
.product-check-name { font-size: 0.875rem; }
.qty-control { display: flex; align-items: center; gap: 0.375rem; }
.qty-label { font-size: 0.75rem; color: var(--color-text-muted); }
.qty-input { width: 56px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 6px; padding: 0.25rem 0.5rem; color: var(--color-text); font-size: 0.875rem; text-align: center; }
</style>
