<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <!-- Tour Image -->
    <div class="relative h-48 bg-gray-200">
      <img 
        v-if="tourImage" 
        :src="tourImage" 
        :alt="tour.name"
        class="w-full h-full object-cover"
        @error="handleImageError"
      >
      <div v-else class="w-full h-full flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
      </div>

      <!-- Tour Type Badge -->
      <div class="absolute top-3 left-3">
        <span 
          class="px-2 py-1 text-xs font-semibold rounded-full"
          :class="getTypeBadgeClass(tour.type)"
        >
          {{ getTypeLabel(tour.type) }}
        </span>
      </div>

      <!-- Featured Badge -->
      <div v-if="tour.is_featured" class="absolute top-3 right-3">
        <span class="bg-yellow-400 text-yellow-900 px-2 py-1 text-xs font-semibold rounded-full">
          Destacado
        </span>
      </div>

      <!-- Duration Badge -->
      <div class="absolute bottom-3 left-3">
        <span class="bg-black bg-opacity-60 text-white px-2 py-1 text-xs rounded-full">
          {{ formatDuration() }}
        </span>
      </div>
    </div>

    <!-- Tour Content -->
    <div class="p-6">
      <!-- Tour Name -->
      <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
        {{ tour.name }}
      </h3>

      <!-- Tour Description -->
      <p class="text-gray-600 text-sm mb-4 line-clamp-3">
        {{ tour.short_description || tour.description }}
      </p>

      <!-- Tour Details -->
      <div class="space-y-2 mb-4">
        <!-- Attractions Count -->
        <div v-if="getAttractionsCount() > 0" class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          {{ getAttractionsCount() }} {{ getAttractionsCount() === 1 ? 'atractivo' : 'atractivos' }}
        </div>

        <!-- Difficulty Level -->
        <div v-if="tour.difficulty_level" class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          Dificultad: {{ getDifficultyLabel(tour.difficulty_level) }}
        </div>

        <!-- Max Participants -->
        <div v-if="tour.max_participants" class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          Máx. {{ tour.max_participants }} personas
        </div>

        <!-- Rating -->
        <div v-if="tour.rating" class="flex items-center text-sm">
          <div class="flex items-center mr-2">
            <svg 
              v-for="star in 5" 
              :key="star"
              class="w-4 h-4"
              :class="star <= Math.round(getRatingNumber()) ? 'text-yellow-400 fill-current' : 'text-gray-300'"
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <span class="text-gray-600">
            {{ getRatingNumber().toFixed(1) }} 
            <span v-if="tour.reviews_count">({{ tour.reviews_count }} reseñas)</span>
          </span>
        </div>
      </div>

      <!-- Price and Actions -->
      <div class="flex items-center justify-between">
        <div class="text-left">
          <div class="text-2xl font-bold text-gray-900">
            Bs. {{ formatPrice(tour.price_per_person) }}
          </div>
          <div class="text-sm text-gray-600">por persona</div>
        </div>

        <div class="flex space-x-2">
          <button
            @click="$emit('view-details', tour)"
            class="px-4 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition-colors text-sm font-medium"
          >
            Ver Detalles
          </button>
          
          <button
            v-if="hasAvailableSchedules()"
            @click="$emit('book-tour', tour)"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium"
          >
            Reservar
          </button>
          
          <span 
            v-else
            class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg text-sm font-medium cursor-not-allowed"
          >
            Sin disponibilidad
          </span>
        </div>
      </div>

      <!-- Next Available Date -->
      <div v-if="getNextAvailableDate()" class="mt-3 text-xs text-gray-500">
        Próxima fecha disponible: {{ getNextAvailableDate() }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tour: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view-details', 'book-tour'])

// Computed properties
const tourImage = computed(() => {
  if (props.tour.media && props.tour.media.length > 0) {
    return props.tour.media[0].url || props.tour.media[0].path
  }
  if (props.tour.attractions && props.tour.attractions.length > 0) {
    const firstAttraction = props.tour.attractions[0]
    if (firstAttraction.media && firstAttraction.media.length > 0) {
      return firstAttraction.media[0].url || firstAttraction.media[0].path
    }
    return firstAttraction.image
  }
  return null
})

// Methods
const getAttractionsCount = () => {
  return props.tour.attractions_count || props.tour.attractions?.length || 0
}

const hasAvailableSchedules = () => {
  return props.tour.schedules && Array.isArray(props.tour.schedules) && props.tour.schedules.length > 0
}

const getNextAvailableDate = () => {
  if (!props.tour.schedules || !Array.isArray(props.tour.schedules) || props.tour.schedules.length === 0) return null
  
  const nextSchedule = props.tour.schedules
    .filter(schedule => schedule && schedule.status === 'available' && schedule.date)
    .sort((a, b) => {
      const dateA = new Date(a.date + ' ' + (a.start_time || '00:00'))
      const dateB = new Date(b.date + ' ' + (b.start_time || '00:00'))
      return dateA - dateB
    })[0]
  
  if (nextSchedule && nextSchedule.date) {
    try {
      return new Date(nextSchedule.date).toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      })
    } catch (e) {
      return null
    }
  }
  return null
}

const getRatingNumber = () => {
  if (!props.tour.rating) return 0
  const rating = typeof props.tour.rating === 'string' ? parseFloat(props.tour.rating) : props.tour.rating
  return isNaN(rating) ? 0 : rating
}

const formatPrice = (price) => {
  if (!price || isNaN(price)) return '0'
  return new Intl.NumberFormat('es-BO').format(price)
}

const formatDuration = () => {
  if (props.tour.duration_days && props.tour.duration_days > 1) {
    return `${props.tour.duration_days} días`
  } else if (props.tour.duration_hours && props.tour.duration_hours > 0) {
    return `${props.tour.duration_hours} horas`
  }
  return '1 día'
}

const getTypeLabel = (type) => {
  const labels = {
    'cultural': 'Cultural',
    'adventure': 'Aventura',
    'nature': 'Naturaleza',
    'historical': 'Histórico',
    'gastronomic': 'Gastronómico'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    'cultural': 'bg-blue-100 text-blue-800',
    'adventure': 'bg-orange-100 text-orange-800',
    'nature': 'bg-green-100 text-green-800',
    'historical': 'bg-purple-100 text-purple-800',
    'gastronomic': 'bg-red-100 text-red-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getDifficultyLabel = (difficulty) => {
  const labels = {
    'easy': 'Fácil',
    'moderate': 'Moderada',
    'hard': 'Difícil',
    'extreme': 'Extrema'
  }
  return labels[difficulty] || difficulty
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>