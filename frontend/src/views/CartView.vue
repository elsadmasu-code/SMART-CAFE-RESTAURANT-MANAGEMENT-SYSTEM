<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
        {{ locale.t('nav_cart') }}
      </h2>
      <button
        v-if="cart.items.length > 0"
        @click="cart.clearCart()"
        class="text-xs text-red-600 hover:underline font-medium"
      >
        Clear All / ባዶ አድርግ
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="cart.items.length === 0" class="bg-white rounded-3xl p-12 text-center border border-stone-200 shadow-sm space-y-4">
      <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto">
        <ShoppingBag class="w-8 h-8" />
      </div>
      <h3 class="font-bold text-stone-800 text-lg">{{ locale.t('cart_empty') }}</h3>
      <p class="text-xs text-stone-500 max-w-sm mx-auto">Browse our delicious Ethiopian dishes, authentic coffee, and pizzas to add items.</p>
      <router-link
        to="/menu"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-cafe-700 text-white font-bold text-xs hover:bg-cafe-800 transition"
      >
        <Utensils class="w-4 h-4" />
        <span>{{ locale.t('btn_view_menu') }}</span>
      </router-link>
    </div>

    <!-- Cart Items & Order Settings -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Items List -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Order Type Selector -->
        <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-3">
          <label class="text-xs font-bold text-stone-800 uppercase tracking-wider block">
            {{ locale.t('order_type') }}
          </label>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="cart.setOrderType('dine_in')"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition text-center"
              :class="cart.orderType === 'dine_in' ? 'bg-cafe-700 text-white border-cafe-700 shadow-sm' : 'border-stone-200 text-stone-700 hover:bg-stone-50'"
            >
              🍽️ {{ locale.t('dine_in') }}
            </button>
            <button
              type="button"
              @click="cart.setOrderType('takeaway')"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition text-center"
              :class="cart.orderType === 'takeaway' ? 'bg-cafe-700 text-white border-cafe-700 shadow-sm' : 'border-stone-200 text-stone-700 hover:bg-stone-50'"
            >
              🛍️ {{ locale.t('takeaway') }}
            </button>
            <button
              type="button"
              @click="cart.setOrderType('delivery')"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition text-center"
              :class="cart.orderType === 'delivery' ? 'bg-cafe-700 text-white border-cafe-700 shadow-sm' : 'border-stone-200 text-stone-700 hover:bg-stone-50'"
            >
              🛵 {{ locale.t('delivery') }}
            </button>
          </div>

          <!-- Table Number Selector if Dine-In -->
          <div v-if="cart.orderType === 'dine_in'" class="pt-2 flex items-center justify-between text-xs">
            <span class="text-stone-600 font-medium">Assigned Table / ጠረጴዛ ቁጥር:</span>
            <select
              v-model="cart.tableId"
              @change="cart.setTable(cart.tableId, `T-${cart.tableId}`)"
              class="bg-stone-50 border border-stone-300 rounded-lg px-3 py-1 font-bold text-cafe-800 focus:ring-1 focus:ring-cafe-500"
            >
              <option v-for="i in 15" :key="i" :value="i.toString()">Table T-{{ i }}</option>
            </select>
          </div>

          <!-- Delivery Address Input if Delivery -->
          <div v-if="cart.orderType === 'delivery'" class="pt-2 space-y-1">
            <label class="text-xs text-stone-600 font-medium">Delivery Address in Kombolcha / አድራሻ:</label>
            <input
              v-model="cart.deliveryAddress"
              type="text"
              placeholder="e.g. Near St. Mary Church, Kebelle 03"
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50"
            />
          </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-3xl border border-stone-200 divide-y divide-stone-100 shadow-sm overflow-hidden">
          <div v-for="item in cart.items" :key="item.key" class="p-4 sm:p-5 flex items-center justify-between gap-4">
            <div class="space-y-1 flex-1 min-w-0">
              <h4 class="font-bold text-stone-900 text-sm truncate">{{ item.name }}</h4>
              <div class="text-xs text-stone-500 space-y-0.5">
                <span v-if="item.variantName" class="text-cafe-700 font-medium">({{ item.variantName }}) </span>
                <span v-if="item.addons?.length > 0">
                  + {{ item.addons.map(a => a.name).join(', ') }}
                </span>
                <p v-if="item.specialInstructions" class="italic text-stone-400 text-[11px]">Note: {{ item.specialInstructions }}</p>
              </div>
              <div class="text-xs font-bold text-cafe-800">
                {{ item.unitPrice }} {{ locale.t('currency') }}
              </div>
            </div>

            <!-- Quantity Controls -->
            <div class="flex items-center gap-2">
              <div class="flex items-center gap-2 bg-stone-100 px-2 py-1 rounded-xl">
                <button @click="cart.updateQuantity(item.key, item.quantity - 1)" class="p-1 text-stone-600 hover:text-stone-900 font-bold">-</button>
                <span class="w-4 text-center text-xs font-bold text-stone-900">{{ item.quantity }}</span>
                <button @click="cart.updateQuantity(item.key, item.quantity + 1)" class="p-1 text-stone-600 hover:text-stone-900 font-bold">+</button>
              </div>
              <button @click="cart.removeItem(item.key)" class="p-1.5 text-stone-400 hover:text-red-500 transition">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Order Summary -->
      <div class="space-y-4">
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
          <h3 class="font-bold text-stone-900 text-base border-b border-stone-100 pb-3">Summary / ሂሳብ</h3>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between text-stone-600">
              <span>{{ locale.t('cart_subtotal') }}</span>
              <span class="font-semibold">{{ cart.subtotal.toFixed(2) }} {{ locale.t('currency') }}</span>
            </div>
            <div class="flex justify-between text-stone-600">
              <span>{{ locale.t('cart_vat') }}</span>
              <span class="font-semibold">{{ cart.vatAmount.toFixed(2) }} {{ locale.t('currency') }}</span>
            </div>
            <div class="flex justify-between text-stone-600">
              <span>{{ locale.t('cart_service') }}</span>
              <span class="font-semibold">{{ cart.serviceAmount.toFixed(2) }} {{ locale.t('currency') }}</span>
            </div>
            <div class="flex justify-between text-stone-900 text-base font-extrabold pt-3 border-t border-stone-200">
              <span>{{ locale.t('cart_total') }}</span>
              <span class="text-cafe-700">{{ cart.totalAmount.toFixed(2) }} {{ locale.t('currency') }}</span>
            </div>
          </div>

          <router-link
            to="/checkout"
            class="w-full py-3.5 px-4 rounded-2xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-center text-xs transition block shadow-md"
          >
            {{ locale.t('btn_checkout') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ShoppingBag, Utensils, Trash2 } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'
import { useLocaleStore } from '../stores/locale'

const cart = useCartStore()
const locale = useLocaleStore()
</script>
