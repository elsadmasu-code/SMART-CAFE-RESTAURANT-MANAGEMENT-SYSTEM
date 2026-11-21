<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Kitchen Display System (KDS) / የወጥ ቤት ገጽ</h2>
        <p class="text-xs text-stone-500">Live Food Preparation Tickets & Station Dispatch</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="loadTickets"
          class="px-4 py-2 rounded-xl bg-white border border-stone-300 text-stone-700 font-bold text-xs hover:bg-stone-50 transition flex items-center gap-1.5"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
          <span>Refresh Tickets</span>
        </button>
      </div>
    </div>

    <!-- Active Tickets Grid -->
    <div v-if="tickets.length === 0" class="bg-white rounded-3xl p-12 text-center border border-stone-200 text-xs text-stone-500">
      All orders have been prepared! New orders will appear here automatically when confirmed by Cashier.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="ticket in tickets"
        :key="ticket.id"
        class="bg-white rounded-3xl border-2 overflow-hidden shadow-sm flex flex-col justify-between transition hover:shadow-md"
        :class="getTicketBorderClass(ticket.elapsed_minutes, ticket.target_time_minutes)"
      >
        <!-- Card Header -->
        <div class="p-4 border-b border-stone-100 flex items-center justify-between bg-stone-50/70">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-extrabold text-stone-900 text-base">#{{ ticket.order_number }}</span>
              <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                Table: {{ ticket.table_number }}
              </span>
            </div>
            <span class="text-[11px] text-stone-500 uppercase font-semibold">{{ ticket.order_type }}</span>
          </div>

          <!-- Timer Badge -->
          <div
            class="px-2.5 py-1 rounded-xl text-xs font-bold flex items-center gap-1"
            :class="ticket.elapsed_minutes > ticket.target_time_minutes ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-stone-200 text-stone-800'"
          >
            <Clock class="w-3.5 h-3.5" />
            <span>{{ ticket.elapsed_minutes }} min</span>
          </div>
        </div>

        <!-- Ordered Food Items -->
        <div class="p-4 space-y-2 flex-1">
          <div
            v-for="item in ticket.items"
            :key="item.id"
            class="flex items-start justify-between text-xs py-1 border-b border-stone-50 last:border-0"
          >
            <div>
              <span class="font-bold text-stone-900 text-sm mr-2">{{ item.quantity }}x</span>
              <span class="font-semibold text-stone-800">{{ item.item_name }}</span>
              <p v-if="item.notes" class="text-[11px] text-red-600 font-medium italic mt-0.5">Note: {{ item.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Preparation Action Buttons -->
        <div class="p-4 bg-stone-50 border-t border-stone-100 flex gap-2">
          <button
            v-if="ticket.status === 'new'"
            @click="updateTicketStatus(ticket.id, 'accepted')"
            class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition"
          >
            Accept / ተቀበል
          </button>

          <button
            v-if="ticket.status === 'accepted'"
            @click="updateTicketStatus(ticket.id, 'preparing')"
            class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition"
          >
            Start Preparing / አዘጋጅ
          </button>

          <button
            v-if="ticket.status === 'preparing' || ticket.status === 'accepted'"
            @click="updateTicketStatus(ticket.id, 'ready')"
            class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm"
          >
            Ready / ተጠናቋል ✔
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RefreshCw, Clock } from 'lucide-vue-next'
import { apiRequest } from '../services/api'

const tickets = ref([])
const loading = ref(false)
let timer = null

function getTicketBorderClass(elapsed, target) {
  if (elapsed > target) return 'border-red-400 bg-red-50/10'
  if (elapsed > target * 0.7) return 'border-amber-300'
  return 'border-stone-200'
}

async function loadTickets() {
  loading.value = true
  try {
    const res = await apiRequest('/kitchen/tickets')
    if (res.success && res.data) {
      tickets.value = res.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function updateTicketStatus(id, newStatus) {
  try {
    const res = await apiRequest(`/kitchen/tickets/${id}/status`, {
      method: 'PATCH',
      body: JSON.stringify({ status: newStatus }),
    })
    if (res.success) {
      await loadTickets()
    }
  } catch (err) {
    alert(err.message || 'Failed to update ticket.')
  }
}

onMounted(() => {
  loadTickets()
  timer = setInterval(loadTickets, 5000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
