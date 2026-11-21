<template>
  <div class="space-y-12 pb-16">
    <!-- Hero Banner with Ethiopian Coffee Aesthetics -->
    <section class="relative bg-gradient-to-br from-cafe-950 via-cafe-900 to-cafe-800 text-white overflow-hidden py-20 px-4 sm:px-6 lg:px-8">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>

      <div class="relative max-w-5xl mx-auto text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/30 text-amber-300 text-xs font-semibold uppercase tracking-wider">
          <Sparkles class="w-4 h-4 text-amber-300" />
          <span>Fkr Cafe • Kombolcha • Ethiopia</span>
        </div>

        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
          {{ locale.t('hero_title') }}
        </h1>

        <p class="text-base sm:text-lg text-stone-300 max-w-2xl mx-auto leading-relaxed">
          {{ locale.t('hero_subtitle') }}
        </p>

        <!-- Fast Call to Action Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
          <router-link
            to="/menu"
            class="px-8 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold transition shadow-lg shadow-amber-500/20 text-sm flex items-center gap-2"
          >
            <Utensils class="w-4 h-4" />
            <span>{{ locale.t('btn_order_now') }}</span>
          </router-link>

          <button
            @click="isQrSimulatorOpen = true"
            class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-medium backdrop-blur-md border border-white/20 transition text-sm flex items-center gap-2"
          >
            <QrCode class="w-4 h-4 text-amber-300" />
            <span>Scan Table QR Code</span>
          </button>
        </div>

        <!-- Multi-Channel Access Badges (SRS Section 4) -->
        <div class="pt-8 flex flex-wrap items-center justify-center gap-3 text-xs text-stone-400">
          <span class="flex items-center gap-1.5 bg-stone-800/80 px-3 py-1 rounded-full border border-stone-700/60">
            <Globe class="w-3.5 h-3.5 text-blue-400" /> Direct Domain: www.fkrcafe.com
          </span>
          <span class="flex items-center gap-1.5 bg-stone-800/80 px-3 py-1 rounded-full border border-stone-700/60">
            <Search class="w-3.5 h-3.5 text-red-400" /> Google Search: Fkr Cafe Kombolcha
          </span>
          <span class="flex items-center gap-1.5 bg-stone-800/80 px-3 py-1 rounded-full border border-stone-700/60">
            <Smartphone class="w-3.5 h-3.5 text-emerald-400" /> Android App & Local Wi-Fi
          </span>
        </div>
      </div>
    </section>

    <!-- Quick Features Highlight -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Feature 1: Traditional Coffee -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-amber-50 text-cafe-700 flex items-center justify-center mb-4">
            <Coffee class="w-6 h-6" />
          </div>
          <h3 class="font-bold text-stone-900 text-lg mb-1">Authentic Jebena Coffee</h3>
          <p class="text-stone-600 text-sm leading-relaxed">
            Slow brewed organic Ethiopian highland coffee served in traditional clay pots with fresh Rue (ጤና አዳም).
          </p>
        </div>

        <!-- Feature 2: Digital Payments -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
            <CreditCard class="w-6 h-6" />
          </div>
          <h3 class="font-bold text-stone-900 text-lg mb-1">Telebirr & CBE Birr</h3>
          <p class="text-stone-600 text-sm leading-relaxed">
            Pay instantly with Telebirr, Commercial Bank of Ethiopia (CBE Birr), or cash directly to your waiter.
          </p>
        </div>

        <!-- Feature 3: Smart AI Assistant -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center mb-4">
            <Bot class="w-6 h-6" />
          </div>
          <h3 class="font-bold text-stone-900 text-lg mb-1">Bilingual AI Assistant</h3>
          <p class="text-stone-600 text-sm leading-relaxed">
            Ask our Gemini AI in Amharic or English for meal suggestions, fasting menus, or ingredient details.
          </p>
        </div>
      </div>
    </section>

    <!-- Google Search Discovery Simulation Card (SRS Section 4.2) -->
    <section class="max-w-4xl mx-auto px-4">
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-stone-100">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
            G
          </div>
          <div>
            <h4 class="font-bold text-stone-900 text-base">Google Business & Search Discovery</h4>
            <p class="text-xs text-stone-500">Verified Listing: Fkr Cafe (Kombolcha, Ethiopia)</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-stone-600">
          <div>
            <span class="font-semibold text-stone-800">Address:</span>
            <p>Main Street, Near CBE, Kombolcha, Amhara, Ethiopia</p>
          </div>
          <div>
            <span class="font-semibold text-stone-800">Operating Hours:</span>
            <p>Monday - Sunday: 06:30 AM - 10:30 PM (22:30)</p>
          </div>
          <div>
            <span class="font-semibold text-stone-800">Phone:</span>
            <p>+251 33 551 2345 / +251 911 000 001</p>
          </div>
          <div>
            <span class="font-semibold text-stone-800">Rating:</span>
            <p class="text-amber-600 font-bold">★ 4.9 (420+ Google Reviews)</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Table QR Code Simulator Modal -->
    <div v-if="isQrSimulatorOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
      <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl border border-stone-200 text-center space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-cafe-700 flex items-center justify-center mx-auto">
          <QrCode class="w-6 h-6" />
        </div>
        <h3 class="font-bold text-stone-900 text-lg">Select Table to Simulate QR Scan</h3>
        <p class="text-xs text-stone-500">In the cafe, scanning the QR sticker on your table automatically links your order.</p>

        <div class="grid grid-cols-3 gap-2 py-2">
          <button
            v-for="i in 15"
            :key="i"
            @click="selectTable(i)"
            class="py-2.5 rounded-xl border border-stone-200 text-sm font-semibold hover:border-cafe-600 hover:bg-cafe-50 hover:text-cafe-800 transition"
          >
            Table {{ i }}
          </button>
        </div>

        <button
          @click="isQrSimulatorOpen = false"
          class="w-full py-2.5 rounded-xl border border-stone-300 text-stone-700 font-medium text-sm hover:bg-stone-50"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Sparkles,
  Utensils,
  QrCode,
  Coffee,
  CreditCard,
  Bot,
  Globe,
  Search,
  Smartphone
} from 'lucide-vue-next'
import { useLocaleStore } from '../stores/locale'
import { useCartStore } from '../stores/cart'

const locale = useLocaleStore()
const cart = useCartStore()
const router = useRouter()
const isQrSimulatorOpen = ref(false)

function selectTable(num) {
  cart.setTable(num.toString(), `T-${num}`)
  isQrSimulatorOpen.value = false
  router.push('/menu')
}
</script>
