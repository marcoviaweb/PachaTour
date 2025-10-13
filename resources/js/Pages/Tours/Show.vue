<template>
  <Head :title="`${tour.name} - Tours - Pacha Tour`" />
  
  <AppLayout>
    <!-- Tour Header -->
    <section class="relative">
      <!-- Hero Image -->
      <div class="relative h-96 bg-gray-200">
        <img 
          v-if="heroImage" 
          :src="heroImage" 
          :alt="tour.name"
          class="w-full h-full object-cover"
        >
        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-r from-purple-600 to-indigo-600">
          <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
          </svg>
        </div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        
        <!-- Content -->
        <div class="absolute inset-0 flex items-end">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 w-full">
            <div class="text-white">
              <!-- Breadcrumbs -->
              <nav class="mb-4">
                <ol class="flex space-x-2 text-sm">
                  <li><Link href="/" class="hover:text-gray-300">Inicio</Link></li>
                  <li class="text-gray-300">/</li>
                  <li><Link href="/tours" class="hover:text-gray-300">Tours</Link></li>
                  <li class="text-gray-300">/</li>
                  <li class="font-medium">{{ tour.name }}</li>
                </ol>
              </nav>
              
              <div class="flex flex-col md:flex-row md:items-end md:justify-between">
                <div class="flex-1">
                  <!-- Tour Type & Featured -->
                  <div class="flex items-center space-x-2 mb-2">
                    <span 
                      class="px-3 py-1 text-sm font-semibold rounded-full"
                      :class="getTypeBadgeClass(tour.type)"
                    >
                      {{ getTypeLabel(tour.type) }}
                    </span>
                    <span v-if="tour.is_featured" class="bg-yellow-400 text-yellow-900 px-3 py-1 text-sm font-semibold rounded-full">
                      Destacado
                    </span>
                  </div>
                  
                  <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ tour.name }}</h1>
                  <p v-if="tour.short_description" class="text-lg text-gray-200 max-w-3xl">
                    {{ tour.short_description }}
                  </p>
                  
                  <!-- Quick Stats -->
                  <div class="flex flex-wrap items-center space-x-6 mt-4 text-sm">
                    <div v-if="tour.rating" class="flex items-center">
                      <svg class="w-4 h-4 text-yellow-400 fill-current mr-1" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      {{ tour.rating.toFixed(1) }} ({{ tour.reviews_count || 0 }} reseñas)
                    </div>
                    <div>{{ formatDuration() }}</div>
                    <div v-if="tour.difficulty_level">{{ getDifficultyLabel(tour.difficulty_level) }}</div>
                  </div>
                </div>
                
                <!-- Price & Booking -->
                <div class="mt-4 md:mt-0 md:ml-8">
                  <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">Bs. {{ formatPrice(tour.price_per_person) }}</div>
                    <div class="text-sm opacity-90">por persona</div>
                    <button 
                      id="booking"
                      @click="scrollToBooking"
                      class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                    >
                      Reservar Ahora
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- Description -->
          <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Descripción del Tour</h2>
            <div class="prose max-w-none text-gray-600">
              <p>{{ tour.description }}</p>
            </div>
          </section>

          <!-- Attractions -->
          <section v-if="tour.attractions && tour.attractions.length > 0" class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Atractivos Incluidos</h2>
            <div class="space-y-4">
              <div 
                v-for="(attraction, index) in tour.attractions" 
                :key="attraction.id"
                class="flex items-start space-x-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex-shrink-0 w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ index + 1 }}. {{ attraction.name }}</h3>
                  <p v-if="attraction.pivot && attraction.pivot.notes" class="text-sm text-gray-600 mt-1">
                    {{ attraction.pivot.notes }}
                  </p>
                  <div class="flex items-center mt-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    </svg>
                    {{ attraction.department?.name }}
                    <span v-if="attraction.pivot && attraction.pivot.duration_minutes" class="ml-4">
                      <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      {{ attraction.pivot.duration_minutes }} min
                    </span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <Link 
                    :href="`/atractivos/${attraction.slug}`"
                    class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                  >
                    Ver Detalles
                  </Link>
                </div>
              </div>
            </div>
          </section>

          <!-- Services -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Included Services -->
            <section v-if="tour.included_services" class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Incluye
              </h3>
              <ul class="space-y-2">
                <li 
                  v-for="service in getServicesList(tour.included_services)" 
                  :key="service"
                  class="flex items-center text-sm text-gray-700"
                >
                  <svg class="w-4 h-4 text-green-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ service }}
                </li>
              </ul>
            </section>

            <!-- Excluded Services -->
            <section v-if="tour.excluded_services" class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                No Incluye
              </h3>
              <ul class="space-y-2">
                <li 
                  v-for="service in getServicesList(tour.excluded_services)" 
                  :key="service"
                  class="flex items-center text-sm text-gray-700"
                >
                  <svg class="w-4 h-4 text-red-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  {{ service }}
                </li>
              </ul>
            </section>
          </div>

          <!-- Requirements & What to Bring -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section v-if="tour.requirements" class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Requisitos</h3>
              <ul class="space-y-2">
                <li 
                  v-for="requirement in getServicesList(tour.requirements)" 
                  :key="requirement"
                  class="text-sm text-gray-700"
                >
                  • {{ requirement }}
                </li>
              </ul>
            </section>

            <section v-if="tour.what_to_bring" class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Qué Traer</h3>
              <ul class="space-y-2">
                <li 
                  v-for="item in getServicesList(tour.what_to_bring)" 
                  :key="item"
                  class="text-sm text-gray-700"
                >
                  • {{ item }}
                </li>
              </ul>
            </section>
          </div>

          <!-- Reviews Section -->
          <section v-if="tour.reviews && tour.reviews.length > 0" class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Reseñas de Viajeros</h2>
            <div class="space-y-6">
              <div 
                v-for="review in tour.reviews" 
                :key="review.id"
                class="border-b border-gray-200 pb-6 last:border-0 last:pb-0"
              >
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center">
                    <div class="font-semibold text-gray-900">{{ review.user.name }}</div>
                    <div class="flex items-center ml-3">
                      <svg 
                        v-for="star in 5" 
                        :key="star"
                        class="w-4 h-4"
                        :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    </div>
                  </div>
                  <time class="text-sm text-gray-500">
                    {{ formatDate(review.created_at) }}
                  </time>
                </div>
                <p class="text-gray-700">{{ review.comment }}</p>
              </div>
            </div>
          </section>
        </div>

        <!-- Right Column - Booking Sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky top-8">
            <!-- Booking Widget -->
            <section class="bg-white rounded-lg shadow-md p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservar Tour</h3>
              
              <!-- Price -->
              <div class="text-center mb-6">
                <div class="text-3xl font-bold text-gray-900">
                  Bs. {{ formatPrice(tour.price_per_person) }}
                </div>
                <div class="text-sm text-gray-600">por persona</div>
              </div>

              <!-- Available Schedules -->
              <div v-if="tour.schedules && tour.schedules.length > 0" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fechas Disponibles
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                  <div 
                    v-for="schedule in tour.schedules" 
                    :key="schedule.id"
                    class="border border-gray-200 rounded-lg p-3 hover:border-purple-300 cursor-pointer transition-colors"
                    :class="selectedSchedule?.id === schedule.id ? 'border-purple-600 bg-purple-50' : ''"
                    @click="selectSchedule(schedule)"
                  >
                    <div class="flex justify-between items-center">
                      <div>
                        <div class="font-medium text-sm">
                          {{ formatScheduleDate(schedule.date_time) }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ formatScheduleTime(schedule.date_time) }}
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-xs text-gray-500">
                          {{ schedule.available_spots || tour.max_participants }} plazas
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Schedules -->
              <div v-else class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h4 class="text-sm font-medium text-gray-900 mb-1">Sin fechas disponibles</h4>
                <p class="text-xs text-gray-500">Próximamente se habilitarán nuevas fechas</p>
              </div>

              <!-- Booking Button -->
              <button
                v-if="selectedSchedule"
                @click="handleBooking"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
              >
                Reservar para {{ formatScheduleDate(selectedSchedule.date_time) }}
              </button>
            </section>

            <!-- Tour Info -->
            <section class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Tour</h3>
              
              <div class="space-y-4">
                <div class="flex justify-between">
                  <span class="text-gray-600">Duración:</span>
                  <span class="font-medium">{{ formatDuration() }}</span>
                </div>
                
                <div v-if="tour.difficulty_level" class="flex justify-between">
                  <span class="text-gray-600">Dificultad:</span>
                  <span class="font-medium">{{ getDifficultyLabel(tour.difficulty_level) }}</span>
                </div>
                
                <div class="flex justify-between">
                  <span class="text-gray-600">Grupo:</span>
                  <span class="font-medium">
                    {{ tour.min_participants || 1 }} - {{ tour.max_participants || 'Sin límite' }} personas
                  </span>
                </div>
                
                <div v-if="tour.guide_language" class="flex justify-between">
                  <span class="text-gray-600">Idioma:</span>
                  <span class="font-medium">{{ tour.guide_language }}</span>
                </div>
                
                <div v-if="tour.meeting_point" class="flex justify-between">
                  <span class="text-gray-600">Punto de encuentro:</span>
                  <span class="font-medium text-right text-sm">{{ tour.meeting_point }}</span>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  tour: {
    type: Object,
    required: true
  }
})

const selectedSchedule = ref(null)

// Computed
const heroImage = computed(() => {
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
const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO').format(price)
}

const formatDuration = () => {
  if (props.tour.duration_days && props.tour.duration_days > 1) {
    return `${props.tour.duration_days} días`
  } else if (props.tour.duration_hours) {
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

const getServicesList = (services) => {
  if (Array.isArray(services)) return services
  if (typeof services === 'string') {
    return services.split('\n').filter(s => s.trim())
  }
  return []
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatScheduleDate = (dateTime) => {
  return new Date(dateTime).toLocaleDateString('es-ES', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
  })
}

const formatScheduleTime = (dateTime) => {
  return new Date(dateTime).toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const selectSchedule = (schedule) => {
  selectedSchedule.value = schedule
}

const scrollToBooking = () => {
  const element = document.getElementById('booking')
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
  }
}

const handleBooking = () => {
  if (!selectedSchedule.value) return
  
  // TODO: Implement booking logic
  alert(`Funcionalidad de reserva pendiente de implementar para ${formatScheduleDate(selectedSchedule.value.date_time)}`)
}
</script>