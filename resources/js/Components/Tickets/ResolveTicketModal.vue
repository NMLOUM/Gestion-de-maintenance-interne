<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <!-- Overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

      <!-- Modal -->
      <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Marquer comme résolu
            </h3>
            <button @click="closeModal" class="text-white hover:text-gray-200 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitResolution" class="px-6 py-6">
          <!-- Temps passé -->
          <div class="mb-6">
            <label for="actual_hours" class="block text-sm font-bold text-gray-900 mb-2">
              ⏱️ Temps passé (en heures) *
            </label>
            <input
              id="actual_hours"
              v-model.number="form.actual_hours"
              type="number"
              step="0.5"
              min="0.5"
              placeholder="Ex: 2.5"
              class="block w-full border-2 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
              required
            />
            <p class="mt-1 text-xs text-gray-500">
              Indiquez le temps réel consacré à la résolution de ce ticket
            </p>
            <p v-if="form.errors.actual_hours" class="mt-2 text-sm text-red-600">{{ form.errors.actual_hours }}</p>
          </div>

          <!-- Commentaire de résolution -->
          <div class="mb-6">
            <label for="resolution_comment" class="block text-sm font-bold text-gray-900 mb-2">
              💬 Commentaire de résolution *
            </label>
            <textarea
              id="resolution_comment"
              v-model="form.comment"
              rows="4"
              placeholder="Décrivez ce qui a été fait pour résoudre le problème..."
              class="block w-full border-2 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
              maxlength="1000"
              required
            ></textarea>
            <div class="flex justify-between mt-1">
              <p class="text-xs text-gray-500">
                Ce commentaire sera visible par le demandeur
              </p>
              <p class="text-xs text-gray-500">{{ form.comment?.length || 0 }}/1000</p>
            </div>
            <p v-if="form.errors.comment" class="mt-2 text-sm text-red-600">{{ form.errors.comment }}</p>
          </div>

          <!-- Résumé -->
          <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              <div class="text-sm text-green-800">
                <p class="font-semibold mb-1">Le ticket sera marqué comme résolu</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                  <li>Le demandeur sera notifié par email</li>
                  <li>Le demandeur pourra évaluer votre intervention</li>
                  <li>Le demandeur pourra clôturer définitivement le ticket</li>
                </ul>
              </div>
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
              class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-bold hover:from-green-700 hover:to-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
              :disabled="!form.actual_hours || !form.comment || form.processing"
            >
              <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ form.processing ? '⏳ Résolution...' : '✅ Marquer comme résolu' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  ticketId: Number,
})

const emit = defineEmits(['close'])

const form = useForm({
  status: 'resolved',
  actual_hours: null,
  comment: '',
})

// Reset form when modal opens
watch(() => props.show, (newVal) => {
  if (newVal) {
    form.reset()
  }
})

const closeModal = () => {
  if (!form.processing) {
    form.reset()
    emit('close')
  }
}

const submitResolution = () => {
  // D'abord mettre à jour le ticket avec le temps passé
  router.put(route('tickets.update', props.ticketId), {
    actual_hours: form.actual_hours
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Ensuite ajouter le commentaire et changer le statut
      router.post(route('tickets.comment', props.ticketId), {
        comment: form.comment,
        is_internal: false
      }, {
        preserveScroll: true,
        onSuccess: () => {
          // Enfin changer le statut
          router.post(route('tickets.status', props.ticketId), {
            status: 'resolved'
          }, {
            preserveScroll: true,
            onSuccess: () => {
              closeModal()
            }
          })
        }
      })
    }
  })
}
</script>
