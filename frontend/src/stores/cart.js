import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])  // { id (uuid), product_id|combo_id, name, unit_price, quantity, add_ons: [{id, name, price}], notes }
  const orderType = ref('dine_in')  // 'dine_in' | 'takeout'

  const total = computed(() => items.value.reduce((sum, item) => {
    const addOnTotal = (item.add_ons || []).reduce((s, a) => s + Number(a.price), 0)
    return sum + (Number(item.unit_price) + addOnTotal) * item.quantity
  }, 0))

  const itemCount = computed(() => items.value.reduce((s, i) => s + i.quantity, 0))

  function addItem(item) {
    items.value.push({
      ...item,
      id: crypto.randomUUID(),
      unit_price: Number(item.unit_price),
      add_ons: (item.add_ons || []).map(a => ({ ...a, price: Number(a.price) })),
    })
  }

  function removeItem(id) {
    items.value = items.value.filter(i => i.id !== id)
  }

  function updateQty(id, qty) {
    const item = items.value.find(i => i.id === id)
    if (item) item.quantity = Math.max(1, qty)
  }

  function clearCart() {
    items.value = []
  }

  return { items, orderType, total, itemCount, addItem, removeItem, updateQty, clearCart }
})
