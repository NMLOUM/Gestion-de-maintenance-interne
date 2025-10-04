<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue';
import StatusBadge from '@/Components/Tickets/StatusBadge.vue';

const props = defineProps({
    assignedTickets: Array,
    stats: Object
});

// Séparer les tickets par statut pour une meilleure organisation
const pendingTickets = computed(() =>
    props.assignedTickets?.filter(t => t.status === 'pending') || []
);

const inProgressTickets = computed(() =>
    props.assignedTickets?.filter(t => t.status === 'in_progress') || []
);

const criticalTickets = computed(() =>
    props.assignedTickets?.filter(t => t.priority === 'critical' && t.status !== 'resolved') || []
);

// Helper pour afficher le temps écoulé
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

// Méthodes d'action
const startTicket = (ticketId) => {
    if (confirm('Êtes-vous sûr de vouloir démarrer cette intervention ?')) {
        router.post(route('tickets.status', ticketId), {
            status: 'in_progress'
        }, {
            preserveScroll: false,
            onSuccess: () => {
                alert('✅ Intervention démarrée !');
            },
            onError: (errors) => {
                console.error('Erreur:', errors);
                alert('❌ Erreur lors du démarrage');
            }
        });
    }
};

const resolveTicket = (ticketId) => {
    if (confirm('Êtes-vous sûr que ce ticket est résolu ?')) {
        router.post(route('tickets.status', ticketId), {
            status: 'resolved'
        }, {
            preserveScroll: false,
            onSuccess: () => {
                alert('✅ Ticket résolu avec succès !');
            },
            onError: (errors) => {
                console.error('Erreur:', errors);
                alert('❌ Erreur lors de la résolution');
            }
        });
    }
};
</script>

<template>
    <Head title="Dashboard Technicien" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mon Espace Technicien</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 📊 MA CHARGE DE TRAVAIL -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Total actifs -->
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Mes tickets actifs</p>
                                <p class="text-3xl font-bold">{{ stats?.my_assigned_tickets || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En attente démarrage -->
                    <div class="bg-white border-2 border-yellow-400 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">En attente démarrage</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ stats?.pending_tickets || 0 }}</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En cours -->
                    <div class="bg-white border-2 border-blue-400 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">En cours</p>
                                <p class="text-3xl font-bold text-blue-600">{{ stats?.in_progress_tickets || 0 }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Critiques à traiter -->
                    <div class="bg-white border-2 border-red-500 rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Critiques à traiter</p>
                                <p class="text-3xl font-bold text-red-600">{{ stats?.critical_tickets || 0 }}</p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🎯 PRIORITÉS DU JOUR - Vue Kanban -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        Priorités du jour
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Colonne URGENT (SLA < 2h ou critique) -->
                        <div class="bg-red-50 rounded-lg p-4 border-2 border-red-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-red-900 flex items-center">
                                    <span class="text-2xl mr-2">🔴</span>
                                    URGENT
                                </h4>
                                <span class="bg-red-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                    {{ criticalTickets.length }}
                                </span>
                            </div>
                            <p class="text-xs text-red-700 mb-3">SLA &lt; 2h ou critiques</p>
                            <div class="space-y-2">
                                <div v-if="criticalTickets.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                    <svg class="mx-auto h-8 w-8 text-green-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aucun ticket urgent
                                </div>
                                <div v-for="ticket in criticalTickets.slice(0, 3)" :key="ticket.id"
                                     class="bg-white rounded-lg p-3 border border-red-300 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs font-mono text-gray-600">{{ ticket.ticket_number }}</span>
                                        <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded">CRITIQUE</span>
                                    </div>
                                    <h5 class="font-semibold text-sm text-gray-900 mb-2 line-clamp-2">{{ ticket.title }}</h5>
                                    <div class="text-xs text-gray-600 mb-2">
                                        👤 {{ ticket.requester?.name }}
                                    </div>
                                    <div v-if="ticket.sla_status" class="mb-2">
                                        <div class="text-xs font-semibold"
                                             :class="{
                                                 'text-red-700': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                                                 'text-orange-700': ticket.sla_status.status === 'warning',
                                                 'text-green-700': ticket.sla_status.status === 'ok'
                                             }">
                                            {{ ticket.sla_status.formatted }}
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <Link :href="route('tickets.show', ticket.id)"
                                              class="flex-1 px-2 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 text-center">
                                            👁️ Voir
                                        </Link>
                                        <button v-if="ticket.status === 'pending'"
                                                @click="startTicket(ticket.id)"
                                                class="flex-1 px-2 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                                            🚀 Démarrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne À DÉMARRER -->
                        <div class="bg-yellow-50 rounded-lg p-4 border-2 border-yellow-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-yellow-900 flex items-center">
                                    <span class="text-2xl mr-2">🟡</span>
                                    À DÉMARRER
                                </h4>
                                <span class="bg-yellow-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                    {{ pendingTickets.length }}
                                </span>
                            </div>
                            <p class="text-xs text-yellow-700 mb-3">Non démarrés</p>
                            <div class="space-y-2">
                                <div v-if="pendingTickets.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                    <svg class="mx-auto h-8 w-8 text-green-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Tout est démarré
                                </div>
                                <div v-for="ticket in pendingTickets.slice(0, 3)" :key="ticket.id"
                                     class="bg-white rounded-lg p-3 border border-yellow-300 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs font-mono text-gray-600">{{ ticket.ticket_number }}</span>
                                        <PriorityBadge :priority="ticket.priority" />
                                    </div>
                                    <h5 class="font-semibold text-sm text-gray-900 mb-2 line-clamp-2">{{ ticket.title }}</h5>
                                    <div class="text-xs text-gray-600 mb-2">
                                        👤 {{ ticket.requester?.name }}
                                    </div>
                                    <div v-if="ticket.sla_status" class="mb-2">
                                        <div class="text-xs font-semibold"
                                             :class="{
                                                 'text-red-700': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                                                 'text-orange-700': ticket.sla_status.status === 'warning',
                                                 'text-green-700': ticket.sla_status.status === 'ok'
                                             }">
                                            SLA: {{ ticket.sla_status.formatted }}
                                        </div>
                                    </div>
                                    <button @click="startTicket(ticket.id)"
                                            class="w-full px-2 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                                        🚀 Démarrer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne EN COURS -->
                        <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-blue-900 flex items-center">
                                    <span class="text-2xl mr-2">🔵</span>
                                    EN COURS
                                </h4>
                                <span class="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                    {{ inProgressTickets.length }}
                                </span>
                            </div>
                            <p class="text-xs text-blue-700 mb-3">À finaliser</p>
                            <div class="space-y-2">
                                <div v-if="inProgressTickets.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    Aucun ticket en cours
                                </div>
                                <div v-for="ticket in inProgressTickets.slice(0, 3)" :key="ticket.id"
                                     class="bg-white rounded-lg p-3 border border-blue-300 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs font-mono text-gray-600">{{ ticket.ticket_number }}</span>
                                        <PriorityBadge :priority="ticket.priority" />
                                    </div>
                                    <h5 class="font-semibold text-sm text-gray-900 mb-2 line-clamp-2">{{ ticket.title }}</h5>
                                    <div class="text-xs text-gray-600 mb-2">
                                        👤 {{ ticket.requester?.name }}<br>
                                        ⏱️ Démarré {{ getTimeAgo(ticket.updated_at) }}
                                    </div>
                                    <div v-if="ticket.sla_status" class="mb-2">
                                        <div class="text-xs font-semibold"
                                             :class="{
                                                 'text-red-700': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                                                 'text-orange-700': ticket.sla_status.status === 'warning',
                                                 'text-green-700': ticket.sla_status.status === 'ok'
                                             }">
                                            {{ ticket.sla_status.formatted }}
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <Link :href="route('tickets.show', ticket.id)"
                                              class="flex-1 px-2 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 text-center">
                                            👁️ Voir
                                        </Link>
                                        <button @click="resolveTicket(ticket.id)"
                                                class="flex-1 px-2 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                            ✅ Résoudre
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📋 TOUS MES TICKETS - Liste détaillée -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">📋 Tous mes tickets ({{ assignedTickets?.length || 0 }})</h3>
                            <Link :href="route('tickets.index')"
                                  class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Voir tout →
                            </Link>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="assignedTickets && assignedTickets.length > 0" class="space-y-3">
                            <div v-for="ticket in assignedTickets.slice(0, 10)" :key="ticket.id"
                                 class="border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-indigo-300 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-sm font-mono text-gray-600">{{ ticket.ticket_number }}</span>
                                            <PriorityBadge :priority="ticket.priority" />
                                            <StatusBadge :status="ticket.status" />
                                        </div>
                                        <h4 class="text-base font-semibold text-gray-900 mb-2">{{ ticket.title }}</h4>
                                        <div class="flex items-center text-sm text-gray-600 space-x-4 mb-2">
                                            <span>👤 {{ ticket.requester?.name }}</span>
                                            <span>⏱️ {{ getTimeAgo(ticket.created_at) }}</span>
                                        </div>
                                        <div v-if="ticket.sla_status" class="text-xs font-semibold"
                                             :class="{
                                                 'text-red-700': ticket.sla_status.status === 'overdue' || ticket.sla_status.status === 'critical',
                                                 'text-orange-700': ticket.sla_status.status === 'warning',
                                                 'text-green-700': ticket.sla_status.status === 'ok'
                                             }">
                                            SLA: {{ ticket.sla_status.formatted }}
                                        </div>
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <Link :href="route('tickets.show', ticket.id)"
                                              class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                                            👁️ Voir
                                        </Link>
                                        <button v-if="ticket.status === 'pending'"
                                                @click="startTicket(ticket.id)"
                                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                            🚀 Démarrer
                                        </button>
                                        <button v-if="ticket.status === 'in_progress'"
                                                @click="resolveTicket(ticket.id)"
                                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                            ✅ Résoudre
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Aucun ticket assigné</p>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">⚡ Actions rapides</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <Link :href="route('tickets.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-4 text-center transition">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Tous mes tickets</span>
                        </Link>
                        <Link :href="route('tickets.index', { status: 'pending' })"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-4 text-center transition">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">À démarrer</span>
                        </Link>
                        <Link :href="route('tickets.index', { status: 'in_progress' })"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-4 text-center transition">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">En cours</span>
                        </Link>
                        <Link :href="route('tickets.index', { priority: 'critical' })"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg p-4 text-center transition">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Critiques</span>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
