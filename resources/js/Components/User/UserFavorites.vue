<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Mis Favoritos</h2>
      <span class="text-sm text-gray-500">{{ favorites.length }} favorito{{ favorites.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="favorites.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No tienes favoritos guardados</h3>
      <p class="text-gray-600 mb-4">Guarda tus atractivos favoritos para encontrarlos fácilmente más tarde.</p>
      <Link 
        href="/atractivos"
        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center"
      >
        Explorar Atractivos
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
        </svg>
      </Link>
    </div>

    <!-- Favorites Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="favorite in favorites"
        :key="favorite.id"
        class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
      >
        <!-- Image -->
        <div class="relative h-48 bg-gray-200">
          <img
            v-if="favorite.attraction.main_image"
            :src="favorite.attraction.main_image"
            :alt="favorite.attraction.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>

          <!-- Remove Favorite Button -->
          <button
            @click="$emit('remove-favorite', favorite)"
            class="absolute top-2 right-2 p-2 bg-white bg-opacity-90 rounded-full hover:bg-opacity-100 transition-all"
            title="Quitar de favoritos"
          >
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
            </svg>
          </button>

          <!-- Department Badge -->
          <div class="absolute bottom-2 left-2">
            <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
              {{ favorite.attraction.department_name }}
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <div class="mb-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">
              {{ favorite.attraction.name }}
            </h3>
            
            <!-- Rating -->
            <div v-if="favorite.attraction.average_rating" class="flex items-center space-x-1 mb-2">
              <div class="flex text-yellow-400">
                <svg 
                  v-for="star in 5" 
                  :key="star"
                  :class="star <= Math.round(favorite.attraction.average_rating) ? 'text-yellow-400' : 'text-gray-300'"
                  class="w-4 h-4" 
                  fill="currentColor" 
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              </div>
              <span class="text-sm text-gray-600">
                {{ favorite.attraction.average_rating.toFixed(1) }} ({{ favorite.attraction.reviews_count }} reseñas)
              </span>
            </div>

            <!-- Description -->
            <p class="text-gray-600 text-sm line-clamp-3">
              {{ favorite.attraction.description }}
            </p>
          </div>

          <!-- Tourism Type -->
          <div v-if="favorite.attraction.tourism_type" class="mb-3">
            <span 
              :class="getTourismTypeBadgeClass(favorite.attraction.tourism_type)"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            >
              {{ getTourismTypeName(favorite.attraction.tourism_type) }}
            </span>
          </div>

          <!-- Price Range -->
          <div v-if="favorite.attraction.price_range" class="mb-4">
            <div class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              <span>{{ favorite.attraction.price_range }}</span>
            </div>
          </div>

          <!-- Added Date -->
          <div class="mb-4 text-xs text-gray-500">
            Agregado el {{ formatDate(favorite.created_at) }}
          </div>

          <!-- Actions -->
          <div class="flex space-x-2">
            <Link
              :href="`/atractivos/${favorite.attraction.slug}`"
              class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
            >
              Ver Detalles
            </Link>
            
            <button
              v-if="favorite.attraction.has_tours"
              @click="$emit('book-tour', favorite.attraction)"
              class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
            >
              Reservar Tour
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Recommendations -->
    <div v-if="favorites.length > 0" class="mt-12 pt-8 border-t border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Te podría interesar</h3>
      <p class="text-gray-600 text-sm mb-4">
        Basado en tus favoritos, estos destinos podrían gustarte:
      </p>
      
      <div class="flex flex-wrap gap-2">
        <Link
          v-for="recommendation in recommendations"
          :key="recommendation.id"
          :href="`/atractivos/${recommendation.slug}`"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors"
        >
          {{ recommendation.name }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'UserFavorites',
  components: {
    Link
  },
  props: {
    favorites: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    recommendations: {
      type: Array,
      default: () => []
    }
  },
  emits: ['remove-favorite', 'book-tour'],
  methods: {
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    getTourismTypeBadgeClass(type) {
      const classes = {
        'cultural': 'bg-purple-100 text-purple-800',
        'natural': 'bg-green-100 text-green-800',
        'adventure': 'bg-orange-100 text-orange-800',
        'historical': 'bg-blue-100 text-blue-800',
        'religious': 'bg-yellow-100 text-yellow-800',
        'gastronomic': 'bg-red-100 text-red-800',
        'urban': 'bg-gray-100 text-gray-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },

    getTourismTypeName(type) {
      const names = {
        'cultural': 'Cultural',
        'natural': 'Natural',
        'adventure': 'Aventura',
        'historical': 'Histórico',
        'religious': 'Religioso',
        'gastronomic': 'Gastronómico',
        'urban': 'Urbano'
      }
      return names[type] || type
    }
  }
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>