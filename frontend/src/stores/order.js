import { defineStore } from 'pinia'
import { apiRequest } from '../services/api'
import { useCartStore } from './cart'

export const useOrderStore = defineStore('order', {
  state: () => ({
    activeOrderUuid: localStorage.getItem('scrms_active_order_uuid') || null,
    currentOrder: null,
    loading: false,
    submitting: false,
    error: null,
  }),
  actions: {
    async placeOrder() {
      const cart = useCartStore()
      if (cart.items.length === 0) {
        throw new Error('Your cart is empty.')
      }

      this.submitting = true
      this.error = null

      const payload = {
        order_type: cart.orderType,
        table_id: cart.orderType === 'dine_in' ? parseInt(cart.tableId) : null,
        guest_name: cart.guestName || 'Valued Guest',
        guest_phone: cart.guestPhone || '',
        delivery_address: cart.deliveryAddress || '',
        notes: cart.notes,
        items: cart.items.map(item => ({
          product_id: item.productId,
          quantity: item.quantity,
          variant_id: item.variantId,
          addon_ids: item.addons.map(a => a.id),
          special_instructions: item.specialInstructions,
        })),
      }

      try {
        const idempotencyKey = 'order-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9)
        const res = await apiRequest('/orders', {
          method: 'POST',
          headers: {
            'X-Idempotency-Key': idempotencyKey,
          },
          body: JSON.stringify(payload),
        })

        if (res.success && res.data) {
          this.activeOrderUuid = res.data.order_uuid
          localStorage.setItem('scrms_active_order_uuid', this.activeOrderUuid)
          cart.clearCart()
          await this.fetchOrderStatus(this.activeOrderUuid)
          return res.data
        }
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.submitting = false
      }
    },

    async submitPayment(method, reference, amount) {
      if (!this.activeOrderUuid) throw new Error('No active order.')
      this.submitting = true

      try {
        const res = await apiRequest('/payments/submit', {
          method: 'POST',
          body: JSON.stringify({
            order_uuid: this.activeOrderUuid,
            payment_method: method,
            transaction_reference: reference,
            amount: parseFloat(amount),
          }),
        })

        if (res.success) {
          await this.fetchOrderStatus(this.activeOrderUuid)
          return res.data
        }
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.submitting = false
      }
    },

    async fetchOrderStatus(uuid) {
      const targetUuid = uuid || this.activeOrderUuid
      if (!targetUuid) return null

      this.loading = true
      try {
        const res = await apiRequest(`/orders/${targetUuid}`)
        if (res.success && res.data) {
          this.currentOrder = res.data
          return res.data
        }
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },
  },
})
