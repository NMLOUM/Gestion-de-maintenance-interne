<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue';
import StatusBadge from '@/Components/Tickets/StatusBadge.vue';
import SlaBadge from '@/Components/SlaBadge.vue';
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
    recentActivities: Array,
    slaPerformance: Object
});

const showAssignModal = ref(false);
const selectedTicket = ref(null);
const activeTab = ref('overview'); // overview, team, tickets, activities
let refreshInterval = null;

// Exposer activeTab globalement pour permettre la navigation depuis le menu
if (typeof window !== 'undefined') {
    window.dashboardActiveTab = activeTab;
}

// Rafraîchir les données toutes les 60 secondes
onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({
            only: ['stats', 'criticalTickets', 'unassignedTickets', 'technicianPerformance',
                   'ticketsByCategory', 'ticketsByPriority', 'overdueTickets', 'recentActivities', 'slaPerformance']
        });
    }, 60000); // 60 secondes
});

// Nettoyer l'intervalle quand le composant est détruit
onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});

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

// Export functionality
const showExportModal = ref(false);
const exportForm = reactive({
    date_from: new Date(new Date().setDate(1)).toISOString().split('T')[0], // Premier jour du mois
    date_to: new Date().toISOString().split('T')[0], // Aujourd'hui
    format: 'pdf'
});

const exportReport = (type) => {
    // Créer un lien temporaire pour télécharger
    const params = new URLSearchParams({
        type: type,
        date_from: exportForm.date_from,
        date_to: exportForm.date_to,
        format: exportForm.format,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    });

    // Ouvrir dans un nouvel onglet
    window.open(route('reports.generate') + '?' + params.toString(), '_blank');
};

const exportSlaReport = () => {
    // Créer un lien temporaire pour télécharger
    const params = new URLSearchParams({
        date_from: exportForm.date_from,
        date_to: exportForm.date_to,
        format: exportForm.format,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    });

    // Ouvrir dans un nouvel onglet
    window.open(route('reports.sla') + '?' + params.toString(), '_blank');
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
                            <button @click="activeTab = 'tickets'"
                                    :class="activeTab === 'tickets'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                                🎫 Tickets nécessitant action
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

                            <!-- Performance des techniciens -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">👥 Performance de l'équipe technique</h3>
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
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                         :class="{
                                                             'text-green-700 bg-green-100': tech.resolution_rate >= 70,
                                                             'text-yellow-700 bg-yellow-100': tech.resolution_rate >= 50 && tech.resolution_rate < 70,
                                                             'text-red-700 bg-red-100': tech.resolution_rate < 50
                                                         }">
                                                        {{ tech.resolution_rate || 0 }}%
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-600">
                                                    {{ tech.avg_resolution_hours || 0 }}h
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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

                        <!-- TAB: PERFORMANCE SLA - REMOVED (already in Direction dashboard) -->
                        <div v-show="activeTab === 'sla'" class="space-y-6" style="display: none;">
                            <!-- Performance SLA Globale -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    ⏱️ Performance SLA Globale
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="bg-white rounded-lg p-4 text-center">
                                        <div class="text-3xl font-bold text-gray-900">{{ slaPerformance?.global?.total || 0 }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Tickets actifs</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 text-center">
                                        <div class="text-3xl font-bold text-green-600">{{ slaPerformance?.global?.on_time || 0 }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Dans les délais</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 text-center">
                                        <div class="text-3xl font-bold text-red-600">{{ slaPerformance?.global?.overdue || 0 }}</div>
                                        <div class="text-sm text-gray-600 mt-1">En retard</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 text-center">
                                        <div class="text-4xl font-bold text-indigo-600">{{ slaPerformance?.global?.on_time_percent || 0 }}%</div>
                                        <div class="text-sm text-gray-600 mt-1">Taux de respect SLA</div>
                                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full transition-all"
                                                 :style="{ width: (slaPerformance?.global?.on_time_percent || 0) + '%' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SLA Details par priorité -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Détails SLA par priorité</h3>
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorité</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SLA</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">À temps</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">En retard</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Respect</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr class="hover:bg-red-50">
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        🔴 Critique
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">24 heures</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold">{{ slaPerformance?.by_priority?.critical?.total || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-green-600">{{ slaPerformance?.by_priority?.critical?.on_time || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-red-600">{{ slaPerformance?.by_priority?.critical?.overdue || 0 }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center justify-center">
                                                        <span class="text-sm font-bold mr-2">{{ slaPerformance?.by_priority?.critical?.on_time_percent || 0 }}%</span>
                                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-green-600 h-2 rounded-full" :style="{ width: (slaPerformance?.by_priority?.critical?.on_time_percent || 0) + '%' }"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-orange-50">
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        🟠 Élevée
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">72 heures (3 jours)</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold">{{ slaPerformance?.by_priority?.high?.total || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-green-600">{{ slaPerformance?.by_priority?.high?.on_time || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-red-600">{{ slaPerformance?.by_priority?.high?.overdue || 0 }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center justify-center">
                                                        <span class="text-sm font-bold mr-2">{{ slaPerformance?.by_priority?.high?.on_time_percent || 0 }}%</span>
                                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-green-600 h-2 rounded-full" :style="{ width: (slaPerformance?.by_priority?.high?.on_time_percent || 0) + '%' }"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-yellow-50">
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        🟡 Normale
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">168 heures (7 jours)</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold">{{ slaPerformance?.by_priority?.normal?.total || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-green-600">{{ slaPerformance?.by_priority?.normal?.on_time || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-red-600">{{ slaPerformance?.by_priority?.normal?.overdue || 0 }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center justify-center">
                                                        <span class="text-sm font-bold mr-2">{{ slaPerformance?.by_priority?.normal?.on_time_percent || 0 }}%</span>
                                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-green-600 h-2 rounded-full" :style="{ width: (slaPerformance?.by_priority?.normal?.on_time_percent || 0) + '%' }"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-green-50">
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        🟢 Faible
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">336 heures (14 jours)</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold">{{ slaPerformance?.by_priority?.low?.total || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-green-600">{{ slaPerformance?.by_priority?.low?.on_time || 0 }}</td>
                                                <td class="px-6 py-4 text-center text-sm font-bold text-red-600">{{ slaPerformance?.by_priority?.low?.overdue || 0 }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center justify-center">
                                                        <span class="text-sm font-bold mr-2">{{ slaPerformance?.by_priority?.low?.on_time_percent || 0 }}%</span>
                                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-green-600 h-2 rounded-full" :style="{ width: (slaPerformance?.by_priority?.low?.on_time_percent || 0) + '%' }"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⚡ ACTIONS RAPIDES -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">⚡ Actions rapides</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button @click="showExportModal = true"
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">📊 Exporter Rapports</span>
                        </button>

                        <Link :href="route('tickets.index', { status: 'in_progress' })"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">🔧 Tickets en cours</span>
                        </Link>

                        <Link :href="route('tickets.index', { assigned_to: 'assigned' })"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="text-sm font-semibold block">👥 Tickets assignés</span>
                        </Link>

                        <Link :href="route('history.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-6 text-center transition hover:scale-105">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">📜 Historique complet</span>
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

        <!-- Modal d'export de rapports -->
        <div v-if="showExportModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showExportModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    📊 Export de Rapports
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Filtres de période -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">📅 Date de début</label>
                                            <input v-model="exportForm.date_from" type="date"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">📅 Date de fin</label>
                                            <input v-model="exportForm.date_to" type="date"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>

                                    <!-- Format -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">📄 Format d'export</label>
                                        <select v-model="exportForm.format"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="pdf">PDF</option>
                                            <option value="excel">Excel</option>
                                        </select>
                                    </div>

                                    <!-- Boutons d'export -->
                                    <div class="border-t pt-4 mt-4">
                                        <p class="text-sm text-gray-600 mb-3">Sélectionnez le type de rapport à exporter :</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button @click="exportReport('summary'); showExportModal = false"
                                                    class="flex items-center justify-center px-4 py-3 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                                </svg>
                                                Rapport Général
                                            </button>

                                            <button @click="exportReport('detailed'); showExportModal = false"
                                                    class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                </svg>
                                                Rapport Détaillé
                                            </button>

                                            <button @click="exportSlaReport(); showExportModal = false"
                                                    class="flex items-center justify-center px-4 py-3 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                Rapport SLA
                                            </button>

                                            <button @click="exportReport('technician'); showExportModal = false"
                                                    class="flex items-center justify-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                                </svg>
                                                Rapport Techniciens
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showExportModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
