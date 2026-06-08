<template>
  <div class="a-page">
    <div class="a-page-header">
      <h1 class="a-page-title">Products</h1>
      <button class="a-btn a-btn-primary" @click="openAdd">+ Add Product</button>
    </div>

    <div v-if="error" class="a-alert-error">{{ error }}</div>

    <div class="a-table-wrap">
      <div v-if="loading" class="a-table-loading">Loading…</div>
      <table v-else class="a-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Available</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="products.length === 0">
            <td colspan="6" class="empty-row">No products found.</td>
          </tr>
          <tr v-for="p in products" :key="p.id">
            <td class="td-name">{{ p.name }}</td>
            <td class="td-muted">{{ categoryName(p.category_id) }}</td>
            <td>₱{{ Number(p.price).toFixed(0) }}</td>
            <td :class="isLowStock(p) ? 'td-low' : ''">
              {{ p.stock_qty ?? '—' }}
              <span v-if="isLowStock(p)" class="a-badge a-badge-warning" style="margin-left:6px">Low</span>
            </td>
            <td>
              <span :class="p.is_available ? 'a-badge a-badge-success' : 'a-badge a-badge-muted'">
                {{ p.is_available ? 'Visible' : 'Hidden' }}
              </span>
            </td>
            <td class="td-actions">
              <button class="a-btn a-btn-secondary a-btn-sm" @click="openEdit(p)">Edit</button>
              <button class="a-btn a-btn-secondary a-btn-sm" @click="toggleAvailable(p)">
                {{ p.is_available ? 'Hide' : 'Show' }}
              </button>
              <button
                :class="p.is_active ? 'a-btn a-btn-danger a-btn-sm' : 'a-btn a-btn-secondary a-btn-sm'"
                @click="toggleActive(p)"
              >
                {{ p.is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="a-modal-overlay" @click.self="closeModal">
      <div class="a-modal-card">
        <div class="a-modal-accent"></div>
        <div class="a-modal-body">
          <div class="a-modal-header">
            <h3 class="a-modal-title">{{ editingProduct?.id ? 'Edit' : 'Add' }} Product</h3>
            <button class="a-btn-icon" @click="closeModal">✕</button>
          </div>
          <form @submit.prevent="saveProduct" class="a-form">
            <div class="a-form-row">
              <div class="a-form-group">
                <label class="a-form-label">Name <span class="a-required">*</span></label>
                <input v-model="form.name" type="text" required />
              </div>
              <div class="a-form-group">
                <label class="a-form-label">Category</label>
                <select v-model="form.category_id">
                  <option value="">— None —</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>

            <div class="a-form-row">
              <div class="a-form-group">
                <label class="a-form-label">Price (₱)</label>
                <input v-model.number="form.price" type="number" step="1" min="0" />
              </div>
              <div class="a-form-group">
                <label class="a-form-label">Stock Qty</label>
                <input v-model.number="form.stock_qty" type="number" min="0" />
              </div>
            </div>

            <div class="a-form-row">
              <div class="a-form-group">
                <label class="a-form-label">Low Stock Threshold</label>
                <input v-model.number="form.low_stock_threshold" type="number" min="0" />
              </div>
            </div>

            <div class="a-form-group">
              <label class="a-form-label">Description</label>
              <textarea v-model="form.description" rows="2" style="resize:vertical"></textarea>
            </div>

            <div class="a-form-row" style="padding: 0.25rem 0 0.5rem; gap: 1.5rem;">
              <label class="a-check-label">
                <input v-model="form.is_available" type="checkbox" class="a-form-checkbox" />
                <span>Available on menu</span>
              </label>
              <label class="a-check-label">
                <input v-model="form.is_active" type="checkbox" class="a-form-checkbox" />
                <span>Active</span>
              </label>
            </div>

            <div class="a-form-group" v-if="addOns.length > 0">
              <label class="a-form-label">Add-ons</label>
              <div class="a-addons-grid">
                <label v-for="ao in addOns" :key="ao.id" class="a-check-label" style="font-size:0.85rem">
                  <input type="checkbox" class="a-form-checkbox" :value="ao.id" v-model="form.add_on_ids" />
                  <span>{{ ao.name }} <span class="a-addon-price">+₱{{ Number(ao.price).toFixed(0) }}</span></span>
                </label>
              </div>
            </div>

            <div v-if="saveError" class="a-alert-error">{{ saveError }}</div>
            <div class="a-modal-footer">
              <button type="button" class="a-btn a-btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="a-btn a-btn-primary" :disabled="saving">
                {{ saving ? 'Saving…' : 'Save Product' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiGet, apiPost, apiPut, apiPatch } from '@/services/api'
import { seedProducts, seedCategories } from '@/db/seed'

const products = ref([])
const categories = ref([])
const addOns = ref([])
const loading = ref(false)
const error = ref('')
const saving = ref(false)
const saveError = ref('')
const showModal = ref(false)
const editingProduct = ref(null)

const defaultForm = () => ({
  name: '',
  category_id: '',
  price: 0,
  description: '',
  stock_qty: 0,
  low_stock_threshold: 5,
  is_available: true,
  is_active: true,
  add_on_ids: [],
})
const form = ref(defaultForm())

async function fetchAll() {
  loading.value = true
  error.value = ''
  try {
    const [pRes, cRes, aRes] = await Promise.all([
      apiGet('/products'),
      apiGet('/categories'),
      apiGet('/add-ons'),
    ])
    products.value = pRes.data?.data ?? pRes.data ?? []
    categories.value = cRes.data?.data ?? cRes.data ?? []
    addOns.value = aRes.data?.data ?? aRes.data ?? []
  } catch {
    products.value = seedProducts
    categories.value = seedCategories
    addOns.value = []
  } finally {
    loading.value = false
  }
}

function categoryName(id) {
  return categories.value.find(c => c.id === id)?.name || '—'
}

function isLowStock(p) {
  if (p.stock_qty == null || p.low_stock_threshold == null) return false
  return p.stock_qty <= p.low_stock_threshold
}

function openAdd() {
  editingProduct.value = {}
  form.value = defaultForm()
  saveError.value = ''
  showModal.value = true
}

function openEdit(p) {
  editingProduct.value = p
  form.value = {
    name: p.name,
    category_id: p.category_id ?? '',
    price: p.price ?? 0,
    description: p.description ?? '',
    stock_qty: p.stock_qty ?? 0,
    low_stock_threshold: p.low_stock_threshold ?? 5,
    is_available: !!p.is_available,
    is_active: !!p.is_active,
    add_on_ids: p.add_ons?.map(a => a.id) ?? p.add_on_ids ?? [],
  }
  saveError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingProduct.value = null
}

async function saveProduct() {
  saving.value = true
  saveError.value = ''
  try {
    if (editingProduct.value?.id) {
      await apiPut(`/products/${editingProduct.value.id}`, form.value)
    } else {
      await apiPost('/products', form.value)
    }
    closeModal()
    await fetchAll()
  } catch (e) {
    saveError.value = e?.response?.data?.message || 'Failed to save product.'
  } finally {
    saving.value = false
  }
}

async function toggleAvailable(p) {
  try {
    await apiPatch(`/products/${p.id}/toggle`, {})
    await fetchAll()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to toggle availability.'
  }
}

async function toggleActive(p) {
  try {
    await apiPut(`/products/${p.id}`, { ...p, is_active: !p.is_active })
    await fetchAll()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to update product.'
  }
}

onMounted(fetchAll)
</script>

<style scoped>
.td-low { color: var(--color-warning); font-weight: 700; }
.a-modal-card { max-width: 580px; }
</style>
