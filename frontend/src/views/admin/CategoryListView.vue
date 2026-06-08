<template>
  <div class="a-page">
    <div class="a-page-header">
      <h1 class="a-page-title">Categories</h1>
      <button class="a-btn a-btn-primary" @click="openAdd">+ Add Category</button>
    </div>

    <div v-if="error" class="a-alert-error">{{ error }}</div>

    <div class="a-table-wrap">
      <div v-if="loading" class="a-table-loading">Loading…</div>
      <table v-else class="a-table">
        <thead>
          <tr>
            <th>Icon</th>
            <th>Name</th>
            <th>Sort Order</th>
            <th>Products</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="categories.length === 0">
            <td colspan="6" class="empty-row">No categories found.</td>
          </tr>
          <tr v-for="cat in categories" :key="cat.id">
            <td style="font-size:1.4rem; width:48px">{{ cat.icon || '—' }}</td>
            <td class="td-name">{{ cat.name }}</td>
            <td class="td-muted">{{ cat.sort_order ?? 0 }}</td>
            <td class="td-muted">{{ cat.products_count ?? 0 }}</td>
            <td>
              <span :class="cat.is_active ? 'a-badge a-badge-success' : 'a-badge a-badge-muted'">
                {{ cat.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="td-actions">
              <button class="a-btn a-btn-secondary a-btn-sm" @click="openEdit(cat)">Edit</button>
              <button
                :class="cat.is_active ? 'a-btn a-btn-danger a-btn-sm' : 'a-btn a-btn-secondary a-btn-sm'"
                @click="toggleActive(cat)"
              >
                {{ cat.is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="a-modal-overlay" @click.self="closeModal">
      <div class="a-modal-card">
        <div class="a-modal-accent"></div>
        <div class="a-modal-body">
          <div class="a-modal-header">
            <h3 class="a-modal-title">{{ editingCategory?.id ? 'Edit' : 'Add' }} Category</h3>
            <button class="a-btn-icon" @click="closeModal">✕</button>
          </div>
          <form @submit.prevent="saveCategory" class="a-form">
            <div class="a-form-group">
              <label class="a-form-label">Name <span class="a-required">*</span></label>
              <input v-model="form.name" type="text" placeholder="e.g. Boneless Chicken" required />
            </div>
            <div class="a-form-group">
              <label class="a-form-label">Icon (emoji)</label>
              <input v-model="form.icon" type="text" placeholder="e.g. 🍗" />
            </div>
            <div class="a-form-group">
              <label class="a-form-label">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" />
            </div>
            <label class="a-check-label" style="margin-bottom:1rem">
              <input v-model="form.is_active" type="checkbox" class="a-form-checkbox" />
              <span>Active</span>
            </label>
            <div v-if="saveError" class="a-alert-error">{{ saveError }}</div>
            <div class="a-modal-footer">
              <button type="button" class="a-btn a-btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="a-btn a-btn-primary" :disabled="saving">
                {{ saving ? 'Saving…' : 'Save' }}
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
import { apiGet, apiPost, apiPut } from '@/services/api'
import { seedCategories } from '@/db/seed'

const categories = ref([])
const loading = ref(false)
const error = ref('')
const saving = ref(false)
const saveError = ref('')

const showModal = ref(false)
const editingCategory = ref(null)
const form = ref({ name: '', icon: '', sort_order: 0, is_active: true })

async function fetchCategories() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiGet('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } catch {
    categories.value = seedCategories
  } finally {
    loading.value = false
  }
}

function openAdd() {
  editingCategory.value = {}
  form.value = { name: '', icon: '', sort_order: 0, is_active: true }
  saveError.value = ''
  showModal.value = true
}

function openEdit(cat) {
  editingCategory.value = cat
  form.value = {
    name: cat.name,
    icon: cat.icon || '',
    sort_order: cat.sort_order ?? 0,
    is_active: !!cat.is_active,
  }
  saveError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingCategory.value = null
}

async function saveCategory() {
  saving.value = true
  saveError.value = ''
  try {
    if (editingCategory.value?.id) {
      await apiPut(`/categories/${editingCategory.value.id}`, form.value)
    } else {
      await apiPost('/categories', form.value)
    }
    closeModal()
    await fetchCategories()
  } catch (e) {
    saveError.value = e?.response?.data?.message || 'Failed to save category.'
  } finally {
    saving.value = false
  }
}

async function toggleActive(cat) {
  try {
    await apiPut(`/categories/${cat.id}`, { ...cat, is_active: !cat.is_active })
    await fetchCategories()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to update category.'
  }
}

onMounted(fetchCategories)
</script>

<style scoped>
/* No view-specific overrides needed */
</style>
