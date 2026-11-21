import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: JSON.parse(localStorage.getItem('scrms_cart') || '[]'),
    orderType: localStorage.getItem('scrms_order_type') || 'dine_in',
    tableId: localStorage.getItem('scrms_table_id') || '1',
    tableNumber: localStorage.getItem('scrms_table_number') || 'T-1',
    guestName: '',
    guestPhone: '',
    deliveryAddress: '',
    notes: '',
  }),
  getters: {
    itemCount: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    subtotal: (state) => state.items.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0),
    vatAmount: (state) => Math.round(state.subtotal * 0.15 * 100) / 100,
    serviceAmount: (state) => Math.round(state.subtotal * 0.05 * 100) / 100,
    totalAmount: (state) => Math.round((state.subtotal + state.vatAmount + state.serviceAmount) * 100) / 100,
  },
  actions: {
    setTable(id, number) {
      this.tableId = id
      this.tableNumber = number
      localStorage.setItem('scrms_table_id', id)
      localStorage.setItem('scrms_table_number', number)
    },
    setOrderType(type) {
      this.orderType = type
      localStorage.setItem('scrms_order_type', type)
    },
    addItem(product, quantity = 1, variant = null, selectedAddons = [], instructions = '') {
      let unitPrice = product.price
      if (variant) {
        unitPrice += variant.price_modifier
      }
      for (const addon of selectedAddons) {
        unitPrice += addon.extra_price
      }

      const cartKey = `${product.id}-${variant?.id || 'base'}-${selectedAddons.map(a => a.id).sort().join(',')}`
      const existing = this.items.find(i => i.key === cartKey)

      if (existing) {
        existing.quantity += quantity
      } else {
        this.items.push({
          key: cartKey,
          productId: product.id,
          name: product.name,
          unitPrice,
          quantity,
          variantId: variant?.id || null,
          variantName: variant?.name || null,
          addons: selectedAddons,
          specialInstructions: instructions,
          imageUrl: product.image_url,
        })
      }
      this.save()
    },
    updateQuantity(key, quantity) {
      if (quantity <= 0) {
        this.removeItem(key)
        return
      }
      const item = this.items.find(i => i.key === key)
      if (item) {
        item.quantity = quantity
        this.save()
      }
    },
    removeItem(key) {
      this.items = this.items.filter(i => i.key !== key)
      this.save()
    },
    clearCart() {
      this.items = []
      this.save()
    },
    save() {
      localStorage.setItem('scrms_cart', JSON.stringify(this.items))
    },
  },
})
