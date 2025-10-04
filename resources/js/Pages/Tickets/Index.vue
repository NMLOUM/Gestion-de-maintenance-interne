<template>
  <AuthenticatedLayout>
    <Head title="Tickets de maintenance" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête avec boutons d'action modernisé -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg mb-6 overflow-hidden">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <div>
                <h2 class="text-3xl font-bold text-white">
                  🎫 Tickets de maintenance
                </h2>
                <p class="text-indigo-100 mt-1">{{ tickets.total }} ticket(s) au total</p>
              </div>
              <div class="flex space-x-3">
                <Link
                  :href="route('tickets.create')"
                  class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-lg font-semibold transition-all hover:scale-105 flex items-center shadow-lg"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Nouveau ticket
                </Link>
                <button
                  @click="showFilters = !showFilters"
                  class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                  </svg>
                  {{ showFilters ? 'Masquer filtres' : 'Filtres' }}
                </button>
              </div>
            </div>

            <!-- Filtres modernisés -->
            <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white bg-opacity-10 rounded-lg p-4">
              <div>
                <label class="block text-sm font-semibold text-white mb-2">🔍 Recherche</label>
                <input
                  v-model="localFilters.search"
                  type="text"
                  placeholder="Titre, description, numéro..."
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @input="debouncedSearch"
                />
              </div>

              <div>
                <label class="block text-sm font-semibold text-white mb-2">📊 Statut</label>
                <select
                  v-model="localFilters.status"
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @change="applyFilters"
                >
                  <option value="">Tous les statuts</option>
                  <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                    {{ status.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold text-white mb-2">⚡ Priorité</label>
                <select
                  v-model="localFilters.priority"
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @change="applyFilters"
                >
                  <option value="">Toutes les priorités</option>
                  <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
                    {{ priority.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold text-white mb-2">🏢 Service</label>
                <select
                  v-model="localFilters.service_id"
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @change="applyFilters"
                >
                  <option value="">Tous les services</option>
                  <option v-for="service in services" :key="service.id" :value="service.id">
                    {{ service.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Liste des tickets en cartes compactes -->
        <div class="space-y-3">
          <div
            v-for="ticket in tickets.data"
            :key="ticket.id"
            class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4"
            :class="ticket.is_overdue ? 'border-red-500 bg-red-50' : 'border-indigo-500'"
          >
            <div class="p-4">
              <div class="flex items-center justify-between">
                <!-- Info principale compacte -->
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                      {{ ticket.requester.name.charAt(0) }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <Link
                      :href="route('tickets.show', ticket.id)"
                      class="text-base font-bold text-gray-900 hover:text-indigo-600 transition-colors block truncate"
                    >
                      {{ ticket.title }}
                    </Link>
                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                      <span class="font-medium text-indigo-600">{{ ticket.ticket_number }}</span>
                      <span>•</span>
                      <span>{{ ticket.requester.name }}</span>
                      <span>•</span>
                      <span>{{ ticket.service.name }}</span>
                    </div>
                  </div>
                </div>

                <!-- Infos compactes au centre -->
                <div class="flex items-center space-x-4 mx-4">
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Catégorie</div>
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                      :style="{ backgroundColor: ticket.category.color + '30', color: ticket.category.color }"
                    >
                      {{ ticket.category.name }}
                    </span>
                  </div>

                  <div class="text-center">
                    <div class="text-xs text-gray-500">Assigné</div>
                    <div class="text-xs font-semibold text-gray-900">
                      {{ ticket.assigned_user?.name || 'Non assigné' }}
                    </div>
                  </div>

                  <div class="text-center">
                    <div class="text-xs text-gray-500">SLA</div>
                    <div v-if="ticket.is_overdue" class="text-xs font-bold text-red-600">
                      🔴 Retard
                    </div>
                    <div v-else class="text-xs font-semibold text-green-600">
                      ✅ OK
                    </div>
                  </div>
                </div>

                <!-- Badges et action -->
                <div class="flex items-center space-x-3">
                  <PriorityBadge :priority="ticket.priority" />
                  <StatusBadge :status="ticket.status" />
                  <Link
                    :href="route('tickets.show', ticket.id)"
                    class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg text-xs font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all"
                    title="Voir détails"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Message si aucun ticket -->
          <div v-if="tickets.data.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun ticket trouvé</h3>
            <p class="text-gray-500">Essayez de modifier vos filtres ou créez un nouveau ticket</p>
          </div>
        </div>

        <!-- Pagination modernisée -->
        <div v-if="tickets.data.length > 0" class="mt-6 bg-white rounded-lg shadow-sm p-4">
          <Pagination :links="tickets.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Tickets/StatusBadge.vue'
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { debounce } from 'lodash'

const props = defineProps({
  tickets: Object,
  filters: Object,
  services: Array,
  categories: Array,
  technicians: Array,
  statusOptions: Array,
  priorityOptions: Array
})

const showFilters = ref(false)
const localFilters = reactive({ ...props.filters })

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('tickets.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>