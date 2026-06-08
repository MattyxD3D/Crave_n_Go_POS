<template>
  <div class="a-page">
    <div class="a-page-header">
      <h1 class="a-page-title">Reports</h1>
      <div class="date-controls">
        <label class="date-label">Date</label>
        <input v-model="selectedDate" type="date" class="date-input" @change="fetchAll" />
      </div>
    </div>

    <div v-if="error" class="a-alert-error">{{ error }}</div>

    <div class="a-stats-row">
      <div class="a-stat-card primary">
        <div class="a-stat-label">Total Revenue</div>
        <div class="a-stat-value">₱{{ fmt(daily.total_revenue) }}</div>
      </div>
      <div class="a-stat-card success">
        <div class="a-stat-label">Total Orders</div>
        <div class="a-stat-value">{{ daily.total_orders ?? 0 }}</div>
      </div>
      <div class="a-stat-card warning">
        <div class="a-stat-label">Avg Order Value</div>
        <div class="a-stat-value">₱{{ fmt(daily.avg_order_value) }}</div>
      </div>
    </div>

    <div class="two-col">
      <!-- Hourly Breakdown -->
      <div class="panel">
        <h2 class="panel-title">Hourly Breakdown</h2>
        <div v-if="loadingHourly" class="panel-loading">Loading…</div>
        <div v-else class="bar-chart">
          <div
            v-for="slot in hourly"
            :key="slot.hour"
            class="bar-row"
          >
            <div class="bar-label">{{ slot.label.split(' - ')[0] }}</div>
            <div class="bar-track">
              <div
                class="bar-fill"
                :style="{ width: maxHourlyRevenue > 0 ? (slot.revenue / maxHourlyRevenue * 100) + '%' : '0%' }"
              ></div>
            </div>
            <div class="bar-value">
              <span v-if="slot.order_count > 0">₱{{ fmt(slot.revenue) }} ({{ slot.order_count }})</span>
              <span v-else class="bar-empty">—</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">Top Products</h2>
          <div class="date-range-row">
            <input v-model="rangeStart" type="date" class="date-input-sm" @change="fetchTopProducts" />
            <span class="range-sep">to</span>
            <input v-model="rangeEnd" type="date" class="date-input-sm" @change="fetchTopProducts" />
          </div>
        </div>
        <div v-if="loadingTop" class="panel-loading">Loading…</div>
        <div v-else-if="topProducts.length === 0" class="panel-empty">No sales in this period.</div>
        <table v-else class="top-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Product</th>
              <th>Qty Sold</th>
              <th>Revenue</th>
              <th>Orders</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in topProducts" :key="item.product_id">
              <td class="td-rank">{{ idx + 1 }}</td>
              <td class="td-name">{{ item.product_name }}</td>
              <td>{{ item.total_qty }}</td>
              <td class="td-revenue">₱{{ fmt(item.total_revenue) }}</td>
              <td class="td-muted">{{ item.order_count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiGet } from '@/services/api'
import db from '@/db/index'

const today = new Date().toISOString().slice(0, 10)
const selectedDate = ref(today)

const weekAgo = new Date(Date.now() - 6 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10)
const rangeStart = ref(weekAgo)
const rangeEnd = ref(today)

const daily = ref({ total_revenue: 0, total_orders: 0, avg_order_value: 0 })
const hourly = ref([])
const topProducts = ref([])

const loadingHourly = ref(false)
const loadingTop = ref(false)
const error = ref('')

const maxHourlyRevenue = computed(() => Math.max(...hourly.value.map(h => h.revenue), 1))

function fmt(n) {
  if (n == null || n === 0) return '0.00'
  return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function ordersForDate(orders, date) {
  return orders.filter(o => o.created_at?.slice(0, 10) === date)
}

function itemTotal(item) {
  const addOnTotal = (item.add_ons || []).reduce((s, a) => s + Number(a.price || 0), 0)
  return (Number(item.unit_price || 0) + addOnTotal) * item.quantity
}

async function localDaily(date) {
  const orders = await db.offline_queue.toArray()
  const dayOrders = ordersForDate(orders, date)
  const total_revenue = dayOrders.reduce((s, o) => s + (o.total ?? o.items.reduce((t, i) => t + itemTotal(i), 0)), 0)
  return {
    total_revenue,
    total_orders: dayOrders.length,
    avg_order_value: dayOrders.length ? total_revenue / dayOrders.length : 0,
  }
}

async function localHourly(date) {
  const orders = await db.offline_queue.toArray()
  const dayOrders = ordersForDate(orders, date)
  const slots = Array.from({ length: 24 }, (_, h) => ({
    hour: h,
    label: `${String(h).padStart(2, '0')}:00 - ${String(h + 1).padStart(2, '0')}:00`,
    revenue: 0,
    order_count: 0,
  }))
  for (const o of dayOrders) {
    const h = new Date(o.created_at).getHours()
    const rev = o.total ?? o.items.reduce((t, i) => t + itemTotal(i), 0)
    slots[h].revenue += rev
    slots[h].order_count += 1
  }
  return slots
}

async function localTopProducts(start, end) {
  const orders = await db.offline_queue.toArray()
  const rangeOrders = orders.filter(o => {
    const d = o.created_at?.slice(0, 10)
    return d >= start && d <= end
  })
  const map = {}
  for (const o of rangeOrders) {
    for (const item of o.items) {
      const key = item.product_id ?? `combo_${item.combo_id}`
      if (!map[key]) map[key] = { product_name: item.name || key, total_qty: 0, total_revenue: 0, order_count: 0 }
      map[key].total_qty += item.quantity
      map[key].total_revenue += itemTotal(item)
      map[key].order_count += 1
    }
  }
  return Object.values(map).sort((a, b) => b.total_revenue - a.total_revenue)
}

async function fetchDaily() {
  try {
    const res = await apiGet('/reports/daily', { date: selectedDate.value })
    daily.value = res.data?.data ?? {}
  } catch {
    daily.value = await localDaily(selectedDate.value)
  }
}

async function fetchHourly() {
  loadingHourly.value = true
  try {
    const res = await apiGet('/reports/hourly', { date: selectedDate.value })
    hourly.value = res.data?.data?.hourly ?? []
  } catch {
    hourly.value = await localHourly(selectedDate.value)
  } finally {
    loadingHourly.value = false
  }
}

async function fetchTopProducts() {
  loadingTop.value = true
  try {
    const res = await apiGet('/reports/top-products', {
      start_date: rangeStart.value,
      end_date: rangeEnd.value,
    })
    topProducts.value = res.data?.data?.top_products ?? []
  } catch {
    topProducts.value = await localTopProducts(rangeStart.value, rangeEnd.value)
  } finally {
    loadingTop.value = false
  }
}

async function fetchAll() {
  error.value = ''
  await Promise.all([fetchDaily(), fetchHourly()])
}

onMounted(() => {
  fetchAll()
  fetchTopProducts()
})
</script>

<style scoped>
.date-controls { display: flex; align-items: center; gap: 0.5rem; }
.date-label { font-size: 0.875rem; color: var(--color-text-muted); font-weight: 600; }
.date-range-row { display: flex; align-items: center; gap: 0.4rem; }
.range-sep { font-size: 0.8rem; color: var(--color-text-muted); }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

.panel {
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius);
  padding: 1.25rem 1.5rem;
  box-shadow: var(--shadow-card);
}
.panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; }
.panel-title { font-family: 'Fredoka One', system-ui, sans-serif; font-size: 1.1rem; font-weight: 400; color: var(--color-text); margin: 0 0 1rem 0; }
.panel-header .panel-title { margin: 0; }
.panel-loading { text-align: center; padding: 2rem; color: var(--color-text-muted); }
.panel-empty   { text-align: center; padding: 2rem; color: var(--color-text-muted); font-size: 0.875rem; }

.bar-chart { display: flex; flex-direction: column; gap: 0.3rem; }
.bar-row { display: grid; grid-template-columns: 2.5rem 1fr 8rem; align-items: center; gap: 0.5rem; font-size: 0.78rem; }
.bar-label { color: var(--color-text-muted); text-align: right; font-weight: 600; }
.bar-track { height: 12px; background: var(--color-surface-2); border-radius: 4px; overflow: hidden; border: 1px solid var(--color-border); }
.bar-fill { height: 100%; background: var(--color-primary); border-radius: 4px; transition: width 0.4s; }
.bar-value { color: var(--color-text); white-space: nowrap; font-size: 0.75rem; }
.bar-empty { color: var(--color-text-muted); }

.top-table { width: 100%; border-collapse: collapse; }
.top-table th { background: var(--color-surface-2); padding: 0.5rem 0.75rem; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-text-muted); border-bottom: 1.5px solid var(--color-border); }
.top-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--color-border); font-size: 0.875rem; color: var(--color-text); }
.top-table tr:last-child td { border-bottom: none; }
.top-table tbody tr:hover td { background: var(--color-surface-2); }
.td-rank    { color: var(--color-text-muted); font-weight: 700; }
.td-name    { font-weight: 600; }
.td-revenue { font-weight: 700; color: var(--color-primary); }
.td-muted   { color: var(--color-text-muted); }
</style>
