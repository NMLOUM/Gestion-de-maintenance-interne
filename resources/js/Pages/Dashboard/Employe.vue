<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PriorityBadge from '@/Components/Tickets/PriorityBadge.vue';
import StatusBadge from '@/Components/Tickets/StatusBadge.vue';

const props = defineProps({
    myTickets: Array,
    stats: Object
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
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Mes tickets</p>
                                <p class="text-3xl font-bold">{{ stats?.my_tickets || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En attente -->
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm font-medium">En attente</p>
                                <p class="text-3xl font-bold">{{ stats?.pending || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- En cours -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">En cours</p>
                                <p class="text-3xl font-bold">{{ stats?.in_progress || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Résolus -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Résolus</p>
                                <p class="text-3xl font-bold">{{ stats?.resolved || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton Créer un ticket -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-lg font-semibold mb-1">Besoin d'aide ?</h3>
                            <p class="text-indigo-100 text-sm">Créez une nouvelle demande de maintenance</p>
                        </div>
                        <Link :href="route('tickets.create')"
                              class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nouveau Ticket
                        </Link>
                    </div>
                </div>

                <!-- 🟡 Mes tickets en cours -->
                <div v-if="activeTickets.length > 0" class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">🔔 Mes demandes en cours ({{ activeTickets.length }})</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="ticket in activeTickets" :key="ticket.id"
                             class="border border-gray-200 rounded-lg p-5 hover:shadow-md hover:border-indigo-300 transition">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-sm font-mono text-gray-600">#{{ ticket.id }}</span>
                                        <PriorityBadge :priority="ticket.priority" />
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
                <div v-if="resolvedTickets.length > 0" class="bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm p-6">
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
                     class="bg-white rounded-lg shadow-sm p-12 text-center">
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link :href="route('tickets.index')"
                          class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 hover:shadow-md transition text-center">
                        <svg class="w-12 h-12 mx-auto text-indigo-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h4 class="font-semibold text-gray-900 mb-1">Tous mes tickets</h4>
                        <p class="text-sm text-gray-600">Voir l'historique complet</p>
                    </Link>

                    <Link :href="route('tickets.create')"
                          class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 hover:shadow-md transition text-center">
                        <svg class="w-12 h-12 mx-auto text-indigo-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <h4 class="font-semibold text-gray-900 mb-1">Nouveau ticket</h4>
                        <p class="text-sm text-gray-600">Créer une demande</p>
                    </Link>

                    <a href="#" class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 hover:shadow-md transition text-center">
                        <svg class="w-12 h-12 mx-auto text-indigo-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h4 class="font-semibold text-gray-900 mb-1">Aide & FAQ</h4>
                        <p class="text-sm text-gray-600">Questions fréquentes</p>
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
