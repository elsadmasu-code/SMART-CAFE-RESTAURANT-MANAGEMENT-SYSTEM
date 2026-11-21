import { defineStore } from 'pinia'
import { apiRequest } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('scrms_user') || 'null'),
    token: localStorage.getItem('scrms_token') || null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    roles: (state) => state.user?.roles || [],
    isManager: (state) => state.user?.roles?.includes('manager'),
    isCashier: (state) => state.user?.roles?.includes('cashier'),
    isChef: (state) => state.user?.roles?.includes('chef'),
    isWaiter: (state) => state.user?.roles?.includes('waiter'),
    isStorekeeper: (state) => state.user?.roles?.includes('storekeeper'),
  },
  actions: {
    async login(emailOrPhone, password) {
      this.loading = true
      this.error = null
      try {
        const res = await apiRequest('/auth/login', {
          method: 'POST',
          body: JSON.stringify({
            email_or_phone: emailOrPhone,
            password: password,
            device_name: 'Web Browser Client',
          }),
        })

        if (res.success && res.data) {
          this.token = res.data.token
          this.user = res.data.user
          localStorage.setItem('scrms_token', this.token)
          localStorage.setItem('scrms_user', JSON.stringify(this.user))
          return res.data
        }
      } catch (err) {
        this.error = err.message || 'Login failed.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchProfile() {
      if (!this.token) return
      try {
        const res = await apiRequest('/auth/profile')
        if (res.success && res.data) {
          this.user = {
            id: res.data.id,
            uuid: res.data.uuid,
            name: res.data.name,
            email: res.data.email,
            phone: res.data.phone,
            roles: res.data.roles?.map(r => r.slug) || [],
          }
          localStorage.setItem('scrms_user', JSON.stringify(this.user))
        }
      } catch (err) {
        if (err.status === 401) {
          this.logout()
        }
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('scrms_token')
      localStorage.removeItem('scrms_user')
    },
  },
})
