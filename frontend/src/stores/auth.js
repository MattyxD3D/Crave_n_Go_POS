import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('pos_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isCashier = computed(() => user.value?.role === 'cashier')

  async function login(email, password) {
    const res = await api.post('/auth/login', { email, password })
    const data = res.data.data
    token.value = data.token
    user.value = data.user
    localStorage.setItem('pos_token', data.token)
    localStorage.setItem('pos_user', JSON.stringify(data.user))
    return data.user
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('pos_token')
    localStorage.removeItem('pos_user')
  }

  return { token, user, isAuthenticated, isAdmin, isCashier, login, logout }
})
