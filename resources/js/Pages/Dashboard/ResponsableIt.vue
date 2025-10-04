<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue';
import StatusBadge from '@/Components/Tickets/StatusBadge.vue';
import AssignTicketModal from '@/Components/Tickets/AssignTicketModal.vue';

const props = defineProps({
    criticalTickets: Array,
    unassignedTickets: Array,
    technicianPerformance: Array,
    ticketsByCategory: Array,
    ticketsByPriority: Object,
    overdueTickets: Array,
    stats: Object,
    technicians: Array,
    recentActivities: Array
});

const showAssignModal = ref(false);
const selectedTicket = ref(null);
const activeTab = ref('overview'); // overview, team, tickets, activities

// Calculer la tendance (comparaison avec le mois précédent)
const ticketTrend = computed(() => {
    if (!props.stats) return null;
    const current = props.stats.total_this_month || 0;
    const previous = props.stats.total_last_month || 0;
    if (previous === 0) return 0;
    return Math.round(((current - previous) / previous) * 100);
});

const openAssignModal = (ticket) => {
    selectedTicket.value = ticket;
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedTicket.value = null;
};

const getTimeAgo = (date) => {
    const now = new Date();
    const created = new Date(date);
    const diffMs = now - created;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays > 0) return `il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`;
    if (diffHours > 0) return `il y a ${diffHours}h`;
    return 'à l\'instant';
};
</script>

<template>
    <Head title="Dashboard Responsable IT" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Centre de Gestion IT</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 📊 VUE D'ENSEMBLE OPÉRATIONNELLE -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Total actifs -->
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Tickets actifs</p>
                                <p class="text-3xl font-bold">{{ (stats?.pending_tickets || 0) + (stats?.in_progress_tickets || 0) }}</p>
                                <p class="text-xs text-indigo-200 mt-1">
                                    {{ stats?.pending_tickets || 0 }} en attente, {{ stats?.in_progress_tickets || 0 }} en cours
                                </p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V9zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0v-4zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Action requise -->
                    <div class="bg-white border-2 border-red-400 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Action requise</p>
                                <p class="text-3xl font-bold text-red-600">{{ (stats?.unassigned_tickets || 0) + (stats?.critical_tickets || 0) }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ stats?.unassigned_tickets || 0 }} non assignés, {{ stats?.critical_tickets || 0 }} critiques
                                </p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Temps moyen -->
                    <div class="bg-white border-2 border-blue-400 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Temps moyen</p>
                                <p class="text-3xl font-bold text-blue-600">{{ stats?.avg_resolution_time || 0 }}h</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Résolution moyenne
                                </p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Résolus ce mois -->
                    <div class="bg-white border-2 border-green-400 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Résolus ce mois</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats?.resolved_this_month || 0 }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Sur {{ stats?.total_this_month || 0 }} créés
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🔖 ONGLETS DE NAVIGATION -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'overview'"
                                    :class="activeTab === 'overview'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                                📊 Vue d'ensemble
                            </button>
                            <button @click="activeTab = 'team'"
                                    :class="activeTab === 'team'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                                👥 Performance équipe
                            </button>
                            <button @click="activeTab = 'tickets'"
                                    :class="activeTab === 'tickets'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                                🎫 Tickets nécessitant action
                            </button>
                            <button @click="activeTab = 'activities'"
                                    :class="activeTab === 'activities'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                                📜 Historique récent
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- TAB: VUE D'ENSEMBLE -->
                        <div v-show="activeTab === 'overview'" class="space-y-6">
                            <div v-if="criticalTickets && criticalTickets.length > 0" class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
                                <h4 class="font-bold text-red-900 mb-3">🔴 Tickets critiques ({{ criticalTickets.length }})</h4>
                                <div class="space-y-2">
                                    <div v-for="ticket in criticalTickets.slice(0, 3)" :key="ticket.id"
                                         class="bg-white rounded-lg p-3 border border-red-200">
                                        <div class="flex justify-between items-center">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900">{{ ticket.title }}</div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ ticket.requester?.name }} • {{ getTimeAgo(ticket.created_at) }}
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <Link :href="route('tickets.show', ticket.id)"
                                                      class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                                                    Voir
                                                </Link>
                                                <button @click="openAssignModal(ticket)"
                                                        class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                    Assigner
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="unassignedTickets && unassignedTickets.length > 0" class="bg-orange-50 border-2 border-orange-300 rounded-lg p-4">
                                <h4 class="font-bold text-orange-900 mb-3">⚠️ Tickets non assignés ({{ unassignedTickets.length }})</h4>
                                <div class="space-y-2">
                                    <div v-for="ticket in unassignedTickets.slice(0, 3)" :key="ticket.id"
                                         class="bg-white rounded-lg p-3 border border-orange-200">
                                        <div class="flex justify-between items-center">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900">{{ ticket.title }}</div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    <PriorityBadge :priority="ticket.priority" class="inline-block mr-2" />
                                                    {{ ticket.requester?.name }}
                                                </div>
                                            </div>
                                            <button @click="openAssignModal(ticket)"
                                                    class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                Assigner
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: PERFORMANCE ÉQUIPE -->
                        <div v-show="activeTab === 'team'">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Performance de l'équipe</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technicien</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tickets actifs</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total assignés</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Résolus</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Taux résolution</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Temps moyen</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">État</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="tech in technicianPerformance" :key="tech.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                                        {{ tech.name.charAt(0) }}
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ tech.name }}</div>
                                                        <div class="text-xs text-gray-500">{{ tech.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <span class="px-2 py-1 text-sm font-semibold text-blue-700 bg-blue-100 rounded-full">
                                                    {{ tech.active_tickets || 0 }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900">
                                                {{ tech.total_assigned || 0 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-green-600 font-medium">
                                                {{ tech.resolved_count || 0 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center">
                                                    <div class="text-sm font-semibold"
                                                         :class="{
                                                             'text-green-600': tech.resolution_rate >= 70,
                                                             'text-yellow-600': tech.resolution_rate >= 50 && tech.resolution_rate < 70,
                                                             'text-red-600': tech.resolution_rate < 50
                                                         }">
                                                        {{ tech.resolution_rate || 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-600">
                                                {{ tech.avg_resolution_hours || 0 }}h
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <span v-if="tech.active_tickets > 5"
                                                      class="px-2 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full">
                                                    Surchargé
                                                </span>
                                                <span v-else-if="tech.active_tickets > 2"
                                                      class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                                                    Actif
                                                </span>
                                                <span v-else
                                                      class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                    Disponible
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB: TICKETS NÉCESSITANT ACTION -->
                        <div v-show="activeTab === 'tickets'" class="space-y-4">
                            <!-- Tickets critiques -->
                            <div v-if="criticalTickets && criticalTickets.length > 0">
                                <h4 class="font-bold text-gray-900 mb-3">🔴 Tickets critiques</h4>
                                <div class="space-y-3">
                                    <div v-for="ticket in criticalTickets" :key="ticket.id"
                                         class="bg-white border border-red-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded">CRITIQUE</span>
                                                    <StatusBadge :status="ticket.status" />
                                                </div>
                                                <h4 class="text-base font-semibold text-gray-900 mb-1">{{ ticket.title }}</h4>
                                                <div class="flex items-center text-sm text-gray-600 space-x-4">
                                                    <span>👤 {{ ticket.requester?.name }}</span>
                                                    <span>⏱️ {{ getTimeAgo(ticket.created_at) }}</span>
                                                    <span v-if="ticket.assignedUser" class="text-green-600">
                                                        ✓ Assigné à {{ ticket.assignedUser.name }}
                                                    </span>
                                                    <span v-else class="text-red-600 font-medium">❌ Non assigné</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2 ml-4">
                                                <Link :href="route('tickets.show', ticket.id)"
                                                      class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                                    👁️ Voir
                                                </Link>
                                                <button @click="openAssignModal(ticket)"
                                                        class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                                    {{ ticket.assignedUser ? '🔄 Réaffecter' : '👤 Assigner' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tickets non assignés -->
                            <div v-if="unassignedTickets && unassignedTickets.length > 0">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-bold text-gray-900">⚠️ Tickets non assignés</h4>
                                    <Link :href="route('tickets.index', { assigned_to: 'unassigned' })"
                                          class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        Voir tous →
                                    </Link>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="ticket in unassignedTickets" :key="ticket.id"
                                         class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <PriorityBadge :priority="ticket.priority" />
                                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded">NON ASSIGNÉ</span>
                                                </div>
                                                <h4 class="text-base font-semibold text-gray-900 mb-1">{{ ticket.title }}</h4>
                                                <div class="flex items-center text-sm text-gray-600 space-x-4">
                                                    <span>📁 {{ ticket.category?.name }}</span>
                                                    <span>👤 {{ ticket.requester?.name }}</span>
                                                    <span>⏱️ {{ getTimeAgo(ticket.created_at) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2 ml-4">
                                                <Link :href="route('tickets.show', ticket.id)"
                                                      class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                                                    👁️ Voir
                                                </Link>
                                                <button @click="openAssignModal(ticket)"
                                                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                                    👤 Assigner maintenant
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tickets en retard -->
                            <div v-if="overdueTickets && overdueTickets.length > 0">
                                <h4 class="font-bold text-gray-900 mb-3">⏰ Tickets en retard</h4>
                                <div class="space-y-3">
                                    <div v-for="ticket in overdueTickets" :key="ticket.id"
                                         class="bg-white border border-yellow-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <PriorityBadge :priority="ticket.priority" />
                                                    <StatusBadge :status="ticket.status" />
                                                </div>
                                                <h4 class="text-base font-semibold text-gray-900 mb-1">{{ ticket.title }}</h4>
                                                <div class="flex items-center text-sm text-gray-600 space-x-4">
                                                    <span>👤 {{ ticket.requester?.name }}</span>
                                                    <span>⏱️ {{ getTimeAgo(ticket.created_at) }}</span>
                                                    <span v-if="ticket.assignedUser" class="text-blue-600">
                                                        Assigné à {{ ticket.assignedUser.name }}
                                                    </span>
                                                </div>
                                            </div>
                                            <Link :href="route('tickets.show', ticket.id)"
                                                  class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                                👁️ Voir
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: HISTORIQUE RÉCENT -->
                        <div v-show="activeTab === 'activities'">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📜 Activités récentes du système</h3>

                            <div v-if="recentActivities && recentActivities.length > 0" class="space-y-3">
                                <div v-for="activity in recentActivities" :key="activity.id"
                                     class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start space-x-3">
                                        <!-- Icon selon le type d'action -->
                                        <div class="flex-shrink-0">
                                            <div v-if="activity.action === 'status_changed'"
                                                 class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div v-else-if="activity.action === 'comment_added'"
                                                 class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div v-else-if="activity.action === 'attachment_added'"
                                                 class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div v-else-if="activity.action === 'ticket_assigned'"
                                                 class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                                </svg>
                                            </div>
                                            <div v-else
                                                 class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Contenu -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ activity.user_name }}
                                                </p>
                                                <span class="text-xs text-gray-500">
                                                    {{ getTimeAgo(activity.created_at) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-700 mb-2">{{ activity.description }}</p>
                                            <div class="flex items-center space-x-3 text-xs">
                                                <Link :href="route('tickets.show', activity.ticket_id)"
                                                      class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                    {{ activity.ticket_number }}
                                                </Link>
                                                <span class="text-gray-500">•</span>
                                                <span class="text-gray-600">{{ activity.ticket_title }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500">Aucune activité récente</p>
                            </div>

                            <div class="mt-6 text-center">
                                <Link :href="route('history.index')"
                                      class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Voir l'historique complet
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⚡ ACTIONS RAPIDES -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">⚡ Actions rapides</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <Link :href="route('tickets.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">Tous les tickets</span>
                        </Link>

                        <Link :href="route('history.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">Historique complet</span>
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal d'assignation -->
        <AssignTicketModal
            :show="showAssignModal"
            :ticket-id="selectedTicket?.id"
            :ticket-title="selectedTicket?.title"
            :current-assignee="selectedTicket?.assignedUser"
            :technicians="technicians"
            @close="closeAssignModal"
        />
    </AuthenticatedLayout>
</template>
