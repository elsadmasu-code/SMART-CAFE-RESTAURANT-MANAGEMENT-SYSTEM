<template>
  <div class="space-y-6 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Inventory & Store / የእቃ ግምጃ ቤት</h2>
        <p class="text-xs text-stone-500">Raw Ingredients, Low-Stock Tracking & Wastage Ledger</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="isPurchaseModalOpen = true"
          class="px-4 py-2.5 rounded-xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm"
        >
          <Plus class="w-4 h-4" />
          <span>Record Purchase / ግዢ መዝግብ</span>
        </button>
      </div>
    </div>

    <!-- Ingredients Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <h3 class="font-bold text-stone-900 text-sm">Ingredient Stock Levels</h3>
        <span class="text-xs text-stone-500 font-medium">Automatic recipe deduction enabled</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase font-semibold">
            <tr>
              <th class="px-5 py-3">Ingredient</th>
              <th class="px-5 py-3">Current Stock</th>
              <th class="px-5 py-3">Unit</th>
              <th class="px-5 py-3">Safety Min</th>
              <th class="px-5 py-3">Unit Cost</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 text-stone-700">
            <tr v-for="item in ingredients" :key="item.id" class="hover:bg-stone-50/50 transition">
              <td class="px-5 py-3.5 font-bold text-stone-900">
                {{ item.name_en }}
                <span class="block text-[11px] font-normal text-stone-500">{{ item.name_am }}</span>
              </td>
              <td class="px-5 py-3.5 font-extrabold text-sm" :class="item.is_low_stock ? 'text-red-600' : 'text-stone-900'">
                {{ item.current_stock }}
              </td>
              <td class="px-5 py-3.5">{{ item.unit }}</td>
              <td class="px-5 py-3.5">{{ item.minimum_stock }}</td>
              <td class="px-5 py-3.5">{{ item.cost_per_unit }} ETB</td>
              <td class="px-5 py-3.5">
                <span
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase"
                  :class="item.is_low_stock ? 'bg-red-100 text-red-800 animate-pulse' : 'bg-emerald-100 text-emerald-800'"
                >
                  {{ item.is_low_stock ? 'Low Stock' : 'Sufficient' }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <button
                  @click="openWastageModal(item)"
                  class="text-xs text-red-600 hover:underline font-semibold"
                >
                  Log Wastage
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Wastage Modal -->
    <div v-if="selectedWastageItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
      <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl border border-stone-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
          <h3 class="font-bold text-stone-900 text-sm">Log Wastage / ብልሽት መዝግብ</h3>
          <button @click="selectedWastageItem = null" class="p-1.5 text-stone-400 hover:text-stone-700">
            <X class="w-4 h-4" />
          </button>
        </div>

        <p class="text-xs text-stone-600">Ingredient: <span class="font-bold">{{ selectedWastageItem.name_en }}</span></p>

        <div class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Quantity Lost ({{ selectedWastageItem.unit }})</label>
            <input
              v-model="wastageQty"
              type="number"
              step="0.01"
              required
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50"
            />
          </div>

          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Reason Code</label>
            <select v-model="wastageReason" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50">
              <option value="expired">Expired / ጊዜው ያለፈበት</option>
              <option value="burned">Burned / ያረረ</option>
              <option value="spilled">Spilled / የፈሰሰ</option>
              <option value="spoiled">Spoiled / የተበላሸ</option>
            </select>
          </div>
        </div>

        <div class="pt-2 flex gap-2">
          <button @click="selectedWastageItem = null" class="flex-1 py-2.5 rounded-xl border border-stone-300 text-xs font-medium">Cancel</button>
          <button @click="submitWastage" class="flex-1 py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700">Record Loss</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { apiRequest } from '../services/api'

const ingredients = ref([])
const isPurchaseModalOpen = ref(false)
const selectedWastageItem = ref(null)
const wastageQty = ref(1)
const wastageReason = ref('spoiled')

async function loadIngredients() {
  try {
    const res = await apiRequest('/inventory/ingredients')
    if (res.success && res.data) {
      ingredients.value = res.data
    }
  } catch (err) {
    console.error(err)
  }
}

function openWastageModal(item) {
  selectedWastageItem.value = item
  wastageQty.value = 1
}

async function submitWastage() {
  if (!selectedWastageItem.value) return
  try {
    const res = await apiRequest('/inventory/wastage', {
      method: 'POST',
      body: JSON.stringify({
        ingredient_id: selectedWastageItem.value.id,
        wasted_quantity: parseFloat(wastageQty.value),
        reason_code: wastageReason.value,
      }),
    })
    if (res.success) {
      selectedWastageItem.value = null
      await loadIngredients()
    }
  } catch (err) {
    alert(err.message || 'Failed to record wastage.')
  }
}

onMounted(() => {
  loadIngredients()
})
</script>
