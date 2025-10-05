<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

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
      position: 'top',
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
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0
      }
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
    <Bar :data="data" :options="chartOptions" />
  </div>
</template>
