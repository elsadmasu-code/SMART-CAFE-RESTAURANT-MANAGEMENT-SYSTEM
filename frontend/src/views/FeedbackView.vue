<template>
  <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="text-center space-y-2">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
        Customer Feedback / አስተያየት
      </h2>
      <p class="text-xs text-stone-500">Help Fkr Cafe in Kombolcha serve you better!</p>
    </div>

    <form @submit.prevent="submitFeedback" class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
      <!-- Star Ratings -->
      <div class="space-y-4">
        <!-- Overall -->
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-stone-700">Overall Experience / አጠቃላይ እርካታ:</span>
          <div class="flex gap-1 text-amber-400">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="ratings.overall = star"
              class="text-lg hover:scale-125 transition"
            >
              {{ star <= ratings.overall ? '★' : '☆' }}
            </button>
          </div>
        </div>

        <!-- Food Quality -->
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-stone-700">Food Quality / የምግብ ጥራት:</span>
          <div class="flex gap-1 text-amber-400">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="ratings.food = star"
              class="text-lg hover:scale-125 transition"
            >
              {{ star <= ratings.food ? '★' : '☆' }}
            </button>
          </div>
        </div>

        <!-- Service -->
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-stone-700">Waiter & Service / የአስተናጋጅ ቅልጥፍና:</span>
          <div class="flex gap-1 text-amber-400">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="ratings.service = star"
              class="text-lg hover:scale-125 transition"
            >
              {{ star <= ratings.service ? '★' : '☆' }}
            </button>
          </div>
        </div>

        <!-- Cleanliness -->
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-stone-700">Cleanliness / የካፌው ጽዳት:</span>
          <div class="flex gap-1 text-amber-400">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="ratings.cleanliness = star"
              class="text-lg hover:scale-125 transition"
            >
              {{ star <= ratings.cleanliness ? '★' : '☆' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Written Comment -->
      <div class="space-y-1.5 pt-2 border-t border-stone-100">
        <label class="text-xs font-bold text-stone-700 block">Comments & Suggestions / ተጨማሪ አስተያየት</label>
        <textarea
          v-model="comment"
          rows="3"
          placeholder="Tell us what you loved or how we can improve..."
          class="w-full text-xs p-3.5 rounded-2xl border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-cafe-500"
        ></textarea>
      </div>

      <div v-if="submitted" class="p-3 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl text-center border border-emerald-200">
        Thank you! Your feedback has been recorded. እናመሰግናለን!
      </div>

      <button
        type="submit"
        :disabled="submitted"
        class="w-full py-3 px-4 rounded-2xl bg-cafe-700 hover:bg-cafe-800 text-white font-bold text-xs transition shadow-md disabled:opacity-50"
      >
        Submit Feedback / አስገባ
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const ratings = ref({
  overall: 5,
  food: 5,
  service: 5,
  cleanliness: 5,
})
const comment = ref('')
const submitted = ref(false)

function submitFeedback() {
  submitted.value = true
}
</script>
