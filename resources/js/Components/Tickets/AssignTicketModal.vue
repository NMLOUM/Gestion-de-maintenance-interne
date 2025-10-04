<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    ticketId: Number,
    ticketTitle: String,
    currentAssignee: Object,
    technicians: Array
});

const emit = defineEmits(['close']);

const selectedTechnician = ref(props.currentAssignee?.id || null);
const isSubmitting = ref(false);

watch(() => props.show, (newValue) => {
    if (newValue) {
        selectedTechnician.value = props.currentAssignee?.id || null;
    }
});

const selectTechnician = (tech) => {
    // Bloquer la sélection si le technicien a 3 tickets actifs ou plus
    if (tech.active_tickets_count >= 3) {
        alert('⚠️ Ce technicien est surchargé !\n\n' +
              `Il a actuellement ${tech.active_tickets_count} tickets actifs.\n` +
              'Veuillez choisir un technicien moins chargé ou attendre qu\'il résolve ses tickets en cours.');
        return;
    }
    selectedTechnician.value = tech.id;
};

const assignTicket = () => {
    if (!selectedTechnician.value) {
        alert('Veuillez sélectionner un technicien');
        return;
    }

    // Vérifier à nouveau la charge avant d'assigner
    const selectedTech = props.technicians.find(t => t.id === selectedTechnician.value);
    if (selectedTech && selectedTech.active_tickets_count >= 3) {
        alert('⚠️ Impossible d\'assigner ce ticket !\n\n' +
              'Le technicien sélectionné a trop de tickets actifs.\n' +
              'Veuillez choisir un autre technicien.');
        return;
    }

    isSubmitting.value = true;

    router.post(route('tickets.assign', props.ticketId), {
        assigned_to: selectedTechnician.value
    }, {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => {
            alert('✅ Ticket assigné avec succès !');
            emit('close');
            isSubmitting.value = false;
        },
        onError: (errors) => {
            console.error('Erreur assignation:', errors);
            alert('❌ Erreur lors de l\'assignation. Veuillez réessayer.');
            isSubmitting.value = false;
        }
    });
};

const closeModal = () => {
    if (!isSubmitting.value) {
        emit('close');
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ currentAssignee ? 'Réaffecter le ticket' : 'Assigner le ticket' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    <strong>Ticket #{{ ticketId }}:</strong> {{ ticketTitle }}
                                </p>

                                <div v-if="currentAssignee" class="mb-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Actuellement assigné à:</strong> {{ currentAssignee.name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Sélectionner un technicien
                                    </label>

                                    <div class="space-y-2 max-h-64 overflow-y-auto">
                                        <div
                                            v-for="tech in technicians"
                                            :key="tech.id"
                                            @click="selectTechnician(tech)"
                                            :class="[
                                                'border rounded-lg p-3 cursor-pointer transition',
                                                selectedTechnician === tech.id
                                                    ? 'border-indigo-500 bg-indigo-50'
                                                    : 'border-gray-300 hover:border-indigo-300 hover:bg-gray-50',
                                                tech.active_tickets_count >= 3
                                                    ? 'opacity-60 cursor-not-allowed'
                                                    : ''
                                            ]"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center flex-1">
                                                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                                        {{ tech.name.charAt(0) }}
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900">{{ tech.name }}</p>
                                                        <p class="text-xs text-gray-500">{{ tech.email }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div
                                                        :class="[
                                                            'text-lg font-bold',
                                                            tech.active_tickets_count === 0 ? 'text-green-600' :
                                                            tech.active_tickets_count < 3 ? 'text-orange-600' :
                                                            'text-red-600'
                                                        ]"
                                                    >
                                                        {{ tech.active_tickets_count }}
                                                    </div>
                                                    <p class="text-xs text-gray-500">tickets actifs</p>
                                                </div>
                                            </div>

                                            <!-- Barre de progression -->
                                            <div class="mt-2">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        :class="[
                                                            'h-2 rounded-full transition-all',
                                                            tech.active_tickets_count === 0 ? 'bg-green-500' :
                                                            tech.active_tickets_count < 3 ? 'bg-orange-500' :
                                                            'bg-red-500'
                                                        ]"
                                                        :style="`width: ${Math.min((tech.active_tickets_count / 5) * 100, 100)}%`"
                                                    ></div>
                                                </div>
                                            </div>

                                            <!-- Avertissement si surchargé -->
                                            <div v-if="tech.active_tickets_count >= 3" class="mt-2 flex items-center text-xs text-red-600">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium">Technicien surchargé - Doit d'abord résoudre des tickets</span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs text-gray-500">
                                        💡 Sélectionnez un technicien avec moins de 3 tickets actifs
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        type="button"
                        @click="assignTicket"
                        :disabled="isSubmitting || !selectedTechnician"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="isSubmitting">Assignation en cours...</span>
                        <span v-else>{{ currentAssignee ? 'Réaffecter' : 'Assigner' }}</span>
                    </button>
                    <button
                        type="button"
                        @click="closeModal"
                        :disabled="isSubmitting"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
