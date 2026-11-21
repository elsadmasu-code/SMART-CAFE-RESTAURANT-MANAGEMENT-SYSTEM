<template>
  <div class="space-y-8 pb-12">
    <div>
      <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Waiter & Floor Service / የአስተናጋጅ ገጽ</h2>
      <p class="text-xs text-stone-500">Pick up ready dishes from Kitchen & respond to customer calls</p>
    </div>

    <!-- Ready Orders Awaiting Delivery -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden space-y-2">
      <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Utensils class="w-5 h-5 text-emerald-600" />
          <h3 class="font-bold text-stone-900 text-sm">Ready for Table Delivery</h3>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">
          {{ tasks.ready_orders?.length || 0 }} Ready
        </span>
      </div>

      <div v-if="!tasks.ready_orders || tasks.ready_orders.length === 0" class="p-8 text-center text-xs text-stone-500">
        No orders currently waiting for table delivery.
      </div>

      <div v-else class="divide-y divide-stone-100">
        <div
          v-for="order in tasks.ready_orders"
          :key="order.id"
          class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-stone-900 text-sm">Order #{{ order.order_number }}</span>
              <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-xs">
                Deliver to Table: {{ order.table?.table_number || 'Takeaway' }}
              </span>
            </div>
            <p class="text-xs text-stone-500 mt-1">
              Items:
              <span v-for="it in order.items" :key="it.id" class="font-medium text-stone-800 mr-2">
                {{ it.product_name_snapshot }} (x{{ it.quantity }})
              </span>
            </p>
          </div>

          <button
            @click="markDelivered(order.id)"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm sm:shrink-0"
          >
            Mark Delivered / ደርሷል ✔
          </button>
        </div>
      </div>
    </div>

    <!-- Table Service Requests (Call Waiter) -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden space-y-2">
      <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <BellRing class="w-5 h-5 text-amber-600" />
          <h3 class="font-bold text-stone-900 text-sm">Customer Table Assistance Calls</h3>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">
          {{ tasks.service_calls?.length || 0 }} Calls
        </span>
      </div>

      <div v-if="!tasks.service_calls || tasks.service_calls.length === 0" class="p-8 text-center text-xs text-stone-500">
        No pending table assistance requests.
      </div>

      <div v-else class="divide-y divide-stone-100">
        <div
          v-for="call in tasks.service_calls"
          :key="call.id"
          class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-stone-900 text-sm">Table: {{ call.table?.table_number }}</span>
              <span class="text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-700 font-semibold uppercase">
                Request: {{ call.request_type }}
              </span>
            </div>
            <p v-if="call.notes" class="text-xs text-stone-500 mt-1 italic">Note: {{ call.notes }}</p>
          </div>

          <div class="flex gap-2">
            <button
              v-if="call.status === 'pending'"
              @click="updateCall(call.id, 'acknowledged')"
              class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold text-xs transition"
            >
              Acknowledge
            </button>
            <button
              @click="updateCall(call.id, 'completed')"
              class="px-4 py-2 rounded-xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition"
            >
              Complete Service
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Utensils, BellRing } from 'lucide-vue-next'
import { apiRequest } from '../services/api'

const tasks = ref({
  ready_orders: [],
  service_calls: [],
})

async function loadTasks() {
  try {
    const res = await apiRequest('/waiter/tasks')
    if (res.success && res.data) {
      tasks.value = res.data
    }
  } catch (err) {
    console.error(err)
  }
}

async function markDelivered(orderId) {
  try {
    const res = await apiRequest(`/waiter/orders/${orderId}/delivered`, {
      method: 'POST',
    })
    if (res.success) {
      await loadTasks()
    }
  } catch (err) {
    alert(err.message || 'Action failed.')
  }
}

async function updateCall(callId, status) {
  try {
    const res = await apiRequest(`/waiter/service-call/${callId}`, {
      method: 'PATCH',
      body: JSON.stringify({ status }),
    })
    if (res.success) {
      await loadTasks()
    }
  } catch (err) {
    alert(err.message || 'Action failed.')
  }
}

onMounted(() => {
  loadTasks()
  setInterval(loadTasks, 4000)
})
</script>
