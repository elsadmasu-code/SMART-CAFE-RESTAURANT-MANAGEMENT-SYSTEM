<template>
  <div class="space-y-6 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Cashier POS / የካሺየር ገጽ</h2>
        <p class="text-xs text-stone-500">Incoming Orders, Payment Reconciliation & Receipt Issuing</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="loadOrders"
          class="px-4 py-2 rounded-xl bg-white border border-stone-300 text-stone-700 font-bold text-xs hover:bg-stone-50 transition flex items-center gap-1.5"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Active Orders Queue -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <h3 class="font-bold text-stone-900 text-sm">Incoming Customer Orders</h3>
        <span class="text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">
          {{ pendingOrders.length }} Awaiting Action
        </span>
      </div>

      <div v-if="orders.length === 0" class="p-12 text-center text-xs text-stone-500">
        No active orders found. New customer orders will appear here automatically.
      </div>

      <div v-else class="divide-y divide-stone-100">
        <div
          v-for="order in orders"
          :key="order.id"
          class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-stone-50/50 transition"
        >
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <span class="font-bold text-stone-900 text-sm">{{ order.order_number }}</span>
              <span class="text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-700 font-semibold uppercase">
                {{ order.order_type }}
              </span>
              <span v-if="order.table" class="text-xs font-bold text-cafe-700">
                ({{ order.table.table_number }})
              </span>
            </div>
            <p class="text-xs text-stone-500">
              Customer: <span class="font-medium text-stone-800">{{ order.guest_name || 'Guest' }}</span> • {{ order.guest_phone || 'No phone' }}
            </p>
            <div class="text-xs text-stone-600">
              <span v-for="(it, idx) in order.items" :key="it.id">
                {{ it.product_name_snapshot }} x{{ it.quantity }}{{ idx < order.items.length - 1 ? ', ' : '' }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-4 sm:shrink-0 justify-between sm:justify-end">
            <div class="text-right">
              <div class="text-base font-extrabold text-stone-900">{{ order.total_amount }} ETB</div>
              <span
                class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                :class="order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
              >
                {{ order.payment_status }}
              </span>
            </div>

            <!-- Verification / Receipt Actions -->
            <button
              v-if="order.payment_status !== 'paid'"
              @click="openVerifyModal(order)"
              class="px-4 py-2.5 rounded-xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition shadow-sm"
            >
              Verify Payment / አረጋግጥ
            </button>
            <button
              v-else
              @click="selectedReceiptOrder = order; isReceiptOpen = true"
              class="px-3.5 py-2.5 rounded-xl border border-stone-300 text-stone-700 font-semibold text-xs hover:bg-stone-50 transition"
            >
              Print Receipt
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Verification Modal -->
    <div v-if="verifyingOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
      <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-stone-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
          <div>
            <h3 class="font-bold text-stone-900 text-base">Verify Payment</h3>
            <p class="text-xs text-stone-500">Order: {{ verifyingOrder.order_number }}</p>
          </div>
          <button @click="verifyingOrder = null" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-xl">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200 flex justify-between text-xs">
          <span class="text-stone-600">Total Due Amount:</span>
          <span class="font-extrabold text-cafe-800 text-sm">{{ verifyingOrder.total_amount }} ETB</span>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">Payment Method</label>
            <select
              v-model="verificationMethod"
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50 font-medium"
            >
              <option value="cash">Cash on Hand / ጥሬ ገንዘብ</option>
              <option value="telebirr">Telebirr (ቴሌብር)</option>
              <option value="cbe_birr">CBE Birr (ሲቢኢ ብር)</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">Transaction Reference (for Telebirr/CBE)</label>
            <input
              v-model="verificationRef"
              type="text"
              placeholder="e.g. TB10982348 or Cashier Initials"
              class="w-full text-xs font-mono px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50"
            />
          </div>
        </div>

        <div class="pt-2 flex gap-3">
          <button
            @click="verifyingOrder = null"
            class="flex-1 py-2.5 rounded-xl border border-stone-300 text-stone-700 font-medium text-xs hover:bg-stone-50"
          >
            Cancel
          </button>
          <button
            @click="submitVerification"
            :disabled="submitting"
            class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2"
          >
            <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
            <span>Confirm & Send to Kitchen</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Receipt Modal -->
    <ReceiptModal
      :is-open="isReceiptOpen"
      :order="selectedReceiptOrder"
      @close="isReceiptOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RefreshCw, X, Loader2 } from 'lucide-vue-next'
import { apiRequest } from '../services/api'
import ReceiptModal from '../components/ReceiptModal.vue'

const orders = ref([])
const loading = ref(false)
const verifyingOrder = ref(null)
const verificationMethod = ref('telebirr')
const verificationRef = ref('')
const submitting = ref(false)
const isReceiptOpen = ref(false)
const selectedReceiptOrder = ref(null)

const pendingOrders = computed(() => {
  return orders.value.filter(o => o.payment_status !== 'paid')
})

async function loadOrders() {
  loading.value = true
  try {
    const res = await apiRequest('/orders')
    if (res.success && res.data) {
      orders.value = res.data.data || res.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function openVerifyModal(order) {
  verifyingOrder.value = order
  verificationMethod.value = order.payments?.[0]?.payment_method || 'telebirr'
  verificationRef.value = order.payments?.[0]?.transaction_reference || 'VERIFIED-CASHIER'
}

async function submitVerification() {
  if (!verifyingOrder.value) return
  submitting.value = true
  try {
    const res = await apiRequest('/payments/verify', {
      method: 'POST',
      body: JSON.stringify({
        order_id: verifyingOrder.value.id,
        payment_method: verificationMethod.value,
        amount_paid: verifyingOrder.value.total_amount,
        transaction_reference: verificationRef.value,
      }),
    })

    if (res.success) {
      verifyingOrder.value = null
      await loadOrders()
    }
  } catch (err) {
    alert(err.message || 'Payment verification failed.')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadOrders()
})
</script>
