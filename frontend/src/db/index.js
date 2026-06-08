import Dexie from 'dexie'

const db = new Dexie('CraveNGoPOS')

// Version 1 — server-side tables use plain `id` (not `++id`) so
// bulkPut with server-assigned IDs works correctly.
db.version(1).stores({
  categories:    'id, name, sort_order, is_active',
  products:      'id, category_id, name, price, stock_qty, is_available, is_active',
  combos:        'id, name, price, is_active',
  add_ons:       'id, name, price, is_active',
  orders:        '++id, order_number, status, synced, created_at',
  offline_queue: '++id, offline_id, created_at, synced',
})

export default db
