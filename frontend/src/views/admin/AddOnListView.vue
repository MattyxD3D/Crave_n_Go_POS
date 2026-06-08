<template>
  <div class="page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">Add-ons</h1>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert-error">{{ error }}</div>

    <div class="table-container">
      <div v-if="loading" class="table-loading">Loading…</div>
      <table v-else>
        <thead>
          <tr>
            <th>Name</th>
            <th>Price (₱)</th>
            <th>Active</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- New row -->
          <tr class="new-row">
            <td>
              <input
                v-model="newRow.name"
                type="text"
                class="inline-input"
                placeholder="Add-on name…"
              />
            </td>
            <td>
              <input
                v-model.number="newRow.price"
                type="number"
                step="0.01"
                min="0"
                class="inline-input inline-input-sm"
                placeholder="0.00"
              />
            </td>
            <td>
              <input v-model="newRow.is_active" type="checkbox" class="form-checkbox" />
            </td>
            <td>
              <button
                class="btn-primary btn-sm"
                :disabled="savingNew || !newRow.name.trim()"
                @click="saveNew"
              >
                {{ savingNew ? '…' : '+ Add' }}
              </button>
            </td>
          </tr>

          <tr v-if="addOns.length === 0">
            <td colspan="4" class="empty-row">No add-ons yet. Use the row above to add one.</td>
          </tr>

          <!-- Existing rows -->
          <tr v-for="ao in addOns" :key="ao.id">
            <td>
              <input
                v-model="ao._name"
                type="text"
                class="inline-input"
              />
            </td>
            <td>
              <input
                v-model.number="ao._price"
                type="number"
                step="0.01"
                min="0"
                class="inline-input inline-input-sm"
              />
            </td>
            <td>
              <input v-model="ao._is_active" type="checkbox" class="form-checkbox" />
            </td>
            <td class="td-actions">
              <button
                class="btn-primary btn-sm"
                :disabled="ao._saving"
                @click="saveExisting(ao)"
              >
                {{ ao._saving ? '…' : 'Save' }}
              </button>
              <button
                class="btn-danger btn-sm"
                :disabled="ao._saving"
                @click="deactivate(ao)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiGet, apiPost, apiPut } from '@/services/api'

const addOns = ref([])
const loading = ref(false)
const error = ref('')
const savingNew = ref(false)

const newRow = ref({ name: '', price: 0, is_active: true })

function normalize(ao) {
  return {
    ...ao,
    _name: ao.name,
    _price: ao.price ?? 0,
    _is_active: !!ao.is_active,
    _saving: false,
  }
}

async function fetchAddOns() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiGet('/add-ons')
    addOns.value = (res.data?.data ?? res.data ?? []).map(normalize)
  } catch {
    addOns.value = []
  } finally {
    loading.value = false
  }
}

async function saveNew() {
  if (!newRow.value.name.trim()) return
  savingNew.value = true
  error.value = ''
  try {
    await apiPost('/add-ons', {
      name: newRow.value.name.trim(),
      price: newRow.value.price,
      is_active: newRow.value.is_active,
    })
    newRow.value = { name: '', price: 0, is_active: true }
    await fetchAddOns()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to create add-on.'
  } finally {
    savingNew.value = false
  }
}

async function saveExisting(ao) {
  ao._saving = true
  error.value = ''
  try {
    await apiPut(`/add-ons/${ao.id}`, {
      name: ao._name,
      price: ao._price,
      is_active: ao._is_active,
    })
    ao.name = ao._name
    ao.price = ao._price
    ao.is_active = ao._is_active
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to save add-on.'
  } finally {
    ao._saving = false
  }
}

async function deactivate(ao) {
  ao._saving = true
  error.value = ''
  try {
    await apiPut(`/add-ons/${ao.id}`, {
      name: ao.name,
      price: ao.price,
      is_active: false,
    })
    await fetchAddOns()
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to deactivate add-on.'
    ao._saving = false
  }
}

onMounted(fetchAddOns)
</script>

<style scoped>
.page { padding: 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin: 0; }

.table-container { background: var(--color-surface); border-radius: var(--radius); border: 1px solid var(--color-border); overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
th { background: var(--color-surface-2); padding: 0.75rem 1rem; text-align: left; font-size: 0.875rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); }
td { padding: 0.625rem 1rem; border-bottom: 1px solid var(--color-border); color: var(--color-text); font-size: 0.9375rem; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--color-surface-2); }

.new-row td { background: rgba(232, 93, 4, 0.04); }
.td-actions { display: flex; gap: 0.5rem; }
.empty-row { text-align: center; color: var(--color-text-muted); padding: 2rem 1rem; }
.table-loading { text-align: center; padding: 2rem; color: var(--color-text-muted); }

.inline-input { background: var(--color-surface-2); border: 1px solid var(--color-border); border-radius: 6px; padding: 0.375rem 0.625rem; color: var(--color-text); font-size: 0.875rem; width: 100%; box-sizing: border-box; }
.inline-input:focus { outline: none; border-color: var(--color-primary); }
.inline-input-sm { width: 100px; }
.form-checkbox { width: 1rem; height: 1rem; accent-color: var(--color-primary); cursor: pointer; }

.btn-primary { background: var(--color-primary); color: white; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.9375rem; min-height: var(--touch-min); }
.btn-secondary { background: var(--color-surface-2); color: var(--color-text); padding: 0.5rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); cursor: pointer; font-size: 0.875rem; }
.btn-danger { background: var(--color-danger); color: white; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.875rem; }
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.8125rem; min-height: 32px; }

.alert-error { background: rgba(239,68,68,0.1); border: 1px solid var(--color-danger); color: var(--color-danger); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
</style>
