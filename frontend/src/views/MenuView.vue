<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Category & Filter Header -->
    <div class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
            {{ locale.t('nav_menu') }}
          </h2>
          <p class="text-xs text-stone-500">Freshly prepared with authentic Ethiopian ingredients</p>
        </div>

        <!-- Fasting Filter Toggle -->
        <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3.5 py-2 rounded-2xl border border-stone-200 shadow-sm text-xs font-semibold text-stone-700">
          <input type="checkbox" v-model="menu.fastingOnly" class="rounded text-cafe-600 focus:ring-cafe-500" />
          <span class="text-emerald-700 font-bold">🌱 {{ locale.t('fasting_only') }}</span>
        </label>
      </div>

      <!-- Search Input -->
      <div class="relative">
        <Search class="w-5 h-5 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="menu.searchQuery"
          type="text"
          :placeholder="locale.t('search_placeholder')"
          class="w-full pl-10 pr-4 py-3 rounded-2xl border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-cafe-500 shadow-sm"
        />
      </div>

      <!-- Category Filter Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <button
          @click="menu.activeCategory = null"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition"
          :class="menu.activeCategory === null ? 'bg-cafe-700 text-white shadow-md' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50'"
        >
          All Items / ሁሉም
        </button>
        <button
          v-for="cat in menu.categories"
          :key="cat.id"
          @click="menu.activeCategory = cat.id"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition"
          :class="menu.activeCategory === cat.id ? 'bg-cafe-700 text-white shadow-md' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50'"
        >
          {{ cat.name }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="menu.loading" class="py-20 text-center space-y-3">
      <Loader2 class="w-8 h-8 animate-spin text-cafe-600 mx-auto" />
      <p class="text-sm text-stone-500">Loading Fkr Cafe menu...</p>
    </div>

    <!-- Product Grid Grouped by Category -->
    <div v-else class="space-y-10">
      <div
        v-for="cat in menu.filteredCategories"
        :key="cat.id"
        class="space-y-4"
        v-show="menu.activeCategory === null || menu.activeCategory === cat.id"
      >
        <div class="flex items-center gap-2 border-b border-stone-200 pb-2">
          <h3 class="text-xl font-bold text-stone-900">{{ cat.name }}</h3>
          <span class="text-xs px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 font-semibold">
            {{ cat.products.length }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="product in cat.products"
            :key="product.id"
            class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between"
          >
            <!-- Card Body -->
            <div class="p-5 space-y-3">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-1.5 mb-1">
                    <span v-if="product.is_fasting" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                      የጾም / Fasting
                    </span>
                    <span class="text-[10px] text-stone-400 flex items-center gap-1">
                      <Clock class="w-3 h-3" /> {{ product.preparation_time_minutes }} {{ locale.t('minutes') }}
                    </span>
                  </div>
                  <h4 class="font-bold text-stone-900 text-base leading-snug">{{ product.name }}</h4>
                </div>
                <span class="font-extrabold text-cafe-700 text-base shrink-0">
                  {{ product.price }} {{ locale.t('currency') }}
                </span>
              </div>

              <p class="text-xs text-stone-600 leading-relaxed line-clamp-2">
                {{ product.description }}
              </p>
            </div>

            <!-- Card Action Footer -->
            <div class="p-4 bg-stone-50 border-t border-stone-100 flex items-center justify-between">
              <span class="text-xs text-stone-500">
                {{ product.variants?.length > 0 ? 'Customizable' : 'Standard' }}
              </span>
              <button
                @click="openProductModal(product)"
                class="px-4 py-2 rounded-xl bg-cafe-700 hover:bg-cafe-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
              >
                <Plus class="w-4 h-4" />
                <span>{{ locale.t('btn_add_to_cart') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Customization Modal -->
    <div v-if="selectedProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
      <div class="bg-white rounded-3xl w-full max-w-lg p-6 shadow-2xl border border-stone-200 space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-bold text-stone-900 text-lg">{{ selectedProduct.name }}</h3>
            <p class="text-xs text-cafe-700 font-bold mt-0.5">{{ selectedProduct.price }} {{ locale.t('currency') }}</p>
          </div>
          <button @click="selectedProduct = null" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-xl">
            <X class="w-5 h-5" />
          </button>
        </div>

        <p class="text-xs text-stone-600 leading-relaxed">{{ selectedProduct.description }}</p>

        <!-- Variants -->
        <div v-if="selectedProduct.variants?.length > 0" class="space-y-2">
          <label class="text-xs font-bold text-stone-800 uppercase tracking-wider block">Choose Variant / መጠን</label>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="v in selectedProduct.variants"
              :key="v.id"
              type="button"
              @click="selectedVariant = v"
              class="p-2.5 rounded-xl border text-xs font-semibold text-left transition"
              :class="selectedVariant?.id === v.id ? 'border-cafe-600 bg-cafe-50 text-cafe-800 ring-2 ring-cafe-500/20' : 'border-stone-200 text-stone-700 hover:bg-stone-50'"
            >
              <div>{{ v.name }}</div>
              <div class="text-[11px] text-stone-500 font-normal">+{{ v.price_modifier }} {{ locale.t('currency') }}</div>
            </button>
          </div>
        </div>

        <!-- Add-ons -->
        <div v-if="selectedProduct.addons?.length > 0" class="space-y-2">
          <label class="text-xs font-bold text-stone-800 uppercase tracking-wider block">Optional Extras / ተጨማሪዎች</label>
          <div class="space-y-1.5">
            <label
              v-for="a in selectedProduct.addons"
              :key="a.id"
              class="flex items-center justify-between p-2.5 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer text-xs"
            >
              <div class="flex items-center gap-2">
                <input type="checkbox" :value="a" v-model="selectedAddons" class="rounded text-cafe-600" />
                <span class="font-medium text-stone-800">{{ a.name }}</span>
              </div>
              <span class="text-stone-500 font-semibold">+{{ a.extra_price }} {{ locale.t('currency') }}</span>
            </label>
          </div>
        </div>

        <!-- Special Instructions -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-stone-800 uppercase tracking-wider block">Special Notes / ተጨማሪ መመሪያ</label>
          <input
            v-model="specialInstructions"
            type="text"
            placeholder="e.g. Extra spicy, no sugar, separate cup..."
            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-cafe-500"
          />
        </div>

        <!-- Quantity & Add Button -->
        <div class="flex items-center justify-between pt-4 border-t border-stone-100">
          <div class="flex items-center gap-3 bg-stone-100 px-3 py-1.5 rounded-xl">
            <button @click="quantity = Math.max(1, quantity - 1)" class="text-stone-600 font-bold p-1 hover:text-stone-900">-</button>
            <span class="font-bold text-sm text-stone-900 w-4 text-center">{{ quantity }}</span>
            <button @click="quantity++" class="text-stone-600 font-bold p-1 hover:text-stone-900">+</button>
          </div>

          <button
            @click="confirmAddToCart"
            class="px-6 py-3 rounded-2xl bg-cafe-700 text-white font-bold text-xs hover:bg-cafe-800 transition shadow-md"
          >
            {{ locale.t('btn_add_to_cart') }} • {{ calculateModalTotal() }} {{ locale.t('currency') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Search, Clock, Plus, X, Loader2 } from 'lucide-vue-next'
import { useMenuStore } from '../stores/menu'
import { useCartStore } from '../stores/cart'
import { useLocaleStore } from '../stores/locale'

const menu = useMenuStore()
const cart = useCartStore()
const locale = useLocaleStore()

const selectedProduct = ref(null)
const selectedVariant = ref(null)
const selectedAddons = ref([])
const specialInstructions = ref('')
const quantity = ref(1)

onMounted(() => {
  menu.fetchMenu(locale.currentLocale)
})

function openProductModal(product) {
  selectedProduct.value = product
  selectedVariant.value = product.variants?.[0] || null
  selectedAddons.value = []
  specialInstructions.value = ''
  quantity.value = 1
}

function calculateModalTotal() {
  if (!selectedProduct.value) return 0
  let total = selectedProduct.value.price
  if (selectedVariant.value) total += selectedVariant.value.price_modifier
  for (const a of selectedAddons.value) total += a.extra_price
  return (total * quantity.value).toFixed(2)
}

function confirmAddToCart() {
  if (!selectedProduct.value) return
  cart.addItem(
    selectedProduct.value,
    quantity.value,
    selectedVariant.value,
    selectedAddons.value,
    specialInstructions.value
  )
  selectedProduct.value = null
}
</script>
