<template>
  <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <!-- Mobile pagination -->
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        @click="goToPrevious"
        :disabled="!hasPrevious"
        :class="[
          'relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700',
          hasPrevious ? 'hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'
        ]"
      >
        Anterior
      </button>
      <button
        @click="goToNext"
        :disabled="!hasNext"
        :class="[
          'relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700',
          hasNext ? 'hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'
        ]"
      >
        Siguiente
      </button>
    </div>

    <!-- Desktop pagination -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">{{ startItem }}</span>
          a
          <span class="font-medium">{{ endItem }}</span>
          de
          <span class="font-medium">{{ total }}</span>
          resultados
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <!-- Previous button -->
          <button
            @click="goToPrevious"
            :disabled="!hasPrevious"
            :class="[
              'relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300',
              hasPrevious ? 'hover:bg-gray-50 focus:z-20 focus:outline-offset-0' : 'opacity-50 cursor-not-allowed'
            ]"
          >
            <span class="sr-only">Anterior</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Page numbers -->
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0',
                page === currentPage
                  ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                  : 'text-gray-900 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <span
              v-else
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"
            >
              ...
            </span>
          </template>

          <!-- Next button -->
          <button
            @click="goToNext"
            :disabled="!hasNext"
            :class="[
              'relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300',
              hasNext ? 'hover:bg-gray-50 focus:z-20 focus:outline-offset-0' : 'opacity-50 cursor-not-allowed'
            ]"
          >
            <span class="sr-only">Siguiente</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </button>
        </nav>
      </div>
    </div>

    <!-- Results per page selector -->
    <div class="hidden sm:block ml-4">
      <select
        v-model="selectedPerPage"
        @change="changePerPage"
        class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
      >
        <option v-for="option in perPageOptions" :key="option" :value="option">
          {{ option }} por página
        </option>
      </select>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch } from 'vue'

export default {
  name: 'SearchPagination',
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    totalPages: {
      type: Number,
      required: true
    },
    total: {
      type: Number,
      required: true
    },
    perPage: {
      type: Number,
      default: 12
    },
    perPageOptions: {
      type: Array,
      default: () => [12, 24, 48]
    }
  },
  emits: ['page-change', 'per-page-change'],
  setup(props, { emit }) {
    const selectedPerPage = ref(props.perPage)

    const hasPrevious = computed(() => props.currentPage > 1)
    const hasNext = computed(() => props.currentPage < props.totalPages)

    const startItem = computed(() => {
      if (props.total === 0) return 0
      return (props.currentPage - 1) * props.perPage + 1
    })

    const endItem = computed(() => {
      const end = props.currentPage * props.perPage
      return end > props.total ? props.total : end
    })

    const visiblePages = computed(() => {
      const pages = []
      const totalPages = props.totalPages
      const current = props.currentPage

      if (totalPages <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= totalPages; i++) {
          pages.push(i)
        }
      } else {
        // Always show first page
        pages.push(1)

        if (current <= 4) {
          // Show pages 2-5 and ellipsis
          for (let i = 2; i <= 5; i++) {
            pages.push(i)
          }
          pages.push('...')
        } else if (current >= totalPages - 3) {
          // Show ellipsis and last 4 pages
          pages.push('...')
          for (let i = totalPages - 3; i <= totalPages - 1; i++) {
            pages.push(i)
          }
        } else {
          // Show ellipsis, current page area, ellipsis
          pages.push('...')
          for (let i = current - 1; i <= current + 1; i++) {
            pages.push(i)
          }
          pages.push('...')
        }

        // Always show last page (if not already shown)
        if (totalPages > 1 && pages[pages.length - 1] !== totalPages) {
          pages.push(totalPages)
        }
      }

      return pages
    })

    const goToPage = (page) => {
      if (page !== props.currentPage && page >= 1 && page <= props.totalPages) {
        emit('page-change', page)
      }
    }

    const goToPrevious = () => {
      if (hasPrevious.value) {
        goToPage(props.currentPage - 1)
      }
    }

    const goToNext = () => {
      if (hasNext.value) {
        goToPage(props.currentPage + 1)
      }
    }

    const changePerPage = () => {
      emit('per-page-change', selectedPerPage.value)
    }

    // Watch for external changes to perPage prop
    watch(() => props.perPage, (newValue) => {
      selectedPerPage.value = newValue
    })

    return {
      selectedPerPage,
      hasPrevious,
      hasNext,
      startItem,
      endItem,
      visiblePages,
      goToPage,
      goToPrevious,
      goToNext,
      changePerPage
    }
  }
}
</script>