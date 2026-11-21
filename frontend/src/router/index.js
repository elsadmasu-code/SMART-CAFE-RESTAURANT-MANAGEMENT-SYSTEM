import { createRouter, createWebHistory } from 'vue-router'
import CustomerLayout from '../layouts/CustomerLayout.vue'
import StaffLayout from '../layouts/StaffLayout.vue'

import HomeView from '../views/HomeView.vue'
import MenuView from '../views/MenuView.vue'
import CartView from '../views/CartView.vue'
import CheckoutView from '../views/CheckoutView.vue'
import OrderTrackingView from '../views/OrderTrackingView.vue'
import FeedbackView from '../views/FeedbackView.vue'

import DashboardView from '../views/DashboardView.vue'
import CashierView from '../views/CashierView.vue'
import KitchenView from '../views/KitchenView.vue'
import WaiterView from '../views/WaiterView.vue'
import InventoryView from '../views/InventoryView.vue'
import AttendanceView from '../views/AttendanceView.vue'
import FinanceView from '../views/FinanceView.vue'

const routes = [
  // Customer Facing Routes
  {
    path: '/',
    component: CustomerLayout,
    children: [
      { path: '', name: 'home', component: HomeView },
      { path: 'menu', name: 'menu', component: MenuView },
      { path: 'cart', name: 'cart', component: CartView },
      { path: 'checkout', name: 'checkout', component: CheckoutView },
      { path: 'order-tracking/:uuid?', name: 'order-tracking', component: OrderTrackingView },
      { path: 'feedback', name: 'feedback', component: FeedbackView },
    ],
  },

  // Staff & Management Facing Routes
  {
    path: '/staff',
    component: StaffLayout,
    children: [
      { path: '', redirect: '/staff/dashboard' },
      { path: 'dashboard', name: 'staff-dashboard', component: DashboardView },
      { path: 'cashier', name: 'staff-cashier', component: CashierView },
      { path: 'kitchen', name: 'staff-kitchen', component: KitchenView },
      { path: 'waiter', name: 'staff-waiter', component: WaiterView },
      { path: 'inventory', name: 'staff-inventory', component: InventoryView },
      { path: 'attendance', name: 'staff-attendance', component: AttendanceView },
      { path: 'finance', name: 'staff-finance', component: FinanceView },
    ],
  },

  // Fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
