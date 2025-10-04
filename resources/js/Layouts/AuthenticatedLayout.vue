<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { Link, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

// Fonction pour aller à l'onglet Performance de l'équipe
const goToTeamPerformance = () => {
    router.visit(route('dashboard'), {
        onSuccess: () => {
            // Attendre que la page soit chargée puis changer l'onglet
            setTimeout(() => {
                if (window.dashboardActiveTab) {
                    window.dashboardActiveTab.value = 'team';
                }
            }, 100);
        }
    });
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>

                                <NavLink :href="route('tickets.index')" :active="route().current('tickets.*')">
                                    Tickets
                                </NavLink>

                                <!-- Navigation pour Technicien -->
                                <template v-if="$page.props.auth.user.role === 'technicien'">
                                    <!-- Pas de liens supplémentaires pour technicien -->
                                </template>

                                <!-- Navigation pour Employé -->
                                <template v-if="$page.props.auth.user.role === 'employe'">
                                    <!-- Pas de liens supplémentaires pour employé -->
                                </template>

                                <!-- Navigation pour Responsable IT -->
                                <template v-if="$page.props.auth.user.role === 'responsable_it'">
                                    <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                        Rapports
                                    </NavLink>
                                </template>

                                <!-- Navigation pour Direction -->
                                <template v-if="$page.props.auth.user.role === 'direction'">
                                    <NavLink :href="route('users.index')" :active="route().current('users.*')">
                                        Utilisateurs
                                    </NavLink>
                                    <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                        Rapports
                                    </NavLink>
                                </template>
                            </div>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Notification Bell -->
                            <div class="ms-3">
                                <NotificationBell />
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="64">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                <!-- Avatar avec initiales -->
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm mr-2">
                                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                                </div>

                                                <div class="flex flex-col items-start">
                                                    <span class="text-sm font-semibold text-gray-700">
                                                        Bonjour, {{ $page.props.auth.user.name.split(' ')[0] }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $page.props.auth.user.role_display || 'Non défini' }}
                                                    </span>
                                                </div>

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Mon Profil
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Se déconnecter
                                            </div>
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('tickets.index')" :active="route().current('tickets.*')">
                            Tickets
                        </ResponsiveNavLink>

                        <!-- Navigation mobile pour Responsable IT -->
                        <template v-if="$page.props.auth.user.role === 'responsable_it'">
                            <a href="#" @click.prevent="goToTeamPerformance"
                               class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                                👥 Équipe de Performance
                            </a>
                            <ResponsiveNavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                Rapports
                            </ResponsiveNavLink>
                        </template>

                        <!-- Navigation mobile pour Direction -->
                        <template v-if="$page.props.auth.user.role === 'direction'">
                            <ResponsiveNavLink :href="route('users.index')" :active="route().current('users.*')">
                                Utilisateurs
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                Rapports
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4 flex items-center">
                            <!-- Avatar avec initiales -->
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg mr-3">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    Bonjour, {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                                <div class="font-medium text-xs text-gray-400">
                                    {{ $page.props.auth.user.role_display || 'Non défini' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon Profil
                                </div>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Se déconnecter
                                </div>
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
