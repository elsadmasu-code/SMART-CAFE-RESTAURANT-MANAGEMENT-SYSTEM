<template>
  <div class="min-h-screen bg-stone-50 flex flex-col pb-20 md:pb-0">
    <!-- Top Announcement / Cafe Info Bar -->
    <div class="bg-cafe-900 text-amber-200/90 text-xs py-1.5 px-4 text-center flex items-center justify-between font-medium">
      <div class="flex items-center gap-2 mx-auto">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span>Fkr Cafe • Kombolcha, Ethiopia • Open Today: 06:30 - 22:30</span>
      </div>
      <!-- Language Switcher -->
      <div class="flex items-center gap-1.5 bg-cafe-800/80 px-2 py-0.5 rounded-full border border-cafe-700 text-xs">
        <button
          @click="locale.setLocale('am')"
          :class="locale.currentLocale === 'am' ? 'text-amber-300 font-bold' : 'text-stone-300 hover:text-white'"
        >
          አማርኛ
        </button>
        <span class="text-cafe-600">|</span>
        <button
          @click="locale.setLocale('en')"
          :class="locale.currentLocale === 'en' ? 'text-amber-300 font-bold' : 'text-stone-300 hover:text-white'"
        >
          EN
        </button>
      </div>
    </div>

    <!-- Main Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cafe-700 to-cafe-500 flex items-center justify-center text-white shadow-md shadow-cafe-900/10">
            <Coffee class="w-6 h-6 text-amber-200" />
          </div>
          <div>
            <h1 class="font-extrabold text-stone-900 text-lg leading-tight tracking-tight">
              {{ locale.t('brand_name') }}
            </h1>
            <p class="text-[11px] text-cafe-600 font-semibold uppercase tracking-wider">Kombolcha • Ethiopia</p>
          </div>
        </router-link>

        <!-- Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-stone-600">
          <router-link to="/" class="hover:text-cafe-700 transition" active-class="text-cafe-700 font-bold">
            {{ locale.t('nav_home') }}
          </router-link>
          <router-link to="/menu" class="hover:text-cafe-700 transition" active-class="text-cafe-700 font-bold">
            {{ locale.t('nav_menu') }}
          </router-link>
          <router-link to="/order-tracking" class="hover:text-cafe-700 transition" active-class="text-cafe-700 font-bold">
            {{ locale.t('nav_order_tracking') }}
          </router-link>
        </nav>

        <!-- Right Action Items -->
        <div class="flex items-center gap-3">
          <!-- Active Table Badge -->
          <div v-if="cart.tableNumber" class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>{{ locale.t('table_number') }}: {{ cart.tableNumber }}</span>
          </div>

          <!-- Call Waiter Button -->
          <button
            @click="isWaiterModalOpen = true"
            class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-cafe-300 text-cafe-800 hover:bg-cafe-50 text-xs font-semibold transition"
          >
            <BellRing class="w-3.5 h-3.5 text-cafe-600" />
            <span>{{ locale.t('nav_call_waiter') }}</span>
          </button>

          <!-- Cart Button -->
          <router-link
            to="/cart"
            class="relative p-2.5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 transition"
            :aria-label="locale.t('nav_cart')"
          >
            <ShoppingBag class="w-5 h-5 text-cafe-800" />
            <span
              v-if="cart.itemCount > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-cafe-600 text-white text-[11px] font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm"
            >
              {{ cart.itemCount }}
            </span>
          </router-link>

          <!-- Staff Portal Link -->
          <router-link
            to="/staff/dashboard"
            class="p-2 rounded-full text-stone-500 hover:text-stone-900 transition"
            title="Staff Login"
          >
            <ShieldCheck class="w-5 h-5" />
          </router-link>
        </div>
      </div>
    </header>

    <!-- Content Outlet -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-400 py-12 px-4 sm:px-6 lg:px-8 border-t border-stone-800">
      <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <div class="flex items-center gap-2 text-white font-bold text-lg mb-2">
            <Coffee class="w-5 h-5 text-amber-400" />
            <span>{{ locale.t('brand_name') }}</span>
          </div>
          <p class="text-xs text-stone-400 leading-relaxed mb-4">
            Smart Cafe & Restaurant Management System (SCRMS) designed for excellence, authentic hospitality, and seamless digital service.
          </p>
          <p class="text-xs text-stone-500">Kombolcha, Amhara Region, Ethiopia</p>
        </div>

        <div>
          <h4 class="text-white text-sm font-semibold mb-3">Customer Access</h4>
          <ul class="text-xs space-y-2">
            <li>• Direct Web Domain: <span class="text-amber-300">www.fkrcafe.com</span></li>
            <li>• Google Search: <span class="text-amber-300">Fkr Cafe Kombolcha</span></li>
            <li>• Table QR Code Ordering (No App Required)</li>
            <li>• Android Mobile Application</li>
            <li>• Offline In-Cafe Wi-Fi Local Server</li>
          </ul>
        </div>

        <div>
          <h4 class="text-white text-sm font-semibold mb-3">Accepted Payments</h4>
          <div class="flex flex-wrap gap-2 text-xs">
            <span class="px-2.5 py-1 rounded bg-stone-800 text-amber-300 font-medium border border-stone-700">Telebirr (ቴሌብር)</span>
            <span class="px-2.5 py-1 rounded bg-stone-800 text-amber-300 font-medium border border-stone-700">CBE Birr (ሲቢኢ ብር)</span>
            <span class="px-2.5 py-1 rounded bg-stone-800 text-stone-300 font-medium border border-stone-700">Cash (ጥሬ ገንዘብ)</span>
          </div>
          <p class="mt-4 text-xs text-stone-500">© 2026 Fkr Cafe. All Rights Reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-stone-200 px-4 py-2 flex items-center justify-around shadow-lg">
      <router-link to="/" class="flex flex-col items-center text-xs text-stone-500" active-class="text-cafe-700 font-bold">
        <Home class="w-5 h-5 mb-0.5" />
        <span>{{ locale.t('nav_home') }}</span>
      </router-link>

      <router-link to="/menu" class="flex flex-col items-center text-xs text-stone-500" active-class="text-cafe-700 font-bold">
        <Utensils class="w-5 h-5 mb-0.5" />
        <span>{{ locale.t('nav_menu') }}</span>
      </router-link>

      <button @click="isWaiterModalOpen = true" class="flex flex-col items-center text-xs text-stone-500">
        <BellRing class="w-5 h-5 mb-0.5 text-amber-600" />
        <span>Call</span>
      </button>

      <router-link to="/cart" class="relative flex flex-col items-center text-xs text-stone-500" active-class="text-cafe-700 font-bold">
        <ShoppingBag class="w-5 h-5 mb-0.5" />
        <span>{{ locale.t('nav_cart') }}</span>
        <span v-if="cart.itemCount > 0" class="absolute -top-1 right-2 w-4 h-4 bg-cafe-600 text-white text-[10px] rounded-full flex items-center justify-center font-bold">
          {{ cart.itemCount }}
        </span>
      </router-link>

      <router-link to="/order-tracking" class="flex flex-col items-center text-xs text-stone-500" active-class="text-cafe-700 font-bold">
        <Clock class="w-5 h-5 mb-0.5" />
        <span>Status</span>
      </router-link>
    </div>

    <!-- Modals -->
    <CallWaiterModal :is-open="isWaiterModalOpen" @close="isWaiterModalOpen = false" />
    <AiAssistantWidget />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Coffee, ShoppingBag, BellRing, ShieldCheck, Home, Utensils, Clock } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'
import { useLocaleStore } from '../stores/locale'
import CallWaiterModal from '../components/CallWaiterModal.vue'
import AiAssistantWidget from '../components/AiAssistantWidget.vue'

const cart = useCartStore()
const locale = useLocaleStore()
const isWaiterModalOpen = ref(false)
</script>
