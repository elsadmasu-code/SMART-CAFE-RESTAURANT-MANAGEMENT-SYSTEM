import { defineStore } from 'pinia'
import { apiRequest } from '../services/api'

export const useMenuStore = defineStore('menu', {
  state: () => ({
    categories: [],
    cafeStatus: {
      is_open: true,
      open_time: '06:30',
      close_time: '22:30',
      currency: 'ETB',
      branch: 'Fkr Cafe, Kombolcha',
    },
    activeCategory: null,
    searchQuery: '',
    fastingOnly: false,
    loading: false,
    error: null,
  }),
  getters: {
    filteredCategories: (state) => {
      return state.categories.map(cat => {
        let prods = cat.products
        if (state.fastingOnly) {
          prods = prods.filter(p => p.is_fasting)
        }
        if (state.searchQuery.trim()) {
          const q = state.searchQuery.toLowerCase()
          prods = prods.filter(p =>
            p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q))
          )
        }
        return {
          ...cat,
          products: prods,
        }
      }).filter(cat => cat.products.length > 0)
    },
  },
  actions: {
    async fetchMenu(locale = 'am') {
      this.loading = true
      this.error = null
      try {
        const res = await apiRequest(`/menu?locale=${locale}`)
        if (res.success && res.data) {
          this.categories = res.data.categories
          if (res.data.cafe_status) {
            this.cafeStatus = res.data.cafe_status
          }
          if (!this.activeCategory && this.categories.length > 0) {
            this.activeCategory = this.categories[0].id
          }
        }
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },
  },
})
