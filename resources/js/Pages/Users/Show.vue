<template>
  <AuthenticatedLayout>
    <Head :title="`Utilisateur: ${user.name}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-semibold text-gray-900">
                Profil de {{ user.name }}
              </h2>
              <div class="flex space-x-4">
                <Link
                  :href="route('users.index')"
                  class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                  Retour à la liste
                </Link>
                <Link
                  :href="route('users.edit', user.id)"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                  Modifier
                </Link>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Informations de l'utilisateur -->
          <div class="lg:col-span-1">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <div class="flex flex-col items-center">
                  <div class="h-32 w-32 rounded-full bg-blue-500 flex items-center justify-center mb-4">
                    <span class="text-4xl font-bold text-white">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <h3 class="text-xl font-semibold text-gray-900">{{ user.name }}</h3>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>

                  <div class="mt-4 w-full">
                    <span
                      :class="[
                        user.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800',
                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium w-full justify-center'
                      ]"
                    >
                      {{ user.is_active ? 'Actif' : 'Inactif' }}
                    </span>
                  </div>
                </div>

                <div class="mt-6 space-y-4">
                  <div>
                    <label class="text-sm font-medium text-gray-500">Rôle</label>
                    <p class="mt-1 text-sm text-gray-900">{{ user.role_display }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Service</label>
                    <p class="mt-1 text-sm text-gray-900">{{ user.service?.name || 'Non assigné' }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="mt-1 text-sm text-gray-900">{{ user.phone || 'Non renseigné' }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Inscription</label>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Dernière connexion</label>
                    <p class="mt-1 text-sm text-gray-900">
                      {{ user.last_login_at ? formatDate(user.last_login_at) : 'Jamais' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets de l'utilisateur -->
          <div class="lg:col-span-2">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets créés</h3>
                <div v-if="user.requested_tickets && user.requested_tickets.length > 0" class="space-y-3">
                  <div
                    v-for="ticket in user.requested_tickets.slice(0, 5)"
                    :key="ticket.id"
                    class="border-l-4 border-blue-400 bg-blue-50 p-4 rounded"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <h4 class="text-sm font-medium text-gray-900">#{{ ticket.id }} - {{ ticket.title }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ ticket.description?.substring(0, 100) }}</p>
                        <div class="mt-2 flex items-center space-x-2">
                          <span class="text-xs px-2 py-1 rounded-full bg-gray-200">{{ ticket.status }}</span>
                          <span class="text-xs px-2 py-1 rounded-full bg-gray-200">{{ ticket.priority }}</span>
                        </div>
                      </div>
                      <Link
                        :href="route('tickets.show', ticket.id)"
                        class="text-blue-600 hover:text-blue-900 text-sm"
                      >
                        Voir
                      </Link>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  Aucun ticket créé
                </div>
              </div>
            </div>

            <div v-if="user.role === 'technicien' || user.role === 'responsable_it'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets assignés</h3>
                <div v-if="user.assigned_tickets && user.assigned_tickets.length > 0" class="space-y-3">
                  <div
                    v-for="ticket in user.assigned_tickets.slice(0, 5)"
                    :key="ticket.id"
                    class="border-l-4 border-green-400 bg-green-50 p-4 rounded"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <h4 class="text-sm font-medium text-gray-900">#{{ ticket.id }} - {{ ticket.title }}</h4>
                        <p class="text-xs text-gray-600 mt-1">Demandeur: {{ ticket.requester?.name }}</p>
                        <div class="mt-2 flex items-center space-x-2">
                          <span class="text-xs px-2 py-1 rounded-full bg-gray-200">{{ ticket.status }}</span>
                          <span class="text-xs px-2 py-1 rounded-full bg-gray-200">{{ ticket.priority }}</span>
                        </div>
                      </div>
                      <Link
                        :href="route('tickets.show', ticket.id)"
                        class="text-green-600 hover:text-green-900 text-sm"
                      >
                        Voir
                      </Link>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  Aucun ticket assigné
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
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  user: Object
})

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
