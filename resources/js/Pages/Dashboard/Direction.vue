<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';

const props = defineProps({
    ticketsByService: Array,
    ticketsByCategory: Array,
    globalPerformance: Object,
    ticketTrend: Array,
    stats: Object,
    slaPerformance: Object
});

const activeTab = ref('overview');
let refreshInterval = null;

// Données pour graphique barres - Tickets par service
const serviceChartData = computed(() => ({
    labels: props.ticketsByService?.map(s => s.name) || [],
    datasets: [{
        label: 'Nombre de tickets',
        data: props.ticketsByService?.map(s => s.tickets_count) || [],
        backgroundColor: [
            'rgba(99, 102, 241, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
        ],
        borderColor: [
            'rgb(99, 102, 241)',
            'rgb(139, 92, 246)',
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
        ],
        borderWidth: 2
    }]
}));

// Données pour graphique camembert - Tickets par catégorie
const categoryChartData = computed(() => ({
    labels: props.ticketsByCategory?.map(c => c.name) || [],
    datasets: [{
        data: props.ticketsByCategory?.map(c => c.tickets_count) || [],
        backgroundColor: [
            'rgba(99, 102, 241, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(251, 146, 60, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(59, 130, 246, 0.8)',
        ],
        borderColor: [
            'rgb(99, 102, 241)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)',
            'rgb(251, 146, 60)',
            'rgb(34, 197, 94)',
            'rgb(59, 130, 246)',
        ],
        borderWidth: 2
    }]
}));

// Données pour graphique ligne - Tendances
const trendChartData = computed(() => ({
    labels: props.ticketTrend?.map(t => t.date) || [],
    datasets: [
        {
            label: 'Créés',
            data: props.ticketTrend?.map(t => t.created) || [],
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Résolus',
            data: props.ticketTrend?.map(t => t.resolved) || [],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
        }
    ]
}));

// Rafraîchir les données toutes les 60 secondes
onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['stats', 'ticketsByService', 'ticketsByCategory', 'globalPerformance', 'ticketTrend', 'slaPerformance'] });
    }, 60000); // 60 secondes
});

// Nettoyer l'intervalle quand le composant est détruit
onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
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
                    <!-- Tickets ce mois -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Tickets ce mois</p>
                                <p class="text-3xl font-bold">{{ globalPerformance?.tickets_this_month || 0 }}</p>
                                <p class="text-xs text-indigo-200 mt-1">Total : {{ stats?.total_tickets || 0 }}</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V9zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0v-4zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Satisfaction client -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Satisfaction client</p>
                                <p class="text-3xl font-bold">{{ globalPerformance?.satisfaction_rate || 0 }}%</p>
                                <p class="text-xs text-green-200 mt-1">Basé sur évaluations</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Temps moyen résolution -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">Temps moyen</p>
                                <p class="text-3xl font-bold">{{ stats?.avg_resolution_time || 0 }}h</p>
                                <p class="text-xs text-orange-200 mt-1">Temps de résolution</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Alertes -->
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Points d'attention</p>
                                <p class="text-3xl font-bold">{{ (stats?.critical_tickets || 0) + (stats?.overdue_tickets || 0) }}</p>
                                <p class="text-xs text-red-200 mt-1">Critiques + Retards</p>
                            </div>
                            <div class="bg-white bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation par onglets -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <nav class="flex -mb-px border-b border-gray-200">
                        <button @click="activeTab = 'overview'"
                                :class="activeTab === 'overview'
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm transition">
                            📊 Vue d'ensemble
                        </button>
                        <button @click="activeTab = 'performance'"
                                :class="activeTab === 'performance'
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm transition">
                            📈 Performance
                        </button>
                        <button @click="activeTab = 'trends'"
                                :class="activeTab === 'trends'
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm transition">
                            📉 Tendances
                        </button>
                    </nav>

                    <!-- Tab: Vue d'ensemble -->
                    <div v-show="activeTab === 'overview'" class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Résolus ce mois
                                </h3>
                                <div class="text-4xl font-bold text-blue-700">
                                    {{ globalPerformance?.resolved_this_month || 0 }}
                                </div>
                                <p class="text-sm text-gray-600 mt-2">Sur {{ globalPerformance?.tickets_this_month || 0 }} tickets créés</p>
                            </div>

                            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Tickets en retard
                                </h3>
                                <div class="text-4xl font-bold text-red-700">
                                    {{ stats?.overdue_tickets || 0 }}
                                </div>
                                <p class="text-sm text-gray-600 mt-2">Dépassant les SLA</p>
                            </div>

                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Tickets critiques
                                </h3>
                                <div class="text-4xl font-bold text-orange-700">
                                    {{ stats?.critical_tickets || 0 }}
                                </div>
                                <p class="text-sm text-gray-600 mt-2">Haute priorité ouverts</p>
                            </div>
                        </div>

                        <!-- Répartition par service et catégorie - GRAPHIQUES -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Répartition par service - Graphique à barres -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    📊 Tickets par service
                                </h3>
                                <BarChart v-if="ticketsByService && ticketsByService.length > 0"
                                          :data="serviceChartData"
                                          :height="300" />
                                <div v-else class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500 mt-2">Aucune donnée disponible</p>
                                </div>
                            </div>

                            <!-- Répartition par catégorie - Graphique camembert -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                                    </svg>
                                    🎯 Tickets par catégorie
                                </h3>
                                <DoughnutChart v-if="ticketsByCategory && ticketsByCategory.length > 0"
                                               :data="categoryChartData"
                                               :height="300" />
                                <div v-else class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                    <p class="text-sm text-gray-500 mt-2">Aucune donnée disponible</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Performance -->
                    <div v-show="activeTab === 'performance'" class="p-6">
                        <!-- Performance SLA Globale -->
                        <div class="mb-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
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

                        <!-- Performance SLA par priorité -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Performance SLA par priorité</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Critique -->
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-red-900">🔴 Critique</h4>
                                        <span class="text-xs text-red-700">SLA: 24h</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-bold">{{ slaPerformance?.by_priority?.critical?.total || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-green-700">À temps:</span>
                                            <span class="font-bold text-green-700">{{ slaPerformance?.by_priority?.critical?.on_time || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-red-700">En retard:</span>
                                            <span class="font-bold text-red-700">{{ slaPerformance?.by_priority?.critical?.overdue || 0 }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span>Respect SLA</span>
                                                <span class="font-bold">{{ slaPerformance?.by_priority?.critical?.on_time_percent || 0 }}%</span>
                                            </div>
                                            <div class="w-full bg-red-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full transition-all"
                                                     :style="{ width: (slaPerformance?.by_priority?.critical?.on_time_percent || 0) + '%' }">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Élevée -->
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-orange-900">🟠 Élevée</h4>
                                        <span class="text-xs text-orange-700">SLA: 72h</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-bold">{{ slaPerformance?.by_priority?.high?.total || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-green-700">À temps:</span>
                                            <span class="font-bold text-green-700">{{ slaPerformance?.by_priority?.high?.on_time || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-red-700">En retard:</span>
                                            <span class="font-bold text-red-700">{{ slaPerformance?.by_priority?.high?.overdue || 0 }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span>Respect SLA</span>
                                                <span class="font-bold">{{ slaPerformance?.by_priority?.high?.on_time_percent || 0 }}%</span>
                                            </div>
                                            <div class="w-full bg-orange-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full transition-all"
                                                     :style="{ width: (slaPerformance?.by_priority?.high?.on_time_percent || 0) + '%' }">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Normale -->
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-yellow-900">🟡 Normale</h4>
                                        <span class="text-xs text-yellow-700">SLA: 168h</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-bold">{{ slaPerformance?.by_priority?.normal?.total || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-green-700">À temps:</span>
                                            <span class="font-bold text-green-700">{{ slaPerformance?.by_priority?.normal?.on_time || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-red-700">En retard:</span>
                                            <span class="font-bold text-red-700">{{ slaPerformance?.by_priority?.normal?.overdue || 0 }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span>Respect SLA</span>
                                                <span class="font-bold">{{ slaPerformance?.by_priority?.normal?.on_time_percent || 0 }}%</span>
                                            </div>
                                            <div class="w-full bg-yellow-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full transition-all"
                                                     :style="{ width: (slaPerformance?.by_priority?.normal?.on_time_percent || 0) + '%' }">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Faible -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-green-900">🟢 Faible</h4>
                                        <span class="text-xs text-green-700">SLA: 336h</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-bold">{{ slaPerformance?.by_priority?.low?.total || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-green-700">À temps:</span>
                                            <span class="font-bold text-green-700">{{ slaPerformance?.by_priority?.low?.on_time || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-red-700">En retard:</span>
                                            <span class="font-bold text-red-700">{{ slaPerformance?.by_priority?.low?.overdue || 0 }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span>Respect SLA</span>
                                                <span class="font-bold">{{ slaPerformance?.by_priority?.low?.on_time_percent || 0 }}%</span>
                                            </div>
                                            <div class="w-full bg-green-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full transition-all"
                                                     :style="{ width: (slaPerformance?.by_priority?.low?.on_time_percent || 0) + '%' }">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- État général -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">📊 État général</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total tickets</span>
                                        <span class="text-lg font-bold text-gray-900">{{ stats?.total_tickets || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">En cours</span>
                                        <span class="text-lg font-bold text-blue-600">{{ stats?.in_progress_tickets || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">En attente</span>
                                        <span class="text-lg font-bold text-yellow-600">{{ stats?.pending_tickets || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Résolus</span>
                                        <span class="text-lg font-bold text-green-600">{{ stats?.resolved_tickets || 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Métriques de performance -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">⚡ Métriques</h3>
                                <div class="space-y-4">
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm text-gray-600">Satisfaction</span>
                                            <span class="text-sm font-bold text-green-600">{{ globalPerformance?.satisfaction_rate || 0 }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full transition-all" :style="{ width: (globalPerformance?.satisfaction_rate || 0) + '%' }"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Temps moyen résolution</span>
                                        <div class="text-2xl font-bold text-blue-600 mt-1">{{ stats?.avg_resolution_time || 0 }}h</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Utilisateurs actifs</span>
                                        <div class="text-2xl font-bold text-purple-600 mt-1">{{ stats?.total_users || 0 }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Taux de résolution -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">🎯 Taux de résolution</h3>
                                <div class="text-center">
                                    <div class="text-5xl font-bold text-purple-600">
                                        {{ stats?.total_tickets > 0 ? Math.round((stats?.resolved_tickets / stats?.total_tickets) * 100) : 0 }}%
                                    </div>
                                    <p class="text-sm text-gray-600 mt-2">
                                        {{ stats?.resolved_tickets || 0 }} résolus / {{ stats?.total_tickets || 0 }} total
                                    </p>
                                    <div class="mt-4 w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-purple-600 h-3 rounded-full transition-all"
                                             :style="{ width: (stats?.total_tickets > 0 ? (stats?.resolved_tickets / stats?.total_tickets) * 100 : 0) + '%' }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Tendances -->
                    <div v-show="activeTab === 'trends'" class="p-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-6" v-if="ticketTrend && ticketTrend.length > 0">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Évolution des tickets (30 derniers jours)</h3>
                            <div class="overflow-x-auto">
                                <div class="flex space-x-2 pb-4 min-w-max">
                                    <div v-for="day in ticketTrend" :key="day.date"
                                         class="flex-shrink-0 text-center">
                                        <div class="text-xs text-gray-500 mb-2">
                                            {{ new Date(day.date).getDate() }}/{{ new Date(day.date).getMonth() + 1 }}
                                        </div>
                                        <div class="flex flex-col-reverse items-center space-y-reverse space-y-1" style="min-height: 120px;">
                                            <div class="bg-blue-500 rounded-t"
                                                 :style="{ height: Math.max(day.created * 3, 2) + 'px', width: '20px' }"
                                                 :title="`${day.created} créés`">
                                            </div>
                                            <div class="bg-green-500 rounded-t"
                                                 :style="{ height: Math.max(day.resolved * 3, 2) + 'px', width: '20px' }"
                                                 :title="`${day.resolved} résolus`">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-4 text-sm mt-4 justify-center">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                                        <span>Tickets créés</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                                        <span>Tickets résolus</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500">Aucune donnée de tendance disponible</p>
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