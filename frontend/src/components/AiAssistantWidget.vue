<template>
  <div>
    <!-- Floating Trigger Button -->
    <button
      @click="isOpen = !isOpen"
      class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-gradient-to-r from-cafe-600 to-cafe-800 text-white px-4 py-3 rounded-full shadow-2xl hover:scale-105 transition transform active:scale-95 border-2 border-amber-400"
      :aria-label="locale.t('ai_assistant_title')"
    >
      <div class="relative">
        <Sparkles class="w-5 h-5 text-amber-300 animate-pulse" />
        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full"></span>
      </div>
      <span class="font-medium text-sm hidden sm:inline">{{ locale.t('ai_assistant_title') }}</span>
    </button>

    <!-- Chat Modal Window -->
    <div
      v-if="isOpen"
      class="fixed bottom-20 right-4 sm:right-6 w-96 max-w-[calc(100vw-2rem)] h-[520px] bg-white rounded-2xl shadow-2xl flex flex-col z-50 border border-stone-200 overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-200"
    >
      <!-- Chat Header -->
      <div class="bg-cafe-800 text-white p-4 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-full bg-cafe-700 flex items-center justify-center border border-amber-400/40">
            <Bot class="w-5 h-5 text-amber-300" />
          </div>
          <div>
            <h3 class="font-semibold text-sm leading-tight">{{ locale.t('ai_assistant_title') }}</h3>
            <span class="text-xs text-amber-200/80">Powered by Gemini AI • Fkr Cafe</span>
          </div>
        </div>
        <button @click="isOpen = false" class="text-stone-300 hover:text-white p-1 rounded-lg">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Messages Area -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-stone-50 text-sm">
        <div
          v-for="(msg, index) in messages"
          :key="index"
          class="flex"
          :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[85%] rounded-2xl px-3.5 py-2.5 shadow-sm leading-relaxed"
            :class="msg.sender === 'user' ? 'bg-cafe-600 text-white rounded-br-none' : 'bg-white text-stone-800 border border-stone-200 rounded-bl-none'"
          >
            {{ msg.text }}
          </div>
        </div>

        <div v-if="loading" class="flex justify-start">
          <div class="bg-white border border-stone-200 rounded-2xl px-4 py-2.5 text-stone-500 flex items-center gap-2">
            <Loader2 class="w-4 h-4 animate-spin text-cafe-600" />
            <span class="text-xs">Thinking...</span>
          </div>
        </div>
      </div>

      <!-- Input Field -->
      <form @submit.prevent="sendMessage" class="p-3 bg-white border-t border-stone-200 flex items-center gap-2">
        <input
          v-model="inputQuery"
          type="text"
          :placeholder="locale.t('ai_assistant_placeholder')"
          class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-cafe-500 focus:border-cafe-500 bg-stone-50"
          :disabled="loading"
        />
        <button
          type="submit"
          :disabled="!inputQuery.trim() || loading"
          class="p-2.5 bg-cafe-700 text-white rounded-xl hover:bg-cafe-800 transition disabled:opacity-50"
        >
          <Send class="w-4 h-4" />
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Sparkles, Bot, X, Send, Loader2 } from 'lucide-vue-next'
import { useLocaleStore } from '../stores/locale'
import { apiRequest } from '../services/api'

const locale = useLocaleStore()
const isOpen = ref(false)
const inputQuery = ref('')
const loading = ref(false)
const messagesContainer = ref(null)

const messages = ref([
  {
    sender: 'assistant',
    text: locale.t('ai_assistant_welcome'),
  },
])

async function sendMessage() {
  const query = inputQuery.value.trim()
  if (!query || loading.value) return

  messages.value.push({ sender: 'user', text: query })
  inputQuery.value = ''
  loading.value = true
  scrollToBottom()

  try {
    const res = await apiRequest('/ai/chat', {
      method: 'POST',
      body: JSON.stringify({
        message: query,
        locale: locale.currentLocale,
        role_context: 'customer',
      }),
    })

    if (res.success && res.data?.reply) {
      messages.value.push({ sender: 'assistant', text: res.data.reply })
    } else {
      messages.value.push({
        sender: 'assistant',
        text: 'ይቅርታ፣ አሁን መልስ መስጠት አልቻልኩም። እባክዎ እንደገና ይሞክሩ።',
      })
    }
  } catch (err) {
    messages.value.push({
      sender: 'assistant',
      text: 'Error connecting to AI service.',
    })
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}
</script>
