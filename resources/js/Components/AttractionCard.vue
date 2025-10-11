<template>
  <div class="attraction-card group cursor-pointer transform transition-all duration-300 hover:scale-105">
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
      <!-- Imagen principal -->
      <div class="relative h-48 overflow-hidden">
        <img
          :src="getMainImage()"
          :alt="attraction.name"
          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
          @error="handleImageError"
        >
        
        <!-- Overlay con gradiente -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        
        <!-- Badge de tipo de turismo -->
        <div class="absolute top-3 left-3">
          <span class="bg-green-600 text-white text-xs font-medium px-2 py-1 rounded-full">
            {{ getTourismTypeLabel() }}
          </span>
        </div>

        <!-- Rating en la esquina superior derecha -->
        <div v-if="attraction.average_rating" class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-full px-2 py-1 flex items-center space-x-1">
          <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="text-sm font-medium text-gray-800">{{ formatRating(attraction.average_rating) }}</span>
        </div>

        <!-- Título y ubicación en la parte inferior -->
        <div class="absolute bottom-3 left-3 right-3 text-white">
          <h3 class="text-lg font-bold mb-1 line-clamp-2">{{ attraction.name }}</h3>
          <div class="flex items-center text-sm opacity-90">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>{{ getDepartmentName() }}</span>
          </div>
        </div>
      </div>

      <!-- Contenido de la tarjeta -->
      <div class="p-4">
        <!-- Descripción -->
        <p class="text-gray-600 text-sm line-clamp-3 mb-4">
          {{ attraction.description || 'Descubre este increíble atractivo turístico.' }}
        </p>

        <!-- Información práctica -->
        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
          <div class="flex items-center space-x-4">
            <!-- Altitud -->
            <span v-if="attraction.altitude" class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
              </svg>
              {{ formatAltitude(attraction.altitude) }}
            </span>
            
            <!-- Número de tours disponibles -->
            <span v-if="getToursCount() > 0" class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              {{ getToursCount() }} tours
            </span>
          </div>

          <!-- Precio desde -->
          <div v-if="getMinPrice()" class="text-green-600 font-semibold">
            Desde {{ formatPrice(getMinPrice()) }}
          </div>
        </div>

        <!-- Tags de características -->
        <div v-if="getTags().length > 0" class="flex flex-wrap gap-2 mb-4">
          <span 
            v-for="tag in getTags().slice(0, 3)" 
            :key="tag"
            class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full"
          >
            {{ tag }}
          </span>
          <span 
            v-if="getTags().length > 3"
            class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full"
          >
            +{{ getTags().length - 3 }}
          </span>
        </div>

        <!-- Botones de acción -->
        <div class="flex space-x-2">
          <button 
            @click="viewDetails"
            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors duration-200 font-medium text-sm"
          >
            Ver Detalles
          </button>
          <button 
            v-if="getToursCount() > 0"
            @click="bookTour"
            class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium text-sm"
          >
            Reservar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3'

export default {
  name: 'AttractionCard',
  props: {
    attraction: {
      type: Object,
      required: true
    },
    showDepartment: {
      type: Boolean,
      default: true
    }
  },
  emits: ['view-details', 'book-tour'],
  setup(props, { emit }) {
    const getMainImage = () => {
      // Si tiene media, usar la primera imagen
      if (props.attraction.media && props.attraction.media.length > 0) {
        const firstImage = props.attraction.media.find(m => m.type === 'image')
        if (firstImage) {
          return `/storage/${firstImage.file_path}`
        }
      }
      
      // Imagen por defecto basada en el slug o nombre
      const slug = props.attraction.slug || props.attraction.name.toLowerCase().replace(/\s+/g, '-')
      return `/images/attractions/${slug}.svg`
    }

    const handleImageError = (event) => {
      // Imagen de fallback
      event.target.src = '/images/placeholder.svg'
    }

    const getTourismTypeLabel = () => {
      const types = {
        'cultural': 'Cultural',
        'natural': 'Natural',
        'adventure': 'Aventura',
        'historical': 'Histórico',
        'religious': 'Religioso',
        'gastronomic': 'Gastronómico',
        'urban': 'Urbano',
        'rural': 'Rural'
      }
      return types[props.attraction.tourism_type] || 'Turismo'
    }

    const getDepartmentName = () => {
      if (props.attraction.department) {
        return props.attraction.department.name
      }
      return 'Bolivia'
    }

    const formatRating = (rating) => {
      return parseFloat(rating).toFixed(1)
    }

    const formatAltitude = (altitude) => {
      return `${altitude.toLocaleString()} msnm`
    }

    const getToursCount = () => {
      return props.attraction.tours_count || props.attraction.tours?.length || 0
    }

    const getMinPrice = () => {
      if (props.attraction.tours && props.attraction.tours.length > 0) {
        const prices = props.attraction.tours.map(tour => parseFloat(tour.price)).filter(price => !isNaN(price))
        return prices.length > 0 ? Math.min(...prices) : null
      }
      return null
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0
      }).format(price)
    }

    const getTags = () => {
      const tags = []
      
      // Agregar tags basados en información práctica
      if (props.attraction.practical_info) {
        const info = typeof props.attraction.practical_info === 'string' 
          ? JSON.parse(props.attraction.practical_info) 
          : props.attraction.practical_info

        if (info.accessibility) tags.push('Accesible')
        if (info.parking) tags.push('Estacionamiento')
        if (info.restaurant) tags.push('Restaurante')
        if (info.guide_required) tags.push('Guía requerido')
      }

      // Agregar tag de altitud si es alta
      if (props.attraction.altitude && props.attraction.altitude > 3500) {
        tags.push('Gran altitud')
      }

      return tags
    }

    const viewDetails = () => {
      const slug = props.attraction.slug || props.attraction.id
      router.visit(`/atractivos/${slug}`)
      emit('view-details', props.attraction)
    }

    const bookTour = () => {
      if (getToursCount() > 0) {
        const slug = props.attraction.slug || props.attraction.id
        router.visit(`/atractivos/${slug}/reservar`)
        emit('book-tour', props.attraction)
      }
    }

    return {
      getMainImage,
      handleImageError,
      getTourismTypeLabel,
      getDepartmentName,
      formatRating,
      formatAltitude,
      getToursCount,
      getMinPrice,
      formatPrice,
      getTags,
      viewDetails,
      bookTour
    }
  }
}
</script>

<style scoped>
.attraction-card {
  @apply relative;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Animación de hover para las tarjetas */
.attraction-card:hover .attraction-overlay {
  @apply opacity-100;
}

.attraction-overlay {
  @apply absolute inset-0 bg-green-600 bg-opacity-10 opacity-0 transition-opacity duration-300 rounded-lg;
}
</style>