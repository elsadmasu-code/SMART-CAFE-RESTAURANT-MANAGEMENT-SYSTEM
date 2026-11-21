<template>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="text-center space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cafe-100 text-cafe-800 text-xs font-bold">
        <span>Order Tracking • ፍቅር ካፌ</span>
      </div>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
        Live Order Status
      </h2>
      <p class="text-xs text-stone-500 font-mono">Order: {{ order?.order_number || 'Loading...' }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !order" class="py-16 text-center space-y-3">
      <Loader2 class="w-8 h-8 animate-spin text-cafe-600 mx-auto" />
      <p class="text-xs text-stone-500">Checking order status...</p>
    </div>

    <div v-else-if="order" class="space-y-6">
      <!-- Status Progress Stepper -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between text-xs font-bold">
          <span class="text-stone-500 uppercase">Preparation Milestone</span>
          <span class="text-cafe-700 bg-cafe-50 px-2.5 py-1 rounded-full border border-cafe-200">
            {{ formatStatusLabel(order.order_status) }}
          </span>
        </div>

        <!-- Stepper Bar -->
        <div class="relative flex items-center justify-between">
          <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-stone-200 w-full z-0"></div>
          <div
            class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-cafe-600 transition-all duration-500 z-0"
            :style="{ width: `${progressPercentage}%` }"
          ></div>

          <div
            v-for="(step, idx) in steps"
            :key="idx"
            class="relative z-10 flex flex-col items-center"
          >
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
              :class="currentStepIndex >= idx ? 'bg-cafe-700 text-white shadow-md' : 'bg-white border-2 border-stone-300 text-stone-400'"
            >
              {{ idx + 1 }}
            </div>
            <span class="text-[10px] font-semibold mt-1.5 text-stone-600 text-center hidden sm:block">
              {{ step.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Order Details Summary -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3 text-xs">
          <div>
            <span class="text-stone-500">Service:</span>
            <span class="font-bold text-stone-800 ml-1 uppercase">{{ order.order_type }}</span>
            <span v-if="order.table" class="ml-2 font-bold text-cafe-700">({{ order.table.table_number }})</span>
          </div>
          <button
            @click="isReceiptOpen = true"
            class="text-cafe-700 hover:underline font-bold flex items-center gap-1"
          >
            <ReceiptIcon class="w-3.5 h-3.5" />
            <span>View Receipt / ደረሰኝ</span>
          </button>
        </div>

        <!-- Ordered Items -->
        <div class="divide-y divide-stone-100 text-xs">
          <div v-for="item in order.items" :key="item.id" class="py-2.5 flex justify-between">
            <div>
              <span class="font-semibold text-stone-800">{{ item.product_name_snapshot }}</span>
              <span class="text-stone-500 ml-1.5">x{{ item.quantity }}</span>
            </div>
            <span class="font-bold text-stone-900">{{ item.total_price }} ETB</span>
          </div>
        </div>

        <!-- Payment & Total Status -->
        <div class="pt-3 border-t border-stone-200 flex justify-between items-center text-xs">
          <div>
            <span class="text-stone-500">Payment:</span>
            <span
              class="ml-1.5 font-bold uppercase"
              :class="order.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-600'"
            >
              {{ order.payment_status }}
            </span>
          </div>
          <div class="text-sm font-extrabold text-cafe-800">
            Total: {{ order.total_amount }} ETB
          </div>
        </div>
      </div>

      <!-- Quick Feedback or New Order -->
      <div class="flex gap-3">
        <router-link
          to="/menu"
          class="flex-1 py-3 px-4 rounded-2xl bg-white border border-stone-300 text-stone-700 font-bold text-xs hover:bg-stone-50 text-center transition"
        >
          Order More Food
        </router-link>
        <router-link
          :to="`/feedback?order=${order.order_number}`"
          class="flex-1 py-3 px-4 rounded-2xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs text-center transition shadow-sm"
        >
          Rate Experience / አስተያየት
        </router-link>
      </div>
    </div>

    <!-- Receipt Modal -->
    <ReceiptModal
      :is-open="isReceiptOpen"
      :order="order"
      @close="isReceiptOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { Loader2, Receipt as ReceiptIcon } from 'lucide-vue-next'
import { useOrderStore } from '../stores/order'
import ReceiptModal from '../components/ReceiptModal.vue'

const route = useRoute()
const orderStore = useOrderStore()

const order = ref(null)
const loading = ref(true)
const isReceiptOpen = ref(false)
let pollingTimer = null

const steps = [
  { key: 'submitted', label: 'Submitted' },
  { key: 'confirmed', label: 'Confirmed' },
  { key: 'preparing', label: 'Preparing' },
  { key: 'ready', label: 'Ready' },
  { key: 'delivered', label: 'Delivered' },
]

const currentStepIndex = computed(() => {
  if (!order.value) return 0
  const status = order.value.order_status
  if (['draft', 'submitted', 'awaiting_payment', 'payment_verification_pending'].includes(status)) return 0
  if (status === 'confirmed' || status === 'accepted_by_kitchen') return 1
  if (status === 'preparing') return 2
  if (status === 'ready' || status === 'assigned_to_waiter') return 3
  if (['delivered', 'completed'].includes(status)) return 4
  return 0
})

const progressPercentage = computed(() => {
  return (currentStepIndex.value / (steps.length - 1)) * 100
})

function formatStatusLabel(st) {
  const map = {
    submitted: 'Submitted / ትዕዛዝ ደርሷል',
    awaiting_payment: 'Awaiting Payment',
    payment_verification_pending: 'Verifying Payment...',
    confirmed: 'Payment Confirmed / ተረጋግጧል',
    accepted_by_kitchen: 'Accepted by Kitchen',
    preparing: 'In Kitchen Preparation / በመዘጋጀት ላይ',
    ready: 'Ready for Pickup / ዝግጁ ሆኗል',
    delivered: 'Delivered to Table / ደርሷል',
    completed: 'Completed',
  }
  return map[st] || st
}

async function loadOrder() {
  const uuid = route.params.uuid || orderStore.activeOrderUuid
  if (!uuid) return
  const data = await orderStore.fetchOrderStatus(uuid)
  if (data) {
    order.value = data
  }
  loading.value = false
}

onMounted(() => {
  loadOrder()
  pollingTimer = setInterval(loadOrder, 4000)
})

onUnmounted(() => {
  if (pollingTimer) clearInterval(pollingTimer)
})
</script>
