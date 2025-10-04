<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    ticketsByService: Array,
    ticketsByCategory: Array,
    globalPerformance: Object,
    ticketTrend: Array,
    stats: Object
});
</script>

<template>
    <Head title="Dashboard Direction" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Direction</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- 📊 Indicateurs clés KPI -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Tickets -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total Tickets</p>
                                <p class="text-3xl font-bold">{{ stats?.total_tickets || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V9zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0v-4zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Résolus ce mois -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Résolus ce mois</p>
                                <p class="text-3xl font-bold">{{ globalPerformance?.resolved_this_month || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Utilisateurs actifs -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Utilisateurs actifs</p>
                                <p class="text-3xl font-bold">{{ stats?.total_users || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Temps moyen -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">Temps moyen</p>
                                <p class="text-3xl font-bold">{{ stats?.avg_resolution_time || 0 }}h</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance globale -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Satisfaction client</h3>
                            <div class="text-3xl font-bold text-green-600">
                                {{ globalPerformance?.satisfaction_rate || 0 }}%
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Basé sur les évaluations</p>
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" :style="{ width: (globalPerformance?.satisfaction_rate || 0) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets en retard</h3>
                            <div class="text-3xl font-bold text-red-600">
                                {{ stats?.overdue_tickets || 0 }}
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Tickets dépassant les SLA</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets critiques</h3>
                            <div class="text-3xl font-bold text-orange-600">
                                {{ stats?.critical_tickets || 0 }}
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Haute priorité ouverts</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Répartition par service -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets par service</h3>

                            <div class="space-y-4" v-if="ticketsByService && ticketsByService.length > 0">
                                <div v-for="service in ticketsByService" :key="service.id"
                                     class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ service.name }}</h4>
                                        <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full"
                                                 :style="{ width: Math.min((service.tickets_count / Math.max(...ticketsByService.map(s => s.tickets_count))) * 100, 100) + '%' }">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4 text-sm font-medium text-gray-900">
                                        {{ service.tickets_count }}
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8">
                                <p class="text-sm text-gray-500">Aucune donnée disponible</p>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par catégorie -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets par catégorie</h3>

                            <div class="grid grid-cols-2 gap-4" v-if="ticketsByCategory && ticketsByCategory.length > 0">
                                <div v-for="category in ticketsByCategory" :key="category.id"
                                     class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-indigo-600">{{ category.tickets_count }}</div>
                                    <div class="text-sm text-gray-600 mt-1">{{ category.name }}</div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8">
                                <p class="text-sm text-gray-500">Aucune donnée disponible</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tendance des tickets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" v-if="ticketTrend && ticketTrend.length > 0">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des tickets (30 derniers jours)</h3>

                        <div class="overflow-x-auto">
                            <div class="flex space-x-2 pb-4">
                                <div v-for="day in ticketTrend" :key="day.date"
                                     class="flex-shrink-0 text-center">
                                    <div class="text-xs text-gray-500 mb-2">
                                        {{ new Date(day.date).getDate() }}
                                    </div>
                                    <div class="flex flex-col space-y-1">
                                        <div class="bg-blue-500 rounded"
                                             :style="{ height: Math.max(day.created * 3, 2) + 'px', width: '20px' }"
                                             :title="`${day.created} créés`">
                                        </div>
                                        <div class="bg-green-500 rounded"
                                             :style="{ height: Math.max(day.resolved * 3, 2) + 'px', width: '20px' }"
                                             :title="`${day.resolved} résolus`">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    <span>Créés</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    <span>Résolus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides Direction -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-white mb-6">⚡ Actions rapides</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link :href="route('tickets.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg p-6 text-center transition-all hover:scale-105 cursor-pointer">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">📋 Tous les tickets</span>
                        </Link>

                        <Link :href="route('users.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg p-6 text-center transition-all hover:scale-105 cursor-pointer">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span class="text-sm font-semibold block">👥 Utilisateurs</span>
                        </Link>

                        <Link :href="route('reports.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg p-6 text-center transition-all hover:scale-105 cursor-pointer">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span class="text-sm font-semibold block">📊 Rapports</span>
                        </Link>

                        <Link :href="route('history.index')"
                              class="bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg p-6 text-center transition-all hover:scale-105 cursor-pointer">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold block">📜 Historique</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>