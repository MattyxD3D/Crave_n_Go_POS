import { ref, readonly } from 'vue'
import db from '@/db/index'
import { apiPost } from './api'

// ── Reactive sync state — import this anywhere to watch sync status ──────────
const _pending  = ref(0)   // number of orders waiting to sync
const _syncing  = ref(false)
const _lastSync = ref(null) // { synced: n, errors: n, at: Date }

export const syncState = readonly({
  pending:  _pending,
  syncing:  _syncing,
  lastSync: _lastSync,
})

// ── Read how many orders are in the queue ────────────────────────────────────
export async function refreshPendingCount() {
  _pending.value = await db.offline_queue.where('synced').equals(0).count()
}

// ── Flush the queue to the server ────────────────────────────────────────────
export async function syncOfflineOrders() {
  if (_syncing.value) return   // already running

  await refreshPendingCount()
  if (_pending.value === 0) return

  _syncing.value = true

  try {
    const queued = await db.offline_queue.where('synced').equals(0).toArray()
    if (queued.length === 0) {
      _pending.value = 0
      return
    }

    // Shape each queued order for the /orders/sync endpoint
    const orders = queued.map(entry => ({
      offline_id:      entry.offline_id,
      order_type:      entry.order_type,
      payment_method:  entry.payment_method,
      amount_tendered: entry.amount_tendered,
      items:           entry.items.map(({ product_id, combo_id, quantity, add_on_ids }) => ({
        product_id, combo_id, quantity, add_on_ids
      })),
    }))

    const res = await apiPost('/orders/sync', { orders })
    const { synced, errors } = res.data.data

    // Build a set of offline_ids that had hard errors
    const errorIds = new Set(
      errors.map(e => e.offline_id).filter(Boolean)
    )

    // Mark everything that didn't error as synced
    for (const entry of queued) {
      if (!errorIds.has(entry.offline_id)) {
        await db.offline_queue.update(entry.id, { synced: 1 })
      }
    }

    _lastSync.value = {
      synced: synced.length,
      errors: errors.length,
      at: new Date(),
    }

    await refreshPendingCount()
    return _lastSync.value

  } catch {
    // Network error — leave queue intact, try again next time
  } finally {
    _syncing.value = false
  }
}

// ── Auto-sync whenever the browser comes online ──────────────────────────────
// Call startAutoSync() once at app startup (main.js or App.vue)
let _listening = false

export function startAutoSync() {
  if (_listening) return
  _listening = true

  // Try immediately on startup in case there's leftover queue from last session
  if (navigator.onLine) {
    syncOfflineOrders()
  } else {
    refreshPendingCount()
  }

  window.addEventListener('online', () => {
    syncOfflineOrders()
  })

  window.addEventListener('offline', () => {
    refreshPendingCount()
  })
}
