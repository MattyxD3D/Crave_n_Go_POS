<template>
  <div class="landing">

    <!-- Checkered strips -->
    <div class="checker-strip checker-top"></div>
    <div class="checker-strip checker-bottom"></div>

    <!-- Staff login link (top right corner) -->
    <router-link to="/login" class="staff-link">Staff Login</router-link>

    <!-- Main content -->
    <div class="landing-inner">

      <!-- Left: Branding + CTA -->
      <div class="hero-left">
        <div class="brand-badge">
          <span class="brand-icon">🍗</span>
        </div>
        <h1 class="brand-name">Crave N' Go</h1>
        <p class="tagline">Flavored chicken, crispy snacks,<br>and meals worth coming back for.</p>

        <button class="btn-order" @click="goOrder">
          🛒 &nbsp;Order Now
        </button>

        <p class="hint">Tap to start your order</p>
      </div>

      <!-- Right: Rotating featured item -->
      <div class="hero-right">
        <div class="feature-card">
          <div class="feature-label">Featured</div>

          <Transition name="slide-fade" mode="out-in">
            <div class="feature-item" :key="currentSlide">
              <div class="feature-emoji">{{ slides[currentSlide].emoji }}</div>
              <div class="feature-info">
                <p class="feature-name">{{ slides[currentSlide].name }}</p>
                <p class="feature-desc">{{ slides[currentSlide].desc }}</p>
                <p class="feature-price">{{ slides[currentSlide].price }}</p>
              </div>
            </div>
          </Transition>

          <!-- Dots -->
          <div class="slide-dots">
            <span
              v-for="(_, i) in slides"
              :key="i"
              class="dot"
              :class="{ active: i === currentSlide }"
              @click="currentSlide = i"
            ></span>
          </div>
        </div>

        <!-- Quick menu highlights -->
        <div class="highlights">
          <div class="highlight-item" v-for="item in highlights" :key="item.name">
            <span class="highlight-emoji">{{ item.emoji }}</span>
            <span class="highlight-name">{{ item.name }}</span>
            <span class="highlight-price">{{ item.price }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Bottom flavor strip -->
    <div class="flavor-strip">
      <span v-for="flavor in flavors" :key="flavor" class="flavor-chip">
        {{ flavor }}
      </span>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const slides = [
  {
    emoji: '🍗',
    name: 'Solo Sulit Meal',
    desc: '2 pcs Boneless Chicken · 1 Flavor of your choice',
    price: '₱95',
  },
  {
    emoji: '🔥',
    name: 'Quick Bite Meal',
    desc: '2 pcs Chicken Wings · 1 Flavor of your choice',
    price: '₱85',
  },
  {
    emoji: '🥩',
    name: 'Crispy Liempo',
    desc: 'Crispy pork belly — golden and satisfying',
    price: '₱120',
  },
  {
    emoji: '🥟',
    name: 'Siomai',
    desc: 'Steam or Fried · Pork, Beef, Chicken & more',
    price: 'from ₱50',
  },
]

const highlights = [
  { emoji: '🍗', name: 'Boneless Chicken', price: 'from ₱95' },
  { emoji: '🔥', name: 'Chicken Wings',    price: 'from ₱85' },
  { emoji: '🍟', name: 'French Fries',     price: 'from ₱35' },
  { emoji: '🥤', name: 'Beverages',        price: 'from ₱15' },
]

const flavors = [
  'Honey BBQ', 'Garlic Butter', 'Japanese BBQ',
  'Spicy BBQ', 'Yangnyeom', 'Fireball Buffalo',
]

const currentSlide = ref(0)
let timer = null

onMounted(() => {
  timer = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % slides.length
  }, 3500)
})

onUnmounted(() => clearInterval(timer))

function goOrder() {
  const user = JSON.parse(localStorage.getItem('pos_user') || 'null')
  const token = localStorage.getItem('pos_token')
  if (token && user?.role === 'cashier') {
    router.push('/cashier')
  } else if (token && user?.role === 'admin') {
    router.push('/admin')
  } else {
    router.push('/cashier')
  }
}
</script>

<style scoped>
/* ── Full screen layout ── */
.landing {
  min-height: 100vh;
  background: var(--color-bg);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

/* ── Checkered strips ── */
.checker-strip {
  position: absolute;
  left: 0;
  width: 100%;
  height: 18px;
  background-image: repeating-conic-gradient(
    var(--color-check-a) 0% 25%,
    var(--color-check-b) 0% 50%
  ) 0 0 / 18px 18px;
  z-index: 10;
}
.checker-top { top: 0; }
.checker-bottom { bottom: 0; }

/* ── Staff login link ── */
.staff-link {
  position: absolute;
  top: 26px;
  right: 1.5rem;
  z-index: 20;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-decoration: none;
  border: 1.5px solid var(--color-border);
  border-radius: 999px;
  padding: 0.3rem 0.9rem;
  background: var(--color-surface);
  transition: border-color 0.15s, color 0.15s;
}
.staff-link:hover {
  color: var(--color-primary);
  border-color: var(--color-primary);
}

/* ── Main content split ── */
.landing-inner {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3rem;
  padding: 4rem 3rem 3rem;
}

/* ── Left: brand + CTA ── */
.hero-left {
  flex: 1;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0;
}

.brand-badge {
  width: 80px;
  height: 80px;
  background: var(--color-primary);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
  box-shadow: 0 8px 24px rgba(185, 28, 28, 0.35);
}

.brand-icon {
  font-size: 2.5rem;
  line-height: 1;
  filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
}

.brand-name {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 3.75rem;
  font-weight: 400;
  color: var(--color-primary);
  line-height: 1;
  margin-bottom: 0.75rem;
  letter-spacing: 0.01em;
}

.tagline {
  font-size: 1.05rem;
  color: var(--color-text-muted);
  line-height: 1.6;
  margin-bottom: 2.25rem;
}

.btn-order {
  background: var(--color-primary);
  color: #fff;
  font-size: 1.35rem;
  font-weight: 700;
  padding: 0 2.5rem;
  height: 64px;
  border-radius: 999px;
  box-shadow: 0 8px 28px rgba(185, 28, 28, 0.38);
  letter-spacing: 0.02em;
  transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
  margin-bottom: 0.75rem;
}
.btn-order:hover {
  background: var(--color-primary-dark);
  box-shadow: 0 12px 32px rgba(185, 28, 28, 0.45);
}
.btn-order:active {
  transform: scale(0.97);
}

.hint {
  font-size: 0.82rem;
  color: var(--color-text-muted);
  letter-spacing: 0.04em;
}

/* ── Right: feature card + highlights ── */
.hero-right {
  flex: 1;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.feature-card {
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-card);
  padding: 1.75rem;
  position: relative;
  min-height: 220px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.feature-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--color-primary);
  background: var(--color-primary-light);
  border-radius: 999px;
  padding: 0.2rem 0.75rem;
  display: inline-block;
  margin-bottom: 1rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  flex: 1;
}

.feature-emoji {
  font-size: 4rem;
  line-height: 1;
  flex-shrink: 0;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
}

.feature-info {
  flex: 1;
}

.feature-name {
  font-family: 'Fredoka One', system-ui, sans-serif;
  font-size: 1.5rem;
  color: var(--color-text);
  margin-bottom: 0.35rem;
  line-height: 1.2;
}

.feature-desc {
  font-size: 0.875rem;
  color: var(--color-text-muted);
  line-height: 1.5;
  margin-bottom: 0.6rem;
}

.feature-price {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--color-primary);
  font-family: 'Fredoka One', system-ui, sans-serif;
}

/* ── Slide dots ── */
.slide-dots {
  display: flex;
  gap: 6px;
  margin-top: 1rem;
}
.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-border);
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}
.dot.active {
  background: var(--color-primary);
  transform: scale(1.25);
}

/* ── Quick highlights row ── */
.highlights {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}

.highlight-item {
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.75rem 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  text-align: center;
  box-shadow: var(--shadow-card);
}

.highlight-emoji {
  font-size: 1.6rem;
  line-height: 1;
}

.highlight-name {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--color-text);
  line-height: 1.2;
}

.highlight-price {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--color-primary);
}

/* ── Flavor strip ── */
.flavor-strip {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding: 0.75rem 2rem 2.25rem;
}

.flavor-chip {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-primary);
  background: var(--color-surface);
  border: 1.5px solid var(--color-primary-light);
  border-radius: 999px;
  padding: 0.3rem 0.9rem;
  white-space: nowrap;
}

/* ── Slide transition ── */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>
