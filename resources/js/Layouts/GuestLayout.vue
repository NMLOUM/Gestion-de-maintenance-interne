<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
        <!-- Logo centré avec effet -->
        <div class="mb-8">
            <Link href="/">
                <div class="transform hover:scale-105 transition-transform duration-200">
                    <ApplicationLogo class="w-auto h-auto" />
                </div>
            </Link>
        </div>

        <!-- Card de connexion -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-xl border border-gray-100">
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>© 2025 Système de Maintenance Interne</p>
        </div>
    </div>
</template>
