<template>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="border-b border-stone-200 pb-4">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
        Checkout & Payment / ክፍያ
      </h2>
      <p class="text-xs text-stone-500">Order from Fkr Cafe (Kombolcha, Ethiopia)</p>
    </div>

    <form @submit.prevent="handleCheckout" class="space-y-6">
      <!-- Guest Details -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <h3 class="font-bold text-stone-900 text-sm">Customer Details / የደንበኛ መረጃ</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Full Name / ሙሉ ስም</label>
            <input
              v-model="cart.guestName"
              type="text"
              required
              placeholder="e.g. Tigist Haile"
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-cafe-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Phone Number / ስልክ ቁጥር</label>
            <input
              v-model="cart.guestPhone"
              type="tel"
              required
              placeholder="e.g. +251 91 123 4567"
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-cafe-500 focus:outline-none"
            />
          </div>
        </div>
      </div>

      <!-- Payment Method Selection -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <h3 class="font-bold text-stone-900 text-sm">{{ locale.t('payment_method') }}</h3>

        <div class="space-y-2.5">
          <!-- Telebirr -->
          <label
            class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
            :class="selectedMethod === 'telebirr' ? 'border-amber-500 bg-amber-50/50 ring-2 ring-amber-500/20' : 'border-stone-200 hover:bg-stone-50'"
          >
            <input type="radio" value="telebirr" v-model="selectedMethod" class="mt-1 text-amber-600 focus:ring-amber-500" />
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm text-stone-900">{{ locale.t('payment_telebirr') }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Popular</span>
              </div>
              <p class="text-xs text-stone-500 mt-0.5">{{ locale.t('payment_instruction_telebirr') }}</p>
            </div>
          </label>

          <!-- CBE Birr -->
          <label
            class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
            :class="selectedMethod === 'cbe_birr' ? 'border-purple-500 bg-purple-50/50 ring-2 ring-purple-500/20' : 'border-stone-200 hover:bg-stone-50'"
          >
            <input type="radio" value="cbe_birr" v-model="selectedMethod" class="mt-1 text-purple-600 focus:ring-purple-500" />
            <div class="flex-1">
              <span class="font-bold text-sm text-stone-900">{{ locale.t('payment_cbe_birr') }}</span>
              <p class="text-xs text-stone-500 mt-0.5">{{ locale.t('payment_instruction_cbe') }}</p>
            </div>
          </label>

          <!-- Cash -->
          <label
            class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
            :class="selectedMethod === 'cash' ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : 'border-stone-200 hover:bg-stone-50'"
          >
            <input type="radio" value="cash" v-model="selectedMethod" class="mt-1 text-emerald-600 focus:ring-emerald-500" />
            <div class="flex-1">
              <span class="font-bold text-sm text-stone-900">{{ locale.t('payment_cash') }}</span>
              <p class="text-xs text-stone-500 mt-0.5">Pay in Ethiopian Birr cash to your waiter upon table delivery or at counter.</p>
            </div>
          </label>
        </div>

        <!-- Transaction Reference Input for Digital Payments -->
        <div v-if="selectedMethod === 'telebirr' || selectedMethod === 'cbe_birr'" class="pt-3 border-t border-stone-100 space-y-2">
          <label class="text-xs font-bold text-stone-800 block">
            Transaction ID / የደረሰኝ ቁጥር <span class="text-red-500">*</span>
          </label>
          <input
            v-model="transactionReference"
            type="text"
            required
            :placeholder="locale.t('payment_ref_placeholder')"
            class="w-full text-xs font-mono px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-cafe-500 focus:outline-none"
          />
        </div>
      </div>

      <!-- Total & Submit Button -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs text-stone-500">{{ locale.t('cart_total') }}</span>
          <p class="text-xl font-extrabold text-cafe-800">{{ cart.totalAmount.toFixed(2) }} {{ locale.t('currency') }}</p>
        </div>

        <button
          type="submit"
          :disabled="orderStore.submitting"
          class="px-8 py-3.5 rounded-2xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition flex items-center gap-2 shadow-lg disabled:opacity-50"
        >
          <Loader2 v-if="orderStore.submitting" class="w-4 h-4 animate-spin" />
          <span>{{ locale.t('btn_order_now') }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Loader2 } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'
import { useOrderStore } from '../stores/order'
import { useLocaleStore } from '../stores/locale'

const cart = useCartStore()
const orderStore = useOrderStore()
const locale = useLocaleStore()
const router = useRouter()

const selectedMethod = ref('telebirr')
const transactionReference = ref('')

async function handleCheckout() {
  try {
    const orderData = await orderStore.placeOrder()
    if (selectedMethod.value !== 'cash') {
      await orderStore.submitPayment(
        selectedMethod.value,
        transactionReference.value,
        cart.totalAmount
      )
    }
    router.push(`/order-tracking/${orderData.order_uuid}`)
  } catch (err) {
    alert(err.message || 'Failed to place order.')
  }
}
</script>
