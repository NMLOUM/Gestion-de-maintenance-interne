<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="statusClasses"
  >
    {{ statusLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusConfig = {
  pending: {
    label: 'En attente',
    classes: 'bg-yellow-100 text-yellow-800'
  },
  in_progress: {
    label: 'En cours',
    classes: 'bg-blue-100 text-blue-800'
  },
  resolved: {
    label: 'Résolu',
    classes: 'bg-green-100 text-green-800'
  },
  closed: {
    label: 'Fermé',
    classes: 'bg-gray-100 text-gray-800'
  },
  cancelled: {
    label: 'Annulé',
    classes: 'bg-red-100 text-red-800'
  }
}

const statusLabel = computed(() => {
  return statusConfig[props.status]?.label || 'Inconnu'
})

const statusClasses = computed(() => {
  return statusConfig[props.status]?.classes || 'bg-gray-100 text-gray-800'
})
</script>