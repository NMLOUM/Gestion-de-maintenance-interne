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

                <!-- Liste de l'historique -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            📊 Historique ({{ histories.total }} actions)
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Heure
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ticket
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Utilisateur
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="history in histories.data" :key="history.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(history.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link
                                            :href="route('tickets.show', history.ticket_id)"
                                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                                        >
                                            #{{ history.ticket_id }}
                                        </Link>
                                        <p v-if="history.ticket" class="text-xs text-gray-500 mt-1">
                                            {{ history.ticket.requester?.name }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="getActionColor(history.action)"
                                        >
                                            {{ getActionIcon(history.action) }} {{ actions[history.action] || history.action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ history.description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                                {{ history.user?.name?.charAt(0) || '?' }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium">{{ history.user?.name || 'Système' }}</p>
                                                <p class="text-xs text-gray-500">{{ history.user?.role }}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
