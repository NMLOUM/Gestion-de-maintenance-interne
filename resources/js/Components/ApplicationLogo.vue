<template>
    <div class="flex items-center space-x-2">
        <!-- Icône d'engrenage et outils pour représenter la maintenance -->
        <div class="relative">
            <svg class="w-9 h-9 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
            </svg>
            <svg class="w-4 h-4 text-purple-600 absolute -bottom-1 -right-1" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>
        <!-- Texte du logo -->
        <div class="flex flex-col">
            <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent leading-tight">
                Système de Maintenance
            </span>
            <span class="text-xs font-semibold text-gray-500 -mt-1">
                Interne
            </span>
        </div>
    </div>
</template>
