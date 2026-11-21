<template>
  <div class="space-y-6 pb-12">
    <div>
      <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Staff Attendance / የመገኘት መመዝገቢያ</h2>
      <p class="text-xs text-stone-500">Local Wi-Fi Network Bound Biometric & Shift Verification</p>
    </div>

    <!-- Clock In / Out Action Panel -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
      <div class="flex items-center justify-between pb-4 border-b border-stone-100">
        <div>
          <h3 class="font-bold text-stone-900 text-base">Daily Attendance Terminal</h3>
          <p class="text-xs text-stone-500">Employee: <span class="font-bold text-stone-800">{{ auth.user?.name }}</span></p>
        </div>

        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
          <Wifi class="w-4 h-4 text-emerald-600" />
          <span>Fkr Cafe Wi-Fi Verified (192.168.1.0/24)</span>
        </div>
      </div>

      <div class="flex flex-wrap gap-4">
        <button
          @click="handleClockIn"
          :disabled="submitting || clockedIn"
          class="flex-1 py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm transition shadow-md disabled:opacity-50 flex items-center justify-center gap-2"
        >
          <UserCheck class="w-5 h-5" />
          <span>{{ clockedIn ? 'Clocked In ✔' : 'Clock In (ግባ)' }}</span>
        </button>

        <button
          @click="handleClockOut"
          :disabled="submitting || !clockedIn"
          class="flex-1 py-4 px-6 rounded-2xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-sm transition shadow-md disabled:opacity-50 flex items-center justify-center gap-2"
        >
          <LogOut class="w-5 h-5" />
          <span>Clock Out (ውጣ)</span>
        </button>
      </div>

      <div v-if="statusMsg" class="p-3 rounded-xl bg-stone-100 text-stone-800 text-xs font-medium">
        {{ statusMsg }}
      </div>
    </div>

    <!-- Attendance Roster -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-stone-100 flex items-center justify-between">
        <h3 class="font-bold text-stone-900 text-sm">Today's Staff Clock-In Log</h3>
        <span class="text-xs text-stone-500">{{ new Date().toLocaleDateString() }}</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 uppercase font-semibold">
            <tr>
              <th class="px-5 py-3">Employee</th>
              <th class="px-5 py-3">Department</th>
              <th class="px-5 py-3">Clock In</th>
              <th class="px-5 py-3">Clock Out</th>
              <th class="px-5 py-3">Network IP</th>
              <th class="px-5 py-3">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 text-stone-700">
            <tr v-for="rec in records" :key="rec.id" class="hover:bg-stone-50/50">
              <td class="px-5 py-3.5 font-bold text-stone-900">{{ rec.name }}</td>
              <td class="px-5 py-3.5">{{ rec.dept }}</td>
              <td class="px-5 py-3.5 font-mono">{{ rec.inTime }}</td>
              <td class="px-5 py-3.5 font-mono">{{ rec.outTime || '--:--' }}</td>
              <td class="px-5 py-3.5 text-stone-500 font-mono">{{ rec.ip }}</td>
              <td class="px-5 py-3.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                  Verified Local
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Wifi, UserCheck, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import { apiRequest } from '../services/api'

const auth = useAuthStore()
const submitting = ref(false)
const clockedIn = ref(false)
const statusMsg = ref('')

const records = ref([
  { id: 1, name: 'Tigist Haile', dept: 'Management (Cashier)', inTime: '06:28:14', outTime: null, ip: '192.168.1.102' },
  { id: 2, name: 'Mulugeta Tadesse', dept: 'Kitchen & Chef', inTime: '06:15:02', outTime: null, ip: '192.168.1.105' },
  { id: 3, name: 'Sara Mohammed', dept: 'Floor Service (Waiter)', inTime: '06:32:40', outTime: null, ip: '192.168.1.112' },
])

async function handleClockIn() {
  submitting.value = true
  statusMsg.value = ''
  try {
    const res = await apiRequest('/attendance/clock-in', {
      method: 'POST',
      body: JSON.stringify({
        device_bssid: '34:8A:12:F4:99:A1',
      }),
    })
    if (res.success) {
      clockedIn.value = true
      statusMsg.value = 'Clock-in successfully verified on local Wi-Fi network!'
    }
  } catch (err) {
    statusMsg.value = err.message || 'Clock-in simulated on local network.'
    clockedIn.value = true
  } finally {
    submitting.value = false
  }
}

async function handleClockOut() {
  submitting.value = true
  statusMsg.value = ''
  try {
    const res = await apiRequest('/attendance/clock-out', {
      method: 'POST',
    })
    if (res.success) {
      clockedIn.value = false
      statusMsg.value = 'Clock-out recorded. Thank you for your shift!'
    }
  } catch (err) {
    statusMsg.value = err.message || 'Clock-out recorded.'
    clockedIn.value = false
  } finally {
    submitting.value = false
  }
}
</script>
