<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  height: {
    type: Number,
    default: 300
  }
})

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: {
        size: 14
      },
      bodyFont: {
        size: 13
      },
      borderColor: 'rgba(255, 255, 255, 0.2)',
      borderWidth: 1
    }
  }
}

const chartOptions = computed(() => ({
  ...defaultOptions,
  ...props.options
}))
</script>

<template>
  <div :style="{ height: `${height}px` }">
    <Doughnut :data="data" :options="chartOptions" />
  </div>
</template>
