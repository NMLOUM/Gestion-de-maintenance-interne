<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <!-- Overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

      <!-- Modal -->
      <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
              </svg>
              Évaluer l'intervention
            </h3>
            <button @click="closeModal" class="text-white hover:text-gray-200 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitEvaluation" class="px-6 py-6">
          <!-- Rating -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-gray-900 mb-3">
              Comment évaluez-vous cette intervention ? *
            </label>
            <div class="flex items-center justify-center space-x-2">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="form.rating = star"
                @mouseenter="hoverRating = star"
                @mouseleave="hoverRating = 0"
                class="focus:outline-none transition-all transform hover:scale-110"
              >
                <svg
                  class="w-12 h-12 transition-colors"
                  :class="star <= (hoverRating || form.rating) ? 'text-yellow-400' : 'text-gray-300'"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </button>
            </div>
            <div v-if="form.rating" class="text-center mt-2">
              <span class="text-sm font-semibold" :class="getRatingColorClass()">
                {{ getRatingLabel() }}
              </span>
            </div>
            <p v-if="form.errors.rating" class="mt-2 text-sm text-red-600">{{ form.errors.rating }}</p>
          </div>

          <!-- Comment -->
          <div class="mb-6">
            <label for="comment" class="block text-sm font-bold text-gray-900 mb-2">
              Commentaire (optionnel)
            </label>
            <textarea
              id="comment"
              v-model="form.comment"
              rows="4"
              placeholder="Partagez votre expérience avec cette intervention..."
              class="block w-full border-2 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
              maxlength="1000"
            ></textarea>
            <div class="flex justify-between mt-1">
              <p v-if="form.errors.comment" class="text-sm text-red-600">{{ form.errors.comment }}</p>
              <p class="text-xs text-gray-500 ml-auto">{{ form.comment?.length || 0 }}/1000</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition"
              :disabled="form.processing"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-bold hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="!form.rating || form.processing"
            >
              {{ form.processing ? '⏳ Envoi...' : existingEvaluation ? '✅ Mettre à jour' : '⭐ Évaluer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  ticketId: Number,
  existingEvaluation: Object,
})

const emit = defineEmits(['close'])

const hoverRating = ref(0)

const form = useForm({
  rating: props.existingEvaluation?.rating || 0,
  comment: props.existingEvaluation?.comment || '',
})

// Mettre à jour le formulaire si l'évaluation existante change
watch(() => props.existingEvaluation, (newVal) => {
  if (newVal) {
    form.rating = newVal.rating
    form.comment = newVal.comment || ''
  }
}, { immediate: true })

const closeModal = () => {
  if (!form.processing) {
    form.reset()
    hoverRating.value = 0
    emit('close')
  }
}

const submitEvaluation = () => {
  form.post(route('evaluations.store', props.ticketId), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
    },
  })
}

const getRatingLabel = () => {
  const labels = {
    1: '⭐ Très insatisfait',
    2: '⭐⭐ Insatisfait',
    3: '⭐⭐⭐ Neutre',
    4: '⭐⭐⭐⭐ Satisfait',
    5: '⭐⭐⭐⭐⭐ Très satisfait'
  }
  return labels[form.rating] || ''
}

const getRatingColorClass = () => {
  if (form.rating <= 2) return 'text-red-600'
  if (form.rating === 3) return 'text-yellow-600'
  return 'text-green-600'
}
</script>
