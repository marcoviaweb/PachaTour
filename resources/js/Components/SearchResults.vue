<template>
  <div class="space-y-6">
    <!-- Search Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">
          {{ searchQuery ? `Resultados para "${searchQuery}"` : 'Explorar atractivos' }}
        </h2>
        <p class="text-gray-600 mt-1">
          {{ total }} {{ total === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
        </p>
      </div>
      
      <!-- View Toggle -->
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-700">Vista:</span>
        <div class="flex rounded-md shadow-sm">
          <button
            @click="viewMode = 'grid'"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-l-md border',
              viewMode === 'grid'
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
          <button
            @click="viewMode = 'list'"
            :class="[
              'px-3 py-2 text-sm font-medium border-t border-b border-r',
              viewMode === 'list'
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </button>
          <button
            @click="viewMode = 'map'"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-r-md border-t border-b border-r',
              viewMode === 'map'
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="text-center">
        <svg class="inline w-8 h-8 mr-2 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600 mt-2">Buscando atractivos...</p>
      </div>
    </div>

    <!-- Results Content -->
    <div v-else-if="attractions.length > 0">
      <!-- Grid View -->
      <div
        v-if="viewMode === 'grid'"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <AttractionCard
          v-for="attraction in attractions"
          :key="attraction.id"
          :attraction="attraction"
          @click="$emit('attraction-select', attraction)"
        />
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list'" class="space-y-4">
        <div
          v-for="attraction in attractions"
          :key="attraction.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
          @click="$emit('attraction-select', attraction)"
        >
          <div class="flex">
            <div class="flex-shrink-0 w-48 h-32">
              <img
                :src="attraction.image_url || '/images/placeholder-attraction.jpg'"
                :alt="attraction.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1 p-4">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ attraction.name }}
                  </h3>
                  <p class="text-sm text-gray-600 mb-2">
                    {{ attraction.department?.name }}
                  </p>
                  <p class="text-gray-700 text-sm line-clamp-2">
                    {{ attraction.description }}
                  </p>
                </div>
                <div class="text-right ml-4">
                  <div v-if="attraction.average_rating" class="flex items-center mb-1">
                    <div class="flex text-yellow-400">
                      <svg
                        v-for="star in 5"
                        :key="star"
                        :class="star <= attraction.average_rating ? 'text-yellow-400' : 'text-gray-300'"
                        class="w-4 h-4 fill-current"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </div>
                    <span class="text-sm text-gray-600 ml-1">
                      ({{ attraction.reviews_count }})
                    </span>
                  </div>
                  <div v-if="attraction.min_price" class="text-lg font-semibold text-green-600">
                    Desde Bs. {{ attraction.min_price }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Map View -->
      <div v-else-if="viewMode === 'map'">
        <InteractiveMap
          :attractions="attractions"
          :height="'500px'"
          @marker-click="$emit('attraction-select', $event)"
        />
      </div>

      <!-- Pagination -->
      <SearchPagination
        :current-page="currentPage"
        :total-pages="totalPages"
        :total="total"
        :per-page="perPage"
        @page-change="$emit('page-change', $event)"
        @per-page-change="$emit('per-page-change', $event)"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ searchQuery 
          ? `No hay atractivos que coincidan con "${searchQuery}"`
          : 'No hay atractivos disponibles con los filtros seleccionados'
        }}
      </p>
      <div class="mt-6">
        <button
          @click="$emit('clear-search')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Limpiar búsqueda
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import AttractionCard from './AttractionCard.vue'
import InteractiveMap from './InteractiveMap.vue'
import SearchPagination from './SearchPagination.vue'

export default {
  name: 'SearchResults',
  components: {
    AttractionCard,
    InteractiveMap,
    SearchPagination
  },
  props: {
    attractions: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    searchQuery: {
      type: String,
      default: ''
    },
    total: {
      type: Number,
      default: 0
    },
    currentPage: {
      type: Number,
      default: 1
    },
    totalPages: {
      type: Number,
      default: 1
    },
    perPage: {
      type: Number,
      default: 12
    }
  },
  emits: [
    'attraction-select',
    'page-change',
    'per-page-change',
    'clear-search'
  ],
  setup() {
    const viewMode = ref('grid')

    return {
      viewMode
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>