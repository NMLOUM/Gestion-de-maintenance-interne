<template>
  <AuthenticatedLayout>
    <Head title="Gestion des utilisateurs" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête modernisé -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg mb-6 overflow-hidden">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <div>
                <h2 class="text-3xl font-bold text-white">
                  👥 Gestion des utilisateurs
                </h2>
                <p class="text-indigo-100 mt-1">{{ users.total }} utilisateur(s) au total</p>
              </div>
              <div class="flex space-x-3">
                <Link
                  :href="route('users.create')"
                  class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-lg font-semibold transition-all hover:scale-105 flex items-center shadow-lg"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Nouvel utilisateur
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
                  placeholder="Nom, email..."
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @input="debouncedSearch"
                />
              </div>

              <div>
                <label class="block text-sm font-semibold text-white mb-2">🎭 Rôle</label>
                <select
                  v-model="localFilters.role"
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @change="applyFilters"
                >
                  <option value="">Tous les rôles</option>
                  <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                    {{ role.label }}
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

              <div>
                <label class="block text-sm font-semibold text-white mb-2">✅ Statut</label>
                <select
                  v-model="localFilters.is_active"
                  class="w-full border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white"
                  @change="applyFilters"
                >
                  <option value="">Tous</option>
                  <option value="1">Actifs</option>
                  <option value="0">Inactifs</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Liste des utilisateurs en cartes compactes -->
        <div class="space-y-3">
          <div
            v-for="user in users.data"
            :key="user.id"
            class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4"
            :class="user.is_active ? 'border-green-500' : 'border-red-500'"
          >
            <div class="p-4">
              <div class="flex items-center justify-between">
                <!-- Informations principales -->
                <div class="flex items-center space-x-3 flex-1">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 truncate">{{ user.name }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                  </div>
                </div>

                <!-- Badges et infos -->
                <div class="flex items-center space-x-3 mx-4">
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Rôle</div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                      {{ user.role_display }}
                    </span>
                  </div>
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Service</div>
                    <div class="text-xs font-semibold text-gray-900">
                      {{ user.service?.name || 'N/A' }}
                    </div>
                  </div>
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Statut</div>
                    <span
                      :class="[
                        user.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800',
                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold'
                      ]"
                    >
                      {{ user.is_active ? '✅' : '❌' }}
                    </span>
                  </div>
                </div>

                <!-- Actions compactes -->
                <div class="flex items-center space-x-2">
                  <Link
                    :href="route('users.show', user.id)"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-all"
                    title="Voir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>
                  <Link
                    :href="route('users.edit', user.id)"
                    class="inline-flex items-center px-3 py-2 bg-orange-500 text-white rounded-lg text-xs font-semibold hover:bg-orange-600 transition-all"
                    title="Modifier"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>
                  <button
                    @click="toggleUserStatus(user)"
                    :class="[
                      user.is_active
                        ? 'bg-red-500 hover:bg-red-600'
                        : 'bg-green-500 hover:bg-green-600',
                      'inline-flex items-center px-3 py-2 text-white rounded-lg text-xs font-semibold transition-all'
                    ]"
                    :title="user.is_active ? 'Désactiver' : 'Activer'"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Message si aucun utilisateur -->
          <div v-if="users.data.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun utilisateur trouvé</h3>
            <p class="text-gray-500">Essayez de modifier vos filtres ou créez un nouvel utilisateur</p>
          </div>
        </div>

        <!-- Pagination modernisée -->
        <div v-if="users.data.length > 0" class="mt-6 bg-white rounded-lg shadow-sm p-4">
          <Pagination :links="users.links" />
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
import Pagination from '@/Components/UI/Pagination.vue'
import { debounce } from 'lodash'

const props = defineProps({
  users: Object,
  filters: Object,
  services: Array,
  roleOptions: Array
})

const showFilters = ref(false)
const localFilters = reactive({ ...props.filters })

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('users.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const toggleUserStatus = (user) => {
  if (confirm(`Êtes-vous sûr de vouloir ${user.is_active ? 'désactiver' : 'activer'} cet utilisateur ?`)) {
    router.post(route('users.toggle-status', user.id), {}, {
      preserveScroll: true
    })
  }
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