<template>
  <div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-checker"></div>

      <div class="sidebar-brand">
        <div class="brand-badge">🍗</div>
        <div>
          <div class="brand-name">Crave N' Go</div>
          <div class="brand-sub">Admin Panel</div>
        </div>
      </div>

      <div class="sidebar-divider" />

      <nav class="sidebar-nav">
        <RouterLink to="/admin/reports"   active-class="nav-active" class="nav-link"><span class="nav-icon">📊</span> Reports</RouterLink>
        <RouterLink to="/admin/inventory" active-class="nav-active" class="nav-link"><span class="nav-icon">📦</span> Inventory</RouterLink>
        <RouterLink to="/admin/products"  active-class="nav-active" class="nav-link"><span class="nav-icon">🍗</span> Products</RouterLink>
        <RouterLink to="/admin/categories" active-class="nav-active" class="nav-link"><span class="nav-icon">📂</span> Categories</RouterLink>
        <RouterLink to="/admin/add-ons"   active-class="nav-active" class="nav-link"><span class="nav-icon">➕</span> Add-ons</RouterLink>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-divider" />
        <div class="user-row">
          <span class="user-avatar">👤</span>
          <span class="user-name">{{ authStore.user?.name || 'Admin' }}</span>
        </div>
        <button class="btn-signout" @click="handleLogout">Sign Out</button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
      <RouterView />
    </main>

  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  background: var(--color-bg);
}

/* ── Sidebar ── */
.sidebar {
  width: 230px;
  flex-shrink: 0;
  background: var(--color-surface);
  height: 100vh;
  display: flex;
  flex-direction: column;
  border-right: 1.5px solid var(--color-border);
  box-shadow: 2px 0 8px rgba(28,25,23,0.06);
}

.sidebar-checker {
  height: 8px;
  background-image: repeating-conic-gradient(
    var(--color-check-a) 0% 25%,
    var(--color-check-b) 0% 50%
  ) 0 0 / 8px 8px;
  flex-shrink: 0;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.1rem 1rem;
}

.brand-badge {
  width: 38px;
  height: 38px;
  background: var(--color-primary);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
  box-shadow: 0 3px 10px rgba(185,28,28,0.28);
}

.brand-name {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.05rem;
  color: var(--color-primary);
  line-height: 1.1;
}

.brand-sub {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--color-text-muted);
  margin-top: 1px;
}

.sidebar-divider {
  height: 1.5px;
  background: var(--color-border);
  margin: 0 0.75rem;
}

/* ── Nav links ── */
.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 0.75rem 0.5rem;
  gap: 0.15rem;
  overflow-y: auto;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.6rem 0.75rem;
  border-radius: var(--radius-sm);
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  min-height: var(--touch-min);
  transition: background 0.15s, color 0.15s;
}

.nav-link:hover {
  background: var(--color-surface-2);
  color: var(--color-text);
}

.nav-link.nav-active {
  background: var(--color-primary);
  color: #fff;
  box-shadow: 0 3px 10px rgba(185,28,28,0.25);
}

.nav-icon {
  font-size: 1rem;
  width: 1.25rem;
  text-align: center;
  flex-shrink: 0;
}

/* ── Footer ── */
.sidebar-footer {
  padding: 0.75rem 0.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0 0.5rem;
}

.user-avatar { font-size: 1.1rem; }

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.btn-signout {
  width: 100%;
  background: transparent;
  color: var(--color-text-muted);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.4rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  min-height: 40px;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.btn-signout:hover {
  background: var(--color-danger-bg);
  color: var(--color-danger);
  border-color: var(--color-danger);
}

.admin-main {
  flex: 1;
  overflow-y: auto;
  background: var(--color-bg);
  min-width: 0;
}

/* ── Mobile Optimization ─────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .admin-layout {
    flex-direction: column;
  }
  .sidebar {
    width: 100%;
    height: auto;
    border-right: none;
    border-bottom: 1.5px solid var(--color-border);
    flex-shrink: 0;
  }
  .sidebar-nav {
    flex-direction: row;
    overflow-x: auto;
    padding: 0.5rem;
    scrollbar-width: none;
  }
  .sidebar-nav::-webkit-scrollbar {
    display: none;
  }
  .nav-link {
    white-space: nowrap;
  }
  .sidebar-footer {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    border-top: 1px solid var(--color-border);
  }
  .btn-signout {
    width: auto;
    min-height: 32px;
  }
}
</style>
