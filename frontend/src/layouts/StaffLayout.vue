<template>
  <div class="min-h-screen bg-stone-100 flex flex-col md:flex-row">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-stone-900 text-stone-300 flex flex-col shrink-0 border-r border-stone-800">
      <!-- Staff Header -->
      <div class="p-5 border-b border-stone-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-cafe-700 flex items-center justify-center text-white font-bold">
            <Coffee class="w-5 h-5 text-amber-300" />
          </div>
          <div>
            <h2 class="font-bold text-white text-sm leading-tight">Fkr Staff Portal</h2>
            <p class="text-[11px] text-stone-400">Kombolcha Branch</p>
          </div>
        </div>
      </div>

      <!-- User Info & Role Badge -->
      <div class="p-4 bg-stone-800/60 border-b border-stone-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-cafe-600/30 text-amber-400 flex items-center justify-center font-bold text-sm border border-cafe-500/40">
          {{ auth.user?.name ? auth.user.name.charAt(0) : 'U' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-white truncate">{{ auth.user?.name || 'Staff User' }}</p>
          <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-400/20 text-amber-300 border border-amber-400/30">
            {{ currentActiveRole }}
          </span>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto text-sm font-medium">
        <router-link
          to="/staff/dashboard"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <LayoutDashboard class="w-4 h-4" />
          <span>Manager Dashboard</span>
        </router-link>

        <router-link
          to="/staff/cashier"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <Receipt class="w-4 h-4" />
          <span>Cashier POS</span>
        </router-link>

        <router-link
          to="/staff/kitchen"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <ChefHat class="w-4 h-4" />
          <span>Kitchen Display (KDS)</span>
        </router-link>

        <router-link
          to="/staff/waiter"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <BellRing class="w-4 h-4" />
          <span>Waiter & Service</span>
        </router-link>

        <router-link
          to="/staff/inventory"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <Boxes class="w-4 h-4" />
          <span>Inventory & Store</span>
        </router-link>

        <router-link
          to="/staff/attendance"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <UserCheck class="w-4 h-4" />
          <span>Attendance (Wi-Fi)</span>
        </router-link>

        <router-link
          to="/staff/finance"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-300 hover:bg-stone-800 hover:text-white"
          active-class="bg-cafe-700 text-white font-bold"
        >
          <DollarSign class="w-4 h-4" />
          <span>Financial Reports</span>
        </router-link>
      </nav>

      <!-- Sidebar Footer -->
      <div class="p-4 border-t border-stone-800 flex items-center justify-between">
        <router-link to="/" class="text-xs text-amber-300 hover:underline flex items-center gap-1">
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Customer View</span>
        </router-link>

        <button
          @click="handleLogout"
          class="text-xs text-stone-400 hover:text-red-400 transition flex items-center gap-1"
        >
          <LogOut class="w-3.5 h-3.5" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Staff Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      <!-- Top Action Bar -->
      <header class="bg-white border-b border-stone-200 px-6 py-3.5 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-medium border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Local Network: 192.168.1.0/24 (Online)</span>
          </div>
          <span class="text-xs text-stone-400 hidden sm:inline">• Fkr Cafe Edge Server</span>
        </div>

        <!-- Role Quick Switcher for Testing / Demonstration -->
        <div class="flex items-center gap-2 text-xs">
          <span class="text-stone-500 hidden md:inline">Simulate Role:</span>
          <select
            v-model="simulatedRole"
            class="bg-stone-50 border border-stone-300 rounded-lg px-2.5 py-1 text-stone-700 focus:outline-none focus:ring-1 focus:ring-cafe-500"
          >
            <option value="manager">Manager</option>
            <option value="cashier">Cashier</option>
            <option value="chef">Chef (KDS)</option>
            <option value="waiter">Waiter</option>
            <option value="storekeeper">Storekeeper</option>
          </select>
        </div>
      </header>

      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  Coffee,
  LayoutDashboard,
  Receipt,
  ChefHat,
  BellRing,
  Boxes,
  UserCheck,
  DollarSign,
  LogOut,
  ArrowLeft
} from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const simulatedRole = ref('manager')

const currentActiveRole = computed(() => {
  return simulatedRole.value || (auth.roles.length > 0 ? auth.roles[0] : 'Staff')
})

function handleLogout() {
  auth.logout()
  router.push('/')
}
</script>
