<template>
  <AuthenticatedLayout>
    <Head :title="`Ticket ${ticket.ticket_number}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête avec actions en ligne -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-2xl mb-6 overflow-hidden">
          <div class="p-6">
            <!-- Ligne principale : Titre + Badges + Actions rapides -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-6">
              <!-- Informations du ticket -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3 mb-2">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white bg-opacity-20 text-white">
                    {{ ticket.ticket_number }}
                  </span>
                  <PriorityBadge :priority="ticket.priority" />
                  <StatusBadge :status="ticket.status" />
                  <SlaBadge :ticket="ticket" :show-details="true" />
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">
                  {{ ticket.title }}
                </h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-indigo-100">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ ticket.requester.name }}</span>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    {{ formatDate(ticket.created_at) }}
                  </div>
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm6 6H7v2h6v-2z" clip-rule="evenodd"/>
                    </svg>
                    {{ ticket.service.name }}
                  </div>
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    {{ ticket.category.name }}
                  </div>
                </div>
              </div>

              <!-- Actions rapides (toujours visibles) -->
              <div class="flex-shrink-0 w-full lg:w-auto lg:min-w-[280px]">
                <div class="bg-white bg-opacity-10 rounded-xl p-5 backdrop-blur-sm border border-white border-opacity-20">
                  <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wide flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Actions rapides
                  </h3>
                  <div class="flex flex-col gap-2.5">
                    <!-- Démarrer -->
                    <button
                      v-if="ticket.status === 'pending' && ticket.assigned_to && canEdit"
                      @click="quickUpdateStatus('in_progress')"
                      class="flex items-center justify-center px-4 py-3 bg-white text-indigo-600 rounded-lg font-bold text-sm transition-all hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      ▶️ Démarrer
                    </button>

                    <!-- Résoudre -->
                    <button
                      v-if="ticket.status === 'in_progress' && canEdit"
                      @click="showResolveModal = true"
                      class="flex items-center justify-center px-4 py-3 bg-green-500 text-white rounded-lg font-bold text-sm transition-all hover:bg-green-600 hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      ✅ Résoudre
                    </button>

                    <!-- Clôturer -->
                    <button
                      v-if="ticket.status === 'resolved'"
                      @click="quickUpdateStatus('closed')"
                      class="flex items-center justify-center px-4 py-3 bg-gray-800 text-white rounded-lg font-bold text-sm transition-all hover:bg-gray-900 hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                      🔒 Clôturer
                    </button>

                    <!-- Annuler (pour tous si pending ou in_progress) -->
                    <button
                      v-if="(ticket.status === 'pending' || ticket.status === 'in_progress') && (canEdit || canAssign)"
                      @click="quickUpdateStatus('cancelled')"
                      class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg font-bold text-sm transition-all hover:bg-red-700 hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      ❌ Annuler
                    </button>

                    <!-- Réouvrir et réassigner (pour les tickets annulés) -->
                    <button
                      v-if="ticket.status === 'cancelled' && canAssign"
                      @click="showReopenModal = true"
                      class="flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded-lg font-bold text-sm transition-all hover:bg-orange-700 hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                      🔄 Réouvrir et réassigner
                    </button>

                    <!-- Évaluer (pour le demandeur si résolu ou fermé) -->
                    <button
                      v-if="(ticket.status === 'resolved' || ticket.status === 'closed') && ticket.requester_id === $page.props.auth.user.id"
                      @click="showEvaluationModal = true"
                      class="flex items-center justify-center px-4 py-3 bg-yellow-500 text-white rounded-lg font-bold text-sm transition-all hover:bg-yellow-600 hover:shadow-xl hover:scale-105 whitespace-nowrap shadow-lg"
                    >
                      <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      {{ ticket.evaluation ? '⭐ Modifier évaluation' : '⭐ Évaluer' }}
                    </button>

                    <!-- Assignation -->
                    <div v-if="canAssign && ticket.status === 'pending'" class="mt-2 pt-2 border-t border-white border-opacity-20">
                      <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wide">👤 Assigner</label>
                      <select
                        v-model="selectedTechnician"
                        @change="assignTicket"
                        class="block w-full border-0 rounded-lg shadow-sm text-sm font-semibold focus:ring-2 focus:ring-white py-2.5 bg-white"
                      >
                        <option value="">Choisir technicien</option>
                        <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                          {{ tech.name }}
                        </option>
                      </select>
                    </div>

                    <!-- Message si aucune action disponible -->
                    <div v-if="!canEdit && !canAssign && ticket.status === 'closed'" class="text-center py-3">
                      <p class="text-white text-sm opacity-80">✅ Ticket clôturé</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Indicateur SLA -->
            <div v-if="ticket.sla_status && ticket.sla_status.status !== 'completed'" class="mb-4">
              <div
                class="rounded-lg p-4 border-2"
                :class="{
                  'bg-red-50 border-red-500': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                  'bg-orange-50 border-orange-500': ticket.sla_status.status === 'warning',
                  'bg-green-50 border-green-500': ticket.sla_status.status === 'ok'
                }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <svg
                      class="w-6 h-6"
                      :class="{
                        'text-red-600': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                        'text-orange-600': ticket.sla_status.status === 'warning',
                        'text-green-600': ticket.sla_status.status === 'ok'
                      }"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                      <div
                        class="font-bold text-sm"
                        :class="{
                          'text-red-900': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                          'text-orange-900': ticket.sla_status.status === 'warning',
                          'text-green-900': ticket.sla_status.status === 'ok'
                        }"
                      >
                        {{ ticket.sla_status.label }}
                      </div>
                      <div
                        class="text-xs"
                        :class="{
                          'text-red-700': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                          'text-orange-700': ticket.sla_status.status === 'warning',
                          'text-green-700': ticket.sla_status.status === 'ok'
                        }"
                      >
                        {{ ticket.sla_status.formatted }}
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div
                      class="text-2xl font-bold"
                      :class="{
                        'text-red-600': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                        'text-orange-600': ticket.sla_status.status === 'warning',
                        'text-green-600': ticket.sla_status.status === 'ok'
                      }"
                    >
                      {{ ticket.sla_percentage }}%
                    </div>
                    <div class="text-xs text-gray-600">SLA utilisé</div>
                  </div>
                </div>
                <!-- Barre de progression -->
                <div class="mt-3 bg-white bg-opacity-50 rounded-full h-2 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="{
                      'bg-red-600': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                      'bg-orange-600': ticket.sla_status.status === 'warning',
                      'bg-green-600': ticket.sla_status.status === 'ok'
                    }"
                    :style="{ width: Math.min(100, ticket.sla_percentage) + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Informations secondaires en grille -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white bg-opacity-10 rounded-lg p-4">
              <div>
                <div class="text-indigo-200 text-xs font-bold mb-1 uppercase tracking-wide">Assigné à</div>
                <div class="text-white font-semibold text-sm">{{ ticket.assigned_user?.name || '❌ Non assigné' }}</div>
              </div>
              <div v-if="ticket.location">
                <div class="text-indigo-200 text-xs font-bold mb-1 uppercase tracking-wide">Localisation</div>
                <div class="text-white font-semibold text-sm">📍 {{ ticket.location }}</div>
              </div>
              <div v-if="ticket.estimated_hours">
                <div class="text-indigo-200 text-xs font-bold mb-1 uppercase tracking-wide">Durée estimée</div>
                <div class="text-white font-semibold text-sm">⏱️ {{ ticket.estimated_hours }}h</div>
              </div>
              <div v-if="ticket.actual_hours">
                <div class="text-indigo-200 text-xs font-bold mb-1 uppercase tracking-wide">Durée réelle</div>
                <div class="text-white font-semibold text-sm">✅ {{ ticket.actual_hours }}h</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contenu principal pleine largeur -->
        <div class="space-y-6">
          <!-- Description -->
          <div class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Description du problème
              </h3>
            </div>
            <div class="p-6">
              <div class="bg-gray-50 rounded-lg p-5 border-l-4 border-indigo-500">
                <p class="whitespace-pre-wrap text-gray-800 leading-relaxed text-base">{{ ticket.description }}</p>
              </div>
            </div>
          </div>

          <!-- Pièces jointes -->
          <div v-if="ticket.attachments.length > 0 || canComment" class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                Pièces jointes ({{ ticket.attachments.length }})
              </h3>
            </div>
            <div class="p-6">
              <!-- Liste des pièces jointes -->
              <div v-if="ticket.attachments.length > 0" class="space-y-3 mb-6">
                <div
                  v-for="attachment in ticket.attachments"
                  :key="attachment.id"
                  class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition-all"
                >
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <svg v-if="attachment.is_image" class="h-8 w-8 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                    <svg v-else class="h-8 w-8 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-semibold text-gray-900 truncate">{{ attachment.original_name }}</p>
                      <p class="text-xs text-gray-500">{{ attachment.file_size_human }} • {{ attachment.user.name }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2 ml-3">
                    <a
                      :href="attachment.url"
                      target="_blank"
                      class="p-2 text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                      title="Télécharger"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </a>
                    <button
                      v-if="canDeleteAttachment(attachment)"
                      @click="deleteAttachment(attachment)"
                      class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                      title="Supprimer"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Formulaire d'ajout -->
              <div v-if="canComment" class="bg-gradient-to-r from-gray-50 to-indigo-50 rounded-lg p-4 border-2 border-dashed border-gray-300">
                <h4 class="text-sm font-bold text-gray-900 mb-3">➕ Ajouter un fichier</h4>
                <form @submit.prevent="uploadFile" class="space-y-3">
                  <input
                    ref="fileInput"
                    type="file"
                    @change="handleFileSelect"
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition"
                  />
                  <button
                    v-if="selectedFile"
                    type="submit"
                    :disabled="uploading"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg text-sm font-bold transition"
                  >
                    {{ uploading ? '⏳ Upload...' : '✅ Ajouter' }}
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Commentaires -->
          <div class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Commentaires ({{ ticket.comments.length }})
              </h3>
            </div>
            <div class="p-6">
              <!-- Liste des commentaires -->
              <div class="space-y-4 mb-6">
                <div
                  v-for="comment in ticket.comments"
                  :key="comment.id"
                  class="border-l-4 rounded-lg p-4 transition-all hover:shadow-md"
                  :class="comment.is_internal ? 'bg-yellow-50 border-yellow-500' : 'bg-blue-50 border-blue-500'"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ comment.user.name.charAt(0) }}
                      </div>
                      <div>
                        <span class="font-bold text-gray-900 block">{{ comment.user.name }}</span>
                        <span class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</span>
                      </div>
                    </div>
                    <span v-if="comment.is_internal" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-200 text-yellow-900">
                      🔒 Interne
                    </span>
                  </div>
                  <p class="text-gray-800 whitespace-pre-wrap leading-relaxed ml-13 text-sm">{{ comment.comment }}</p>
                </div>
              </div>

              <!-- Formulaire d'ajout de commentaire -->
              <div v-if="canComment" class="bg-gradient-to-r from-gray-50 to-indigo-50 rounded-lg p-4 border-2 border-dashed border-gray-300">
                <form @submit.prevent="addComment" class="space-y-3">
                  <textarea
                    v-model="newComment"
                    rows="4"
                    placeholder="✍️ Ajouter un commentaire..."
                    class="block w-full border-2 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                  ></textarea>
                  <div class="flex justify-between items-center">
                    <div v-if="$page.props.auth.user.is_technician" class="flex items-center">
                      <input
                        id="is_internal"
                        v-model="isInternalComment"
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      />
                      <label for="is_internal" class="ml-2 block text-sm font-semibold text-gray-700">
                        🔒 Commentaire interne
                      </label>
                    </div>
                    <button
                      type="submit"
                      :disabled="!newComment.trim() || addingComment"
                      class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white px-6 py-2 rounded-lg text-sm font-bold transition"
                    >
                      {{ addingComment ? '⏳ Envoi...' : '💬 Commenter' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Évaluation existante -->
          <div v-if="ticket.evaluation" class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Évaluation de l'intervention
              </h3>
            </div>
            <div class="p-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl shadow-md">
                    {{ ticket.evaluation.rating }}
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center space-x-2 mb-2">
                    <div class="flex">
                      <svg
                        v-for="star in 5"
                        :key="star"
                        class="w-6 h-6"
                        :class="star <= ticket.evaluation.rating ? 'text-yellow-400' : 'text-gray-300'"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    </div>
                    <span class="font-bold text-gray-900">
                      {{ getRatingLabel(ticket.evaluation.rating) }}
                    </span>
                  </div>
                  <div v-if="ticket.evaluation.comment" class="bg-gray-50 rounded-lg p-4 border-l-4 border-yellow-400">
                    <p class="text-gray-800 text-sm leading-relaxed">{{ ticket.evaluation.comment }}</p>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    Évalué le {{ formatDate(ticket.evaluation.created_at) }}
                    <span v-if="ticket.evaluation.user"> par {{ ticket.evaluation.user.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Historique -->
          <div class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historique complet
              </h3>
            </div>
            <div class="p-6">
              <div class="flow-root">
                <ul class="-mb-8">
                  <li v-for="(history, index) in ticket.histories" :key="history.id">
                    <div class="relative pb-8">
                      <span
                        v-if="index !== ticket.histories.length - 1"
                        class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gradient-to-b from-indigo-400 to-purple-400"
                        aria-hidden="true"
                      />
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center ring-4 ring-white shadow-md">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                          </span>
                        </div>
                        <div class="min-w-0 flex-1">
                          <div class="bg-gray-50 rounded-lg p-3 border-l-2 border-indigo-300">
                            <p class="text-sm text-gray-800 font-semibold mb-1">{{ history.description }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(history.created_at) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'évaluation -->
    <EvaluationModal
      :show="showEvaluationModal"
      :ticket-id="ticket.id"
      :existing-evaluation="ticket.evaluation"
      @close="showEvaluationModal = false"
    />

    <!-- Modal de résolution -->
    <ResolveTicketModal
      :show="showResolveModal"
      :ticket-id="ticket.id"
      @close="showResolveModal = false"
    />

    <!-- Modal de réouverture et réassignation -->
    <div v-if="showReopenModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showReopenModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  🔄 Réouvrir et réassigner le ticket
                </h3>
                <div class="mt-4">
                  <p class="text-sm text-gray-500 mb-4">
                    Ce ticket annulé sera réouvert et mis en attente. Sélectionnez un technicien pour l'assigner.
                  </p>
                  <div>
                    <label for="reopen-technician" class="block text-sm font-medium text-gray-700 mb-2">
                      Assigner à :
                    </label>
                    <select
                      id="reopen-technician"
                      v-model="reopenTechnician"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option :value="null">Choisir un technicien</option>
                      <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                        {{ tech.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="reopenAndReassign"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              🔄 Réouvrir et assigner
            </button>
            <button
              type="button"
              @click="showReopenModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Tickets/StatusBadge.vue'
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue'
import SlaBadge from '@/Components/SlaBadge.vue'
import EvaluationModal from '@/Components/Tickets/EvaluationModal.vue'
import ResolveTicketModal from '@/Components/Tickets/ResolveTicketModal.vue'
import { useSwal } from '@/composables/useSwal'

const { confirm, success, error, toast } = useSwal()

const props = defineProps({
  ticket: Object,
  technicians: Array,
  canEdit: Boolean,
  canComment: Boolean,
  canAssign: Boolean
})

// États réactifs
const newComment = ref('')
const isInternalComment = ref(false)
const addingComment = ref(false)
const selectedTechnician = ref(props.ticket.assigned_to)
const selectedStatus = ref(props.ticket.status)
const selectedFile = ref(null)
const uploading = ref(false)
const fileInput = ref(null)
const showEvaluationModal = ref(false)
const showResolveModal = ref(false)
const showReopenModal = ref(false)
const reopenTechnician = ref(null)

// Méthodes
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const addComment = () => {
  if (!newComment.value.trim()) return

  addingComment.value = true
  
  router.post(route('tickets.comment', props.ticket.id), {
    comment: newComment.value,
    is_internal: isInternalComment.value
  }, {
    onSuccess: () => {
      newComment.value = ''
      isInternalComment.value = false
    },
    onFinish: () => {
      addingComment.value = false
    }
  })
}

const assignTicket = () => {
  if (!selectedTechnician.value) return

  router.post(route('tickets.assign', props.ticket.id), {
    assigned_to: selectedTechnician.value
  })
}

const updateStatus = () => {
  router.post(route('tickets.status', props.ticket.id), {
    status: selectedStatus.value
  })
}

const quickUpdateStatus = async (status) => {
  const messages = {
    'in_progress': {
      title: 'Démarrer l\'intervention',
      text: 'Voulez-vous démarrer le travail sur ce ticket ?',
      icon: 'info',
      confirmButtonText: 'Oui, démarrer'
    },
    'resolved': {
      title: 'Marquer comme résolu',
      text: 'Confirmer que ce ticket est résolu ?',
      icon: 'success',
      confirmButtonText: 'Oui, résolu'
    },
    'closed': {
      title: 'Clôturer le ticket',
      text: 'Voulez-vous clôturer définitivement ce ticket ?',
      icon: 'warning',
      confirmButtonText: 'Oui, clôturer'
    },
    'cancelled': {
      title: 'Annuler le ticket',
      text: 'Êtes-vous sûr de vouloir annuler ce ticket ?',
      icon: 'warning',
      confirmButtonText: 'Oui, annuler'
    },
    'pending': {
      title: 'Remettre en attente',
      text: 'Remettre ce ticket en attente ?',
      icon: 'question',
      confirmButtonText: 'Oui, remettre en attente'
    }
  };

  const config = messages[status] || {
    title: 'Changer le statut',
    text: `Changer le statut vers "${status}" ?`,
    icon: 'question',
    confirmButtonText: 'Confirmer'
  };

  const result = await confirm(config);

  if (result.isConfirmed) {
    router.post(route('tickets.status', props.ticket.id), {
      status: status
    }, {
      preserveScroll: false,
      onSuccess: () => {
        selectedStatus.value = status;
        toast('Statut mis à jour avec succès !', 'success');
      },
      onError: (errors) => {
        console.error('Erreur:', errors);
        error('Erreur lors de la mise à jour du statut. Vérifiez vos permissions.');
      }
    })
  }
}

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0]
}

const uploadFile = () => {
  if (!selectedFile.value) return

  uploading.value = true
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)

  router.post(route('tickets.attachment', props.ticket.id), formData, {
    onSuccess: () => {
      selectedFile.value = null
      fileInput.value.value = ''
    },
    onFinish: () => {
      uploading.value = false
    }
  })
}

const deleteAttachment = (attachment) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
    router.delete(route('attachments.destroy', attachment.id))
  }
}

const getRatingLabel = (rating) => {
  const labels = {
    1: 'Très insatisfait',
    2: 'Insatisfait',
    3: 'Neutre',
    4: 'Satisfait',
    5: 'Très satisfait'
  }
  return labels[rating] || 'Non évalué'
}

const canDeleteAttachment = (attachment) => {
  return props.canEdit || attachment.user_id === $page.props.auth.user.id
}

const reopenAndReassign = async () => {
  if (!reopenTechnician.value) {
    error('Veuillez sélectionner un technicien')
    return
  }

  showReopenModal.value = false

  // D'abord réouvrir le ticket (cancelled -> pending)
  router.post(route('tickets.status', props.ticket.id), {
    status: 'pending'
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Puis assigner au nouveau technicien
      router.post(route('tickets.assign', props.ticket.id), {
        assigned_to: reopenTechnician.value
      }, {
        preserveScroll: false,
        onSuccess: () => {
          success('Ticket réouvert et réassigné avec succès !')
          reopenTechnician.value = null
        },
        onError: () => {
          error('Erreur lors de la réassignation')
        }
      })
    },
    onError: () => {
      error('Erreur lors de la réouverture du ticket')
    }
  })
}
</script>
                  