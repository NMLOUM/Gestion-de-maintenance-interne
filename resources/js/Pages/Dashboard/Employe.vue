<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue';
import StatusBadge from '@/Components/Tickets/StatusBadge.vue';
import SlaBadge from '@/Components/SlaBadge.vue';

const props = defineProps({
    myTickets: Array,
    stats: Object
});

const activeTab = ref('tickets');
let refreshInterval = null;

// Rafraîchir les données toutes les 60 secondes
onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['myTickets', 'stats'] });
    }, 60000); // 60 secondes
});

// Nettoyer l'intervalle quand le composant est détruit
onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});

// Séparer les tickets par statut
const activeTickets = computed(() =>
    props.myTickets?.filter(t => !['resolved', 'closed'].includes(t.status)) || []
);

const resolvedTickets = computed(() =>
    props.myTickets?.filter(t => t.status === 'resolved') || []
);

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

const getStatusLabel = (status) => {
    const labels = {
        'pending': '⏱️ En attente d\'assignation',
        'in_progress': '🔧 En cours de traitement',
        'resolved': '✅ Résolu - En attente de validation',
        'closed': '✓ Fermé',
        'cancelled': '❌ Annulé'
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Mes Demandes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes Demandes de Maintenance</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 📊 Statistiques en cartes -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Total -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-lg p-6 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-700 text-sm font-medium">Mes tickets</p>
                                <p class="text-3xl font-bold text-blue-800">{{ stats?.my_tickets || 0 }}</p>
                            </div>
                            <div class="bg-blue-200 rounded-full p-3">
                                <svg class="w-8 h-8 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En attente -->
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg shadow-lg p-6 border border-amber-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-700 text-sm font-medium">En attente</p>
                                <p class="text-3xl font-bold text-amber-800">{{ stats?.pending || 0 }}</p>
                            </div>
                            <div class="bg-amber-200 rounded-full p-3">
                                <svg class="w-8 h-8 text-amber-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En cours -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-lg p-6 border border-orange-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-700 text-sm font-medium">En cours</p>
                                <p class="text-3xl font-bold text-orange-800">{{ stats?.in_progress || 0 }}</p>
                            </div>
                            <div class="bg-orange-200 rounded-full p-3">
                                <svg class="w-8 h-8 text-orange-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Résolus -->
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg shadow-lg p-6 border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-700 text-sm font-medium">Résolus</p>
                                <p class="text-3xl font-bold text-emerald-800">{{ stats?.resolved || 0 }}</p>
                            </div>
                            <div class="bg-emerald-200 rounded-full p-3">
                                <svg class="w-8 h-8 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation par onglets -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <nav class="flex -mb-px border-b border-gray-200">
                        <button @click="activeTab = 'tickets'"
                                :class="activeTab === 'tickets'
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm transition">
                            🎫 Mes tickets ({{ activeTickets.length + resolvedTickets.length }})
                        </button>
                        <button @click="activeTab = 'faq'"
                                :class="activeTab === 'faq'
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm transition">
                            ❓ Aide & FAQ
                        </button>
                    </nav>

                    <!-- Tab: Mes tickets -->
                    <div v-show="activeTab === 'tickets'" class="p-6">
                        <!-- 🟡 Mes tickets en cours -->
                        <div v-if="activeTickets.length > 0" class="mb-6">
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">🔔 Mes demandes en cours ({{ activeTickets.length }})</h3>
                            </div>
                            <div class="space-y-4">
                        <div v-for="ticket in activeTickets" :key="ticket.id"
                             class="border border-gray-200 rounded-lg p-5 hover:shadow-md hover:border-indigo-300 transition">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-sm font-mono text-gray-600">#{{ ticket.id }}</span>
                                        <PriorityBadge :priority="ticket.priority" />
                                        <SlaBadge :ticket="ticket" />
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ ticket.title }}</h4>
                                    <p class="text-sm text-gray-600 mb-3">{{ getStatusLabel(ticket.status) }}</p>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        <span>⏱️ Créé {{ getTimeAgo(ticket.created_at) }}</span>
                                        <span v-if="ticket.assignedUser" class="text-indigo-600">
                                            👤 Assigné à {{ ticket.assignedUser.name }}
                                        </span>
                                        <span v-else class="text-yellow-600">
                                            ⏳ En attente d'assignation
                                        </span>
                                    </div>

                                    <!-- Dernier commentaire -->
                                    <div v-if="ticket.last_comment" class="mt-3 bg-gray-50 border-l-4 border-indigo-400 p-3 rounded">
                                        <div class="flex items-center text-xs text-gray-600 mb-1">
                                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="font-medium">{{ ticket.last_comment.user?.name }}</span>
                                            <span class="mx-1">•</span>
                                            <span>{{ getTimeAgo(ticket.last_comment.created_at) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 line-clamp-2">{{ ticket.last_comment.comment }}</p>
                                    </div>
                                </div>
                                <Link :href="route('tickets.show', ticket.id)"
                                      class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 ml-4">
                                    👁️ Voir détails
                                </Link>
                            </div>

                            <!-- Barre de progression visuelle -->
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>Progression</span>
                                    <span>
                                        <span v-if="ticket.status === 'pending'">Étape 1/3</span>
                                        <span v-else-if="ticket.status === 'in_progress'">Étape 2/3</span>
                                        <span v-else-if="ticket.status === 'resolved'">Étape 3/3</span>
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all"
                                         :class="{
                                             'bg-yellow-500 w-1/3': ticket.status === 'pending',
                                             'bg-orange-500 w-2/3': ticket.status === 'in_progress',
                                             'bg-green-500 w-full': ticket.status === 'resolved'
                                         }">
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span :class="{ 'font-semibold text-gray-900': ticket.status === 'pending' }">Créé</span>
                                    <span :class="{ 'font-semibold text-gray-900': ticket.status === 'in_progress' }">En cours</span>
                                    <span :class="{ 'font-semibold text-gray-900': ticket.status === 'resolved' }">Résolu</span>
                                </div>
                            </div>
                        </div>
                        </div>
                        </div>

                        <!-- ✅ Tickets résolus à valider -->
                        <div v-if="resolvedTickets.length > 0" class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-lg font-bold text-green-900">✅ Tickets résolus - À valider ({{ resolvedTickets.length }})</h3>
                    </div>
                    <p class="text-sm text-green-700 mb-4">Ces tickets ont été résolus par nos techniciens. Veuillez les consulter et valider la résolution.</p>
                    <div class="space-y-3">
                        <div v-for="ticket in resolvedTickets" :key="ticket.id"
                             class="bg-white border border-green-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-sm font-mono text-gray-600">#{{ ticket.id }}</span>
                                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded">RÉSOLU</span>
                                    </div>
                                    <h4 class="text-base font-semibold text-gray-900 mb-1">{{ ticket.title }}</h4>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        <span>👤 Résolu par {{ ticket.assignedUser?.name }}</span>
                                        <span>⏱️ {{ getTimeAgo(ticket.updated_at) }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <Link :href="route('tickets.show', ticket.id)"
                                          class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                        👁️ Voir & Valider
                                    </Link>
                                </div>
                            </div>
                        </div>
                        </div>
                        </div>

                        <!-- Message si aucun ticket -->
                        <div v-if="!myTickets || myTickets.length === 0"
                             class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune demande en cours</h3>
                    <p class="text-gray-500 mb-6">Vous n'avez pas encore créé de ticket de maintenance</p>
                    <Link :href="route('tickets.create')"
                          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer ma première demande
                    </Link>
                        </div>

                        <!-- Actions rapides -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Actions rapides
                            </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tous mes tickets -->
                    <Link :href="route('tickets.index')"
                          class="group relative bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6 hover:border-blue-400 hover:shadow-xl hover:scale-105 transition-all duration-300 text-center overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform duration-300 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-1 text-lg">📋 Tous mes tickets</h4>
                            <p class="text-sm text-gray-600">Historique complet</p>
                        </div>
                    </Link>

                    <!-- Nouveau ticket -->
                    <Link :href="route('tickets.create')"
                          class="group relative bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 hover:border-green-400 hover:shadow-xl hover:scale-105 transition-all duration-300 text-center overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-green-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform duration-300 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-1 text-lg">➕ Nouveau ticket</h4>
                            <p class="text-sm text-gray-600">Créer une demande</p>
                        </div>
                    </Link>

                    <!-- Aide & FAQ -->
                    <button @click="activeTab = 'faq'"
                          class="group relative bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl p-6 hover:border-purple-400 hover:shadow-xl hover:scale-105 transition-all duration-300 text-center overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-purple-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform duration-300 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-1 text-lg">❓ Aide & FAQ</h4>
                            <p class="text-sm text-gray-600">Questions fréquentes</p>
                        </div>
                    </button>
                        </div>
                        </div>
                    </div>

                    <!-- Tab: FAQ -->
                    <div v-show="activeTab === 'faq'" class="p-6">
                        <div class="flex items-center mb-6">
                        <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">❓ Aide & Questions Fréquentes</h3>
                    </div>

                    <div class="space-y-4">
                        <!-- FAQ Item 1 -->
                        <div class="bg-white rounded-lg p-4 border border-indigo-100">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                Comment créer un ticket de maintenance ?
                            </h4>
                            <p class="text-sm text-gray-600">Cliquez sur "Nouveau Ticket" en haut de cette page, remplissez le formulaire avec les détails de votre problème, et soumettez. Vous recevrez une notification quand un technicien sera assigné.</p>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div class="bg-white rounded-lg p-4 border border-indigo-100">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                Combien de temps prend le traitement d'un ticket ?
                            </h4>
                            <p class="text-sm text-gray-600">
                                Le délai dépend de la priorité : <strong class="text-red-600">Critique (24h)</strong>,
                                <strong class="text-orange-600">Élevée (3 jours)</strong>,
                                <strong class="text-yellow-600">Normale (7 jours)</strong>,
                                <strong class="text-green-600">Faible (14 jours)</strong>.
                            </p>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="bg-white rounded-lg p-4 border border-indigo-100">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                Comment suivre l'avancement de mon ticket ?
                            </h4>
                            <p class="text-sm text-gray-600">Cliquez sur "Voir détails" sur n'importe quel ticket pour voir son statut, les commentaires des techniciens, et l'historique complet. Vous pouvez également ajouter des commentaires pour plus d'informations.</p>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="bg-white rounded-lg p-4 border border-indigo-100">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                Que faire quand mon ticket est résolu ?
                            </h4>
                            <p class="text-sm text-gray-600">Consultez le ticket résolu dans la section "Tickets résolus - À valider", vérifiez que le problème est bien résolu, puis validez la résolution. Si le problème persiste, vous pouvez le signaler dans les commentaires.</p>
                        </div>

                        <!-- Contact Support -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-4 text-white">
                            <h4 class="font-semibold mb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                </svg>
                                Besoin d'aide supplémentaire ?
                            </h4>
                            <p class="text-sm text-indigo-100">Contactez le service IT pour toute question urgente ou non couverte par la FAQ.</p>
                        </div>
                    </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
