<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    histories: Object,
    users: Array,
    actions: Object,
    filters: Object
});

const filterForm = ref({
    ticket_id: props.filters?.ticket_id || '',
    user_id: props.filters?.user_id || '',
    action: props.filters?.action || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || ''
});

const applyFilters = () => {
    router.get(route('history.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filterForm.value = {
        ticket_id: '',
        user_id: '',
        action: '',
        date_from: '',
        date_to: ''
    };
    router.get(route('history.index'));
};

const getActionIcon = (action) => {
    const icons = {
        'created': '➕',
        'assigned': '👤',
        'status_changed': '🔄',
        'comment_added': '💬',
        'attachment_added': '📎',
        'updated': '✏️'
    };
    return icons[action] || '📝';
};

const getActionColor = (action) => {
    const colors = {
        'created': 'bg-blue-100 text-blue-800',
        'assigned': 'bg-green-100 text-green-800',
        'status_changed': 'bg-yellow-100 text-yellow-800',
        'comment_added': 'bg-purple-100 text-purple-800',
        'attachment_added': 'bg-pink-100 text-pink-800',
        'updated': 'bg-gray-100 text-gray-800'
    };
    return colors[action] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getRelativeDate = (date) => {
    const today = new Date();
    const historyDate = new Date(date);
    const diffTime = today - historyDate;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Aujourd\'hui';
    if (diffDays === 1) return 'Hier';
    if (diffDays < 7) return `Il y a ${diffDays} jours`;

    return historyDate.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const groupHistoriesByDate = (histories) => {
    const groups = {};

    histories.forEach(history => {
        const dateKey = new Date(history.created_at).toDateString();
        if (!groups[dateKey]) {
            groups[dateKey] = {
                date: history.created_at,
                items: []
            };
        }
        groups[dateKey].items.push(history);
    });

    return Object.values(groups);
};
</script>

<template>
    <Head title="Historique Complet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📋 Historique Complet des Actions
                </h2>
                <Link :href="route('dashboard')"
                      class="text-sm text-indigo-600 hover:text-indigo-800">
                    ← Retour au dashboard
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Filtres -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtres</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ticket #</label>
                            <input
                                v-model="filterForm.ticket_id"
                                type="number"
                                placeholder="Ex: 123"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                            <select
                                v-model="filterForm.user_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            >
                                <option value="">Tous</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type d'action</label>
                            <select
                                v-model="filterForm.action"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            >
                                <option value="">Toutes</option>
                                <option v-for="(label, value) in actions" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                            <input
                                v-model="filterForm.date_from"
                                type="date"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                            <input
                                v-model="filterForm.date_to"
                                type="date"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4">
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium"
                        >
                            🔍 Rechercher
                        </button>
                        <button
                            @click="resetFilters"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium"
                        >
                            🔄 Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Timeline de l'historique -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                        <h3 class="text-lg font-semibold text-gray-900">
                            📊 Historique ({{ histories.total }} actions)
                        </h3>
                    </div>

                    <div class="px-6 py-6">
                        <!-- Timeline verticale -->
                        <div v-for="group in groupHistoriesByDate(histories.data)" :key="group.date" class="mb-8 last:mb-0">
                            <!-- En-tête de date -->
                            <div class="flex items-center mb-4">
                                <div class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full font-semibold text-sm">
                                    {{ getRelativeDate(group.date) }}
                                </div>
                                <div class="flex-1 h-px bg-gray-200 ml-4"></div>
                            </div>

                            <!-- Items de la timeline -->
                            <div class="relative pl-8 space-y-6">
                                <!-- Ligne verticale -->
                                <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-200 via-purple-200 to-pink-200"></div>

                                <div v-for="history in group.items" :key="history.id" class="relative">
                                    <!-- Point sur la timeline -->
                                    <div class="absolute -left-5 top-2 w-3 h-3 rounded-full border-2 border-white shadow-md"
                                         :class="history.action === 'created' ? 'bg-blue-500' :
                                                 history.action === 'assigned' ? 'bg-green-500' :
                                                 history.action === 'status_changed' ? 'bg-yellow-500' :
                                                 history.action === 'comment_added' ? 'bg-purple-500' :
                                                 'bg-gray-400'">
                                    </div>

                                    <!-- Carte de l'événement -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <!-- En-tête -->
                                                <div class="flex items-center gap-3 mb-2">
                                                    <!-- Avatar -->
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold shadow">
                                                        {{ history.user?.name?.charAt(0) || '?' }}
                                                    </div>

                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-semibold text-gray-900">{{ history.user?.name || 'Système' }}</span>
                                                            <span
                                                                class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                                                :class="getActionColor(history.action)"
                                                            >
                                                                {{ getActionIcon(history.action) }} {{ actions[history.action] || history.action }}
                                                            </span>
                                                        </div>
                                                        <p class="text-xs text-gray-500">
                                                            {{ formatTime(history.created_at) }} • {{ history.user?.role || 'Système' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Description -->
                                                <div class="ml-13 mb-2">
                                                    <p class="text-sm text-gray-700">{{ history.description }}</p>
                                                </div>

                                                <!-- Lien vers le ticket -->
                                                <div class="ml-13 flex items-center gap-2">
                                                    <Link
                                                        :href="route('tickets.show', history.ticket_id)"
                                                        class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium hover:underline"
                                                    >
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Ticket #{{ history.ticket_id }}
                                                    </Link>
                                                    <span v-if="history.ticket?.requester" class="text-xs text-gray-400">
                                                        par {{ history.ticket.requester.name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="histories.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Affichage de {{ histories.from }} à {{ histories.to }} sur {{ histories.total }} résultats
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="(link, index) in histories.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    v-html="link.label"
                                    :disabled="!link.url"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Message si vide -->
                    <div v-if="histories.data.length === 0" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun historique trouvé</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune action ne correspond à vos critères de recherche.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
