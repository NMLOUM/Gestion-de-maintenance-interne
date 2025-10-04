<template>
  <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              :class="column.headerClass"
            >
              <div class="flex items-center">
                {{ column.label }}
                <button
                  v-if="column.sortable"
                  @click="toggleSort(column.key)"
                  class="ml-2 flex-none rounded text-gray-400 hover:text-gray-600"
                >
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(item, index) in data"
            :key="getItemKey(item, index)"
            :class="getRowClass(item, index)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm"
              :class="column.cellClass"
            >
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getNestedValue(item, column.key)"
                :index="index"
              >
                {{ formatValue(getNestedValue(item, column.key), column) }}
              </slot>
            </td>
          </tr>
          <tr v-if="data.length === 0">
            <td :colspan="columns.length" class="px-6 py-12 text-center text-gray-500">
              <slot name="empty">
                Aucune donnée disponible
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
      <slot name="pagination" :pagination="pagination">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              :disabled="!pagination.prev_page_url"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              @click="$emit('page-change', pagination.current_page - 1)"
            >
              Précédent
            </button>
            <button
              :disabled="!pagination.next_page_url"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              @click="$emit('page-change', pagination.current_page + 1)"
            >
              Suivant
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de
                <span class="font-medium">{{ pagination.from || 0 }}</span>
                à
                <span class="font-medium">{{ pagination.to || 0 }}</span>
                sur
                <span class="font-medium">{{ pagination.total }}</span>
                résultats
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  :disabled="!pagination.prev_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  @click="$emit('page-change', pagination.current_page - 1)"
                >
                  ‹
                </button>
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  :class="[
                    page === pagination.current_page
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                  @click="$emit('page-change', page)"
                >
                  {{ page }}
                </button>
                <button
                  :disabled="!pagination.next_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  @click="$emit('page-change', pagination.current_page + 1)"
                >
                  ›
                </button>
              </nav>
            </div>
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  pagination: {
    type: Object,
    default: null
  },
  sortBy: {
    type: String,
    default: null
  },
  sortDirection: {
    type: String,
    default: 'asc'
  },
  rowKey: {
    type: [String, Function],
    default: 'id'
  },
  rowClass: {
    type: [String, Function],
    default: null
  }
})

const emit = defineEmits(['sort', 'page-change'])

const visiblePages = computed(() => {
  if (!props.pagination) return []

  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const delta = 2

  const range = []
  const rangeWithDots = []

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }

  if (current - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }

  rangeWithDots.push(...range)

  if (current + delta < last - 1) {
    rangeWithDots.push('...', last)
  } else {
    if (last > 1) rangeWithDots.push(last)
  }

  return rangeWithDots.filter(page => page !== '...' || rangeWithDots.length > 3)
})

const getItemKey = (item, index) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(item, index)
  }
  return item[props.rowKey] || index
}

const getRowClass = (item, index) => {
  if (typeof props.rowClass === 'function') {
    return props.rowClass(item, index)
  }
  return props.rowClass || 'hover:bg-gray-50'
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((o, p) => o && o[p], obj)
}

const formatValue = (value, column) => {
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value)
  }

  if (value === null || value === undefined) {
    return '-'
  }

  return value
}

const toggleSort = (column) => {
  if (props.sortBy === column) {
    emit('sort', column, props.sortDirection === 'asc' ? 'desc' : 'asc')
  } else {
    emit('sort', column, 'asc')
  }
}
</script>