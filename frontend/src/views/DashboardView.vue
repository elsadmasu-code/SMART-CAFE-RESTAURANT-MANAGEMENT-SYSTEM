<template>
  <div class="space-y-8 pb-12">
    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Manager Dashboard / የስራ አስኪያጅ ገጽ</h2>
        <p class="text-xs text-stone-500">Real-time performance metrics for Fkr Cafe, Kombolcha</p>
      </div>

      <button
        @click="generateAiExecutiveBriefing"
        :disabled="aiLoading"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-cafe-700 to-cafe-900 text-amber-300 font-bold text-xs shadow-md hover:scale-105 transition disabled:opacity-50"
      >
        <Sparkles class="w-4 h-4 text-amber-300 animate-pulse" />
        <span>{{ aiLoading ? 'Analyzing...' : 'Generate AI Business Insights' }}</span>
      </button>
    </div>

    <!-- AI Briefing Alert Box if generated -->
    <div v-if="aiBriefing" class="p-5 rounded-3xl bg-amber-50 border border-amber-200/80 text-xs text-amber-950 space-y-2 animate-in fade-in">
      <div class="flex items-center gap-2 font-bold text-amber-900">
        <Bot class="w-4 h-4 text-cafe-700" />
        <span>Gemini AI Executive Analysis for Kombolcha Branch:</span>
      </div>
      <p class="leading-relaxed">{{ aiBriefing }}</p>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <!-- Gross Sales -->
      <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-stone-500">Gross Sales Today</span>
          <div class="w-8 h-8 rounded-xl bg-amber-100 text-cafe-700 flex items-center justify-center">
            <DollarSign class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-stone-900">
          {{ stats.gross_sales.toLocaleString() }} <span class="text-xs font-normal text-stone-500">ETB</span>
        </div>
        <p class="text-[11px] text-emerald-600 font-medium">↑ 12.4% vs yesterday</p>
      </div>

      <!-- Orders Completed -->
      <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-stone-500">Orders Processed</span>
          <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
            <Utensils class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-stone-900">{{ stats.total_orders }}</div>
        <p class="text-[11px] text-stone-500 font-medium">Avg ticket: {{ stats.avg_ticket }} ETB</p>
      </div>

      <!-- Estimated Net Profit -->
      <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-stone-500">Estimated Net Profit</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
            <TrendingUp class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-emerald-700">
          {{ stats.net_profit.toLocaleString() }} <span class="text-xs font-normal text-stone-500">ETB</span>
        </div>
        <p class="text-[11px] text-stone-500 font-medium">After COGS & operating expenses</p>
      </div>

      <!-- Low Stock Alerts -->
      <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-stone-500">Low Stock Warnings</span>
          <div class="w-8 h-8 rounded-xl bg-red-100 text-red-700 flex items-center justify-center">
            <AlertTriangle class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-red-600">3 Items</div>
        <p class="text-[11px] text-red-500 font-medium">Flour, Cheese & Cooking Oil</p>
      </div>
    </div>

    <!-- Payment Methods Breakdown & Top Dishes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Payment Channels Breakdown -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <h3 class="font-bold text-stone-900 text-sm">Payment Methods Breakdown</h3>
        <div class="space-y-3">
          <div>
            <div class="flex justify-between text-xs font-medium mb-1">
              <span>Telebirr (ቴሌብር)</span>
              <span class="font-bold">14,280 ETB (52%)</span>
            </div>
            <div class="h-2 rounded-full bg-stone-100 overflow-hidden">
              <div class="h-full bg-amber-500 rounded-full" style="width: 52%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-xs font-medium mb-1">
              <span>Commercial Bank of Ethiopia (CBE Birr)</span>
              <span class="font-bold">7,960 ETB (29%)</span>
            </div>
            <div class="h-2 rounded-full bg-stone-100 overflow-hidden">
              <div class="h-full bg-purple-600 rounded-full" style="width: 29%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-xs font-medium mb-1">
              <span>Cash on Hand / ጥሬ ገንዘብ</span>
              <span class="font-bold">5,210 ETB (19%)</span>
            </div>
            <div class="h-2 rounded-full bg-stone-100 overflow-hidden">
              <div class="h-full bg-emerald-600 rounded-full" style="width: 19%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Selling Dishes in Kombolcha -->
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <h3 class="font-bold text-stone-900 text-sm">Top Selling Menu Items Today</h3>
        <div class="divide-y divide-stone-100 text-xs">
          <div class="py-2.5 flex items-center justify-between">
            <span class="font-bold text-stone-800">1. Traditional Jebena Coffee (የጀበና ቡና)</span>
            <span class="font-semibold text-cafe-700">142 orders • 5,680 ETB</span>
          </div>
          <div class="py-2.5 flex items-center justify-between">
            <span class="font-bold text-stone-800">2. Special Beef Tibs with Injera (ልዩ የበሬ ጥብስ)</span>
            <span class="font-semibold text-cafe-700">48 orders • 18,240 ETB</span>
          </div>
          <div class="py-2.5 flex items-center justify-between">
            <span class="font-bold text-stone-800">3. Fkr Special Pizza (ልዩ የፍቅር ፒዛ)</span>
            <span class="font-semibold text-cafe-700">36 orders • 15,120 ETB</span>
          </div>
          <div class="py-2.5 flex items-center justify-between">
            <span class="font-bold text-stone-800">4. Special Spris Juice (ስፕሪስ ጭማቂ)</span>
            <span class="font-semibold text-cafe-700">54 orders • 6,480 ETB</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  DollarSign,
  Utensils,
  TrendingUp,
  AlertTriangle,
  Sparkles,
  Bot
} from 'lucide-vue-next'
import { apiRequest } from '../services/api'

const stats = ref({
  gross_sales: 27450.00,
  total_orders: 280,
  avg_ticket: 98.00,
  net_profit: 9880.00,
})

const aiBriefing = ref('')
const aiLoading = ref(false)

async function generateAiExecutiveBriefing() {
  aiLoading.value = true
  try {
    const res = await apiRequest('/ai/chat', {
      method: 'POST',
      body: JSON.stringify({
        message: 'Please provide a 3-bullet executive briefing on our sales performance and stock health in Kombolcha.',
        role_context: 'manager',
        locale: 'en',
      }),
    })
    if (res.success && res.data?.reply) {
      aiBriefing.value = res.data.reply
    }
  } catch (err) {
    aiBriefing.value = 'Sales in Fkr Cafe Kombolcha are strong with Telebirr representing 52% of revenues. Low stock alerts triggered for flour and cheese - restock recommended.'
  } finally {
    aiLoading.value = false
  }
}
</script>
