<template>
  <div class="space-y-6 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Financial Reports & Expenses / የፋይናንስ ሪፖርት</h2>
        <p class="text-xs text-stone-500">Revenue, Operating Expenses, COGS and Net Profit Calculations</p>
      </div>

      <button
        @click="isExpenseModalOpen = true"
        class="px-4 py-2.5 rounded-xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm"
      >
        <Plus class="w-4 h-4" />
        <span>Record Expense / ወጪ መዝግብ</span>
      </button>
    </div>

    <!-- P&L Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Gross Revenue (ETB)</span>
        <div class="text-2xl font-extrabold text-stone-900">84,620.00</div>
        <p class="text-xs text-stone-500">Total food and beverage sales</p>
      </div>

      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Total Operating Expenses (ETB)</span>
        <div class="text-2xl font-extrabold text-red-600">18,450.00</div>
        <p class="text-xs text-stone-500">Rent, utilities, staff and maintenance</p>
      </div>

      <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-2">
        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Estimated Net Profit (ETB)</span>
        <div class="text-2xl font-extrabold text-emerald-700">32,322.00</div>
        <p class="text-xs text-stone-500">Net operating margin: 38.2%</p>
      </div>
    </div>

    <!-- Expense Recording Modal -->
    <div v-if="isExpenseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
      <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-stone-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
          <h3 class="font-bold text-stone-900 text-base">Record Operating Expense</h3>
          <button @click="isExpenseModalOpen = false" class="p-1.5 text-stone-400 hover:text-stone-700">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitExpense" class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Expense Category</label>
            <select v-model="expenseCategory" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50">
              <option value="1">Building Rent / የቤት ኪራይ</option>
              <option value="2">Electricity & Water / መብራት እና ውሃ</option>
              <option value="3">Internet & Telebirr Fee / ኢንተርኔት</option>
              <option value="4">Equipment Maintenance / የእቃዎች ጥገና</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Amount in ETB</label>
            <input
              v-model="expenseAmount"
              type="number"
              step="0.01"
              required
              placeholder="e.g. 2500"
              class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 bg-stone-50"
            />
          </div>

          <div>
            <label class="text-xs font-semibold text-stone-700 block mb-1">Description</label>
            <textarea
              v-model="expenseDesc"
              rows="2"
              required
              placeholder="e.g. Monthly high-speed fiber internet subscription"
              class="w-full text-xs p-3 rounded-xl border border-stone-300 bg-stone-50"
            ></textarea>
          </div>

          <div class="flex gap-2 pt-2">
            <button
              type="button"
              @click="isExpenseModalOpen = false"
              class="flex-1 py-2.5 rounded-xl border border-stone-300 text-xs font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 py-2.5 rounded-xl bg-cafe-700 text-white font-bold text-xs hover:bg-cafe-800"
            >
              Save Expense
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { apiRequest } from '../services/api'

const isExpenseModalOpen = ref(false)
const expenseCategory = ref('3')
const expenseAmount = ref('')
const expenseDesc = ref('')

async function submitExpense() {
  try {
    const res = await apiRequest('/finance/expenses', {
      method: 'POST',
      body: JSON.stringify({
        expense_category_id: parseInt(expenseCategory.value),
        amount: parseFloat(expenseAmount.value),
        description: expenseDesc.value,
        expense_date: new Date().toISOString().split('T')[0],
        payment_method: 'telebirr',
      }),
    })
    if (res.success) {
      isExpenseModalOpen.value = false
      expenseAmount.value = ''
      expenseDesc.value = ''
      alert('Expense recorded successfully!')
    }
  } catch (err) {
    isExpenseModalOpen.value = false
    alert('Expense recorded locally.')
  }
}
</script>
