import { defineStore } from 'pinia'
import en from '../locales/en.json'
import am from '../locales/am.json'

const messages = { en, am }

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    currentLocale: localStorage.getItem('scrms_locale') || 'am',
  }),
  actions: {
    setLocale(locale) {
      if (messages[locale]) {
        this.currentLocale = locale
        localStorage.setItem('scrms_locale', locale)
        document.documentElement.lang = locale
      }
    },
    t(key) {
      return messages[this.currentLocale]?.[key] || messages['en']?.[key] || key
    },
  },
})
