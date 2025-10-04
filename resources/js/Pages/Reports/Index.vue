<template>
  <AuthenticatedLayout>
    <Head title="Rapports" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900">
                  Rapports et Statistiques
                </h2>
                <div class="flex items-center mt-1 space-x-3">
                  <p class="text-sm text-gray-600">Analyse des performances et activités du système</p>
                  <div class="flex items-center space-x-1 text-xs text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Mise à jour auto (30s)</span>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                  Dernière actualisation: {{ formatTime(lastUpdated) }}
                </p>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="loadData"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  Actualiser
                </button>
                <button
                  @click="showFilters = !showFilters"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                  {{ showFilters ? 'Masquer les filtres' : 'Filtres' }}
                </button>
              </div>
            </div>

            <!-- Filtres de période -->
            <div v-show="showFilters" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Période</label>
                <select
                  v-model="selectedPeriod"
                  @change="loadData"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                >
                  <option value="week">Cette semaine</option>
                  <option value="month">Ce mois</option>
                  <option value="quarter">Ce trimestre</option>
                  <option value="year">Cette année</option>
                  <option value="custom">Période personnalisée</option>
                </select>
              </div>

              <div v-if="selectedPeriod === 'custom'">
                <label class="block text-sm font-medium text-gray-700">Date de début</label>
                <input
                  v-model="customStart"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                />
              </div>

              <div v-if="selectedPeriod === 'custom'">
                <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                <input
                  v-model="customEnd"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Statistiques globales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                      <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Tickets créés</dt>
                    <dd class="text-2xl font-bold text-gray-900">{{ stats.created || 0 }}</dd>
                    <dd class="text-xs text-gray-500">Cette période</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Tickets résolus</dt>
                    <dd class="text-2xl font-bold text-gray-900">{{ stats.resolved || 0 }}</dd>
                    <dd class="text-xs text-green-600">{{ stats.resolution_rate || 0 }}% de résolution</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Temps moyen</dt>
                    <dd class="text-2xl font-bold text-gray-900">{{ stats.avg_time || 0 }}h</dd>
                    <dd class="text-xs text-gray-500">Résolution moyenne</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Utilisateurs actifs</dt>
                    <dd class="text-2xl font-bold text-gray-900">{{ stats.active_users || 0 }}</dd>
                    <dd class="text-xs text-gray-500">Cette période</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Graphiques et tableaux -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Tickets par status -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par statut</h3>
              <div class="space-y-3">
                <div v-for="(count, status) in byStatus" :key="status">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700 capitalize">{{ status }}</span>
                    <span class="font-medium">{{ count }}</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :class="getStatusColor(status)"
                      :style="{ width: (count / stats.created * 100) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets par priorité -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par priorité</h3>
              <div class="space-y-3">
                <div v-for="(count, priority) in byPriority" :key="priority">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700 capitalize">{{ priority }}</span>
                    <span class="font-medium">{{ count }}</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :class="getPriorityColor(priority)"
                      :style="{ width: (count / stats.created * 100) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top techniciens -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Top techniciens</h3>
              <div class="space-y-3">
                <div v-for="tech in topTechnicians" :key="tech.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-white">{{ tech.name.charAt(0) }}</span>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">{{ tech.name }}</p>
                      <p class="text-xs text-gray-500">{{ tech.resolved_count }} résolus</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="text-sm font-medium text-green-600">{{ tech.success_rate }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets par service -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets par service</h3>
              <div class="space-y-3">
                <div v-for="service in byService" :key="service.id">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">{{ service.name }}</span>
                    <span class="font-medium">{{ service.count }}</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-indigo-600 h-2 rounded-full"
                      :style="{ width: (service.count / stats.created * 100) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  stats: Object,
  byStatus: Object,
  byPriority: Object,
  byService: Array,
  topTechnicians: Array
})

const showFilters = ref(false)
const selectedPeriod = ref('month')
const customStart = ref('')
const customEnd = ref('')
const lastUpdated = ref(new Date())
let refreshInterval = null

const loadData = () => {
  router.reload({
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      lastUpdated.value = new Date()
    }
  })
}

// Rafraîchissement automatique toutes les 30 secondes
onMounted(() => {
  refreshInterval = setInterval(() => {
    loadData()
  }, 30000) // 30 secondes
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-500',
    in_progress: 'bg-blue-500',
    resolved: 'bg-green-500',
    closed: 'bg-gray-500',
    cancelled: 'bg-red-500'
  }
  return colors[status] || 'bg-gray-500'
}

const getPriorityColor = (priority) => {
  const colors = {
    low: 'bg-green-500',
    normal: 'bg-blue-500',
    high: 'bg-orange-500',
    critical: 'bg-red-500'
  }
  return colors[priority] || 'bg-gray-500'
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}
</script>
