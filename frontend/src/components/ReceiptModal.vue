<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in">
    <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl border border-stone-200 font-mono text-xs">
      <div class="text-center pb-4 border-b border-dashed border-stone-300">
        <h2 class="text-base font-bold uppercase tracking-wider text-stone-900">Fkr Cafe / ፍቅር ካፌ</h2>
        <p class="text-stone-600">Kombolcha, Amhara, Ethiopia</p>
        <p class="text-stone-500">TIN: 0049281729 • Tel: +251 33 551 2345</p>
        <p class="mt-2 text-stone-700 font-bold">OFFICIAL RECEIPT / ደረሰኝ</p>
      </div>

      <div class="py-3 border-b border-dashed border-stone-300 space-y-1">
        <div class="flex justify-between">
          <span class="text-stone-500">Receipt No:</span>
          <span class="font-bold text-stone-900">{{ receipt?.receipt_number || 'REC-20261121-001' }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-500">Order No:</span>
          <span>{{ receipt?.order?.order_number || order?.order_number || 'FKR-001' }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-500">Date & Time:</span>
          <span>{{ receipt?.created_at || new Date().toLocaleString() }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-500">Payment Method:</span>
          <span class="uppercase font-bold text-cafe-700">{{ receipt?.payment_method_snapshot || 'TELEBIRR' }}</span>
        </div>
      </div>

      <!-- Order Items Snapshot -->
      <div class="py-3 border-b border-dashed border-stone-300 space-y-1.5 max-h-48 overflow-y-auto">
        <div v-for="item in (receipt?.order?.items || order?.items || [])" :key="item.id" class="flex justify-between">
          <span class="truncate max-w-[180px]">{{ item.product_name_snapshot }} x{{ item.quantity }}</span>
          <span class="font-medium">{{ item.total_price }} ETB</span>
        </div>
      </div>

      <!-- Financial Totals -->
      <div class="py-3 border-b border-dashed border-stone-300 space-y-1">
        <div class="flex justify-between">
          <span>Subtotal:</span>
          <span>{{ receipt?.order?.subtotal || order?.subtotal || 0 }} ETB</span>
        </div>
        <div class="flex justify-between text-stone-600">
          <span>VAT (15%):</span>
          <span>{{ receipt?.order?.tax_amount || order?.tax_amount || 0 }} ETB</span>
        </div>
        <div class="flex justify-between text-stone-600">
          <span>Service Charge (5%):</span>
          <span>{{ receipt?.order?.service_charge || order?.service_charge || 0 }} ETB</span>
        </div>
        <div class="flex justify-between text-sm font-bold text-stone-900 pt-1 border-t border-stone-200">
          <span>TOTAL PAID:</span>
          <span>{{ receipt?.total_amount || order?.total_amount || 0 }} ETB</span>
        </div>
      </div>

      <div class="text-center pt-4 text-stone-500 text-[11px] space-y-1">
        <p>Thank you for dining at Fkr Cafe!</p>
        <p>ስለጎበኙን እናመሰግናለን!</p>
      </div>

      <div class="mt-5 flex gap-2">
        <button
          @click="printReceipt"
          class="flex-1 py-2.5 px-4 rounded-xl bg-cafe-700 text-white font-medium hover:bg-cafe-800 transition text-center"
        >
          Print / አትም
        </button>
        <button
          @click="$emit('close')"
          class="py-2.5 px-4 rounded-xl border border-stone-300 text-stone-700 font-medium hover:bg-stone-50 transition"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isOpen: Boolean,
  receipt: Object,
  order: Object,
})

defineEmits(['close'])

function printReceipt() {
  window.print()
}
</script>
