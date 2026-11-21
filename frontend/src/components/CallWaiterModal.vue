<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-stone-200">
      <div class="flex items-center justify-between pb-4 border-b border-stone-100">
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
            <BellRing class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-bold text-stone-900 text-lg">{{ locale.t('btn_call_waiter') }}</h3>
            <p class="text-xs text-stone-500">{{ locale.t('table_number') }}: {{ cart.tableNumber }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-2 text-stone-400 hover:text-stone-700 rounded-xl">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="py-5 space-y-3">
        <p class="text-sm font-medium text-stone-700">What service do you require? / ምን እንርዳዎት?</p>
        <div class="grid grid-cols-2 gap-2.5">
          <button
            v-for="opt in serviceOptions"
            :key="opt.type"
            type="button"
            @click="selectedType = opt.type"
            class="flex items-center gap-2 p-3 rounded-2xl border text-sm text-left font-medium transition"
            :class="selectedType === opt.type ? 'border-cafe-600 bg-cafe-50 text-cafe-800 ring-2 ring-cafe-500/20' : 'border-stone-200 hover:bg-stone-50 text-stone-700'"
          >
            <component :is="opt.icon" class="w-4 h-4 text-cafe-600" />
            <span>{{ opt.label }}</span>
          </button>
        </div>

        <div class="pt-2">
          <label class="text-xs font-medium text-stone-600 block mb-1">Optional Note / ተጨማሪ ማስታወሻ</label>
          <input
            v-model="customNotes"
            type="text"
            placeholder="e.g. Extra hot water, please"
            class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-cafe-500"
          />
        </div>
      </div>

      <div v-if="successMsg" class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
        {{ successMsg }}
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button
          @click="$emit('close')"
          class="flex-1 py-3 px-4 rounded-xl border border-stone-300 text-stone-700 font-medium hover:bg-stone-50 transition text-sm"
        >
          Cancel
        </button>
        <button
          @click="submitCall"
          :disabled="submitting"
          class="flex-1 py-3 px-4 rounded-xl bg-cafe-700 text-white font-medium hover:bg-cafe-800 transition text-sm shadow-md flex items-center justify-center gap-2"
        >
          <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
          <span>Call Waiter / ጥራ</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { BellRing, X, GlassWater, Receipt, UserCheck, UtensilsCrossed, Loader2 } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'
import { useLocaleStore } from '../stores/locale'
import { apiRequest } from '../services/api'

defineProps({
  isOpen: Boolean,
})
const emit = defineEmits(['close'])

const cart = useCartStore()
const locale = useLocaleStore()

const selectedType = ref('waiter_call')
const customNotes = ref('')
const submitting = ref(false)
const successMsg = ref('')

const serviceOptions = [
  { type: 'waiter_call', label: 'Call Waiter / አስተናጋጅ', icon: UserCheck },
  { type: 'water', label: 'Water / ውሃ', icon: GlassWater },
  { type: 'bill', label: 'Bill / ሂሳብ አምጣ', icon: Receipt },
  { type: 'napkins', label: 'Napkins / ወረቀት', icon: UtensilsCrossed },
]

async function submitCall() {
  submitting.value = true
  successMsg.value = ''
  try {
    const res = await apiRequest('/waiter/service-call', {
      method: 'POST',
      body: JSON.stringify({
        table_id: parseInt(cart.tableId) || 1,
        request_type: selectedType.value,
        notes: customNotes.value,
      }),
    })

    if (res.success) {
      successMsg.value = 'Waiter notified! An attendant will reach your table shortly.'
      setTimeout(() => {
        emit('close')
        successMsg.value = ''
        customNotes.value = ''
      }, 1500)
    }
  } catch (err) {
    alert(err.message || 'Failed to dispatch request.')
  } finally {
    submitting.value = false
  }
}
</script>
