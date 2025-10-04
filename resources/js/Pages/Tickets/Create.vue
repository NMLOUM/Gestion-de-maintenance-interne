<template>
  <AuthenticatedLayout>
    <Head title="Nouveau ticket" />

    <div class="py-6">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête modernisé -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg mb-6 p-6">
          <h2 class="text-3xl font-bold text-white mb-2">
            ➕ Créer un nouveau ticket
          </h2>
          <p class="text-indigo-100">Décrivez votre problème ou demande de maintenance</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="p-8">

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Titre -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                  Titre *
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.title }"
                  placeholder="Résumé du problème"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                  Description *
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.description }"
                  placeholder="Décrivez le problème en détail..."
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
              </div>

              <!-- Service et Catégorie -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Service *
                  </label>
                  <select
                    id="service_id"
                    v-model="form.service_id"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.service_id }"
                  >
                    <option value="">Sélectionner un service</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">
                      {{ service.name }}
                    </option>
                  </select>
                  <p v-if="errors.service_id" class="mt-1 text-sm text-red-600">{{ errors.service_id }}</p>
                </div>

                <div>
                  <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Catégorie *
                  </label>
                  <select
                    id="category_id"
                    v-model="form.category_id"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.category_id }"
                  >
                    <option value="">Sélectionner une catégorie</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                </div>
              </div>

              <!-- Priorité et Localisation -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                    Priorité *
                  </label>
                  <select
                    id="priority"
                    v-model="form.priority"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.priority }"
                  >
                    <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
                      {{ priority.label }}
                    </option>
                  </select>
                  <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority }}</p>
                </div>

                <div>
                  <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                    Localisation
                  </label>
                  <input
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Bureau, salle, étage..."
                  />
                </div>
              </div>

              <!-- Pièces jointes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Pièces jointes
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <span>Choisir des fichiers</span>
                        <input 
                          id="file-upload" 
                          ref="fileInput"
                          type="file" 
                          class="sr-only" 
                          multiple
                          accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                          @change="handleFileSelect"
                        />
                      </label>
                      <p class="pl-1">ou glisser-déposer</p>
                    </div>
                    <p class="text-xs text-gray-500">
                      PNG, JPG, PDF, DOC jusqu'à 10MB (max 5 fichiers)
                    </p>
                  </div>
                </div>

                <!-- Liste des fichiers sélectionnés -->
                <div v-if="selectedFiles.length > 0" class="mt-4">
                  <h4 class="text-sm font-medium text-gray-700 mb-2">Fichiers sélectionnés:</h4>
                  <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                    <li v-for="(file, index) in selectedFiles" :key="index" class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                      <div class="w-0 flex-1 flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 flex-1 w-0 truncate">
                          {{ file.name }}
                        </span>
                        <span class="ml-2 text-gray-500">
                          ({{ formatFileSize(file.size) }})
                        </span>
                      </div>
                      <div class="ml-4 flex-shrink-0">
                        <button 
                          type="button"
                          @click="removeFile(index)"
                          class="font-medium text-red-600 hover:text-red-500"
                        >
                          Supprimer
                        </button>
                      </div>
                    </li>
                  </ul>
                </div>
                <p v-if="errors.attachments" class="mt-1 text-sm text-red-600">{{ errors.attachments }}</p>
              </div>

              <!-- Boutons d'action modernisés -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <Link
                  :href="route('tickets.index')"
                  class="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all flex items-center"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Annuler
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                >
                  <svg v-if="!processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ processing ? 'Création en cours...' : '✅ Créer le ticket' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  services: Array,
  categories: Array,
  priorityOptions: Array,
  errors: Object
})

// États réactifs
const processing = ref(false)
const selectedFiles = ref([])
const fileInput = ref(null)

const form = reactive({
  title: '',
  description: '',
  service_id: '',
  category_id: '',
  priority: 'normal',
  location: '',
  attachments: []
})

// Méthodes
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  
  // Vérifier le nombre total de fichiers
  if (selectedFiles.value.length + files.length > 5) {
    alert('Vous ne pouvez pas sélectionner plus de 5 fichiers.')
    return
  }
  
  // Vérifier la taille de chaque fichier
  const invalidFiles = files.filter(file => file.size > 10 * 1024 * 1024) // 10MB
  if (invalidFiles.length > 0) {
    alert('Certains fichiers dépassent la taille limite de 10MB.')
    return
  }
  
  selectedFiles.value.push(...files)
  form.attachments = selectedFiles.value
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
  form.attachments = selectedFiles.value
  
  // Réinitialiser l'input file
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const submit = () => {
  processing.value = true
  
  // Créer un FormData pour gérer les fichiers
  const formData = new FormData()
  
  // Ajouter les champs du formulaire
  Object.keys(form).forEach(key => {
    if (key !== 'attachments') {
      formData.append(key, form[key])
    }
  })
  
  // Ajouter les fichiers
  selectedFiles.value.forEach((file, index) => {
    formData.append(`attachments[${index}]`, file)
  })
  
  router.post(route('tickets.store'), formData, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>