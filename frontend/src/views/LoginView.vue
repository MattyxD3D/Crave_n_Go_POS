<template>
  <div class="login-screen">

    <!-- Decorative checkered strips -->
    <div class="checker-top"></div>
    <div class="checker-bottom"></div>

    <div class="login-card">

      <!-- Card top accent bar -->
      <div class="card-accent"></div>

      <!-- Brand -->
      <div class="brand">
        <div class="brand-icon">🍗</div>
        <h1 class="brand-title">Crave N' Go</h1>
        <p class="brand-subtitle">Point of Sale</p>
      </div>

      <!-- Divider -->
      <div class="divider"></div>

      <!-- Role buttons -->
      <div class="role-buttons">
        <button class="btn-role btn-cashier" @click="signIn('cashier')">
          🧾 &nbsp;Cashier
        </button>

        <button class="btn-role btn-admin" @click="signIn('admin')">
          🔐 &nbsp;Admin
        </button>

        <router-link to="/" class="btn-home">← Back to Home</router-link>
      </div>
    </div>

    <p class="footer-note">Crave N' Go &copy; {{ year }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const year = computed(() => new Date().getFullYear())

function signIn(role) {
  router.push(role === 'admin' ? '/admin' : '/cashier')
}
</script>

<style scoped>
/* ── Layout ── */
.login-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: var(--color-bg);
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
}

/* ── Checkered decorative strips (top & bottom of screen) ── */
.checker-top,
.checker-bottom {
  position: absolute;
  left: 0;
  width: 100%;
  height: 18px;
  background-image: repeating-conic-gradient(
    var(--color-check-a) 0% 25%,
    var(--color-check-b) 0% 50%
  ) 0 0 / 18px 18px;
}
.checker-top { top: 0; }
.checker-bottom { bottom: 0; }

/* ── Card ── */
.login-card {
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  width: 100%;
  max-width: 420px;
  overflow: hidden;
}

/* ── Red accent bar at top of card ── */
.card-accent {
  height: 6px;
  background: var(--color-primary);
}

/* ── Brand / Logo area ── */
.brand {
  text-align: center;
  padding: 2rem 2.5rem 1.5rem;
}

.brand-icon {
  font-size: 3.5rem;
  line-height: 1;
  margin-bottom: 0.75rem;
  display: block;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.brand-title {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 2.25rem;
  font-weight: 400;
  color: var(--color-primary);
  letter-spacing: 0.01em;
  line-height: 1.1;
  margin-bottom: 0.25rem;
}

.brand-subtitle {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

/* ── Divider ── */
.divider {
  height: 1.5px;
  background: repeating-linear-gradient(
    90deg,
    var(--color-primary) 0px,
    var(--color-primary) 6px,
    transparent 6px,
    transparent 12px
  );
  margin: 0 2.5rem;
  opacity: 0.25;
}

/* ── Role buttons ── */
.role-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.75rem 2.5rem 2.5rem;
}


.btn-role {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  min-height: 56px;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: var(--radius-sm);
  letter-spacing: 0.03em;
  transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
}

.btn-cashier {
  background: var(--color-primary);
  color: #fff;
  box-shadow: 0 4px 12px rgba(185, 28, 28, 0.30);
}
.btn-cashier:hover:not(:disabled) {
  background: var(--color-primary-dark);
}

.btn-admin {
  background: var(--color-surface);
  color: var(--color-text);
  border: 1.5px solid var(--color-border);
  box-shadow: var(--shadow-card);
}
.btn-admin:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-role:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-home {
  display: block;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-decoration: none;
  margin-top: 0.25rem;
  padding: 0.4rem;
  transition: color 0.15s;
}
.btn-home:hover {
  color: var(--color-primary);
}

/* ── Footer ── */
.footer-note {
  margin-top: 1.5rem;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  letter-spacing: 0.03em;
}
</style>
