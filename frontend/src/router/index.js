import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', component: () => import('@/views/LandingView.vue'), meta: { public: true, skipAuthRedirect: true } },
  { path: '/login', component: () => import('@/views/LoginView.vue'), meta: { public: true } },
  { path: '/cashier', component: () => import('@/views/cashier/OrderView.vue'), meta: { requiresAuth: true, role: 'cashier' } },
  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', redirect: '/admin/reports' },
      { path: 'reports', component: () => import('@/views/admin/ReportsView.vue') },
      { path: 'inventory', component: () => import('@/views/admin/InventoryView.vue') },
      { path: 'products', component: () => import('@/views/admin/ProductListView.vue') },
      { path: 'categories', component: () => import('@/views/admin/CategoryListView.vue') },
      { path: 'combos', component: () => import('@/views/admin/ComboListView.vue') },
      { path: 'add-ons', component: () => import('@/views/admin/AddOnListView.vue') },
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => next())

export default router
