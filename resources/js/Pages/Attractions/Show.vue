<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Hero Section con imagen principal -->
      <div class="relative h-96 overflow-hidden">
        <img
          :src="getMainImage()"
          :alt="attraction.name"
          class="w-full h-full object-cover"
          @error="handleImageError"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        
        <!-- Contenido del hero -->
        <div class="absolute bottom-0 left-0 right-0 p-8">
          <div class="max-w-7xl mx-auto">
            <div class="flex items-center space-x-2 mb-4">
              <span class="bg-green-600 text-white text-sm font-medium px-3 py-1 rounded-full">
                {{ getTourismTypeLabel() }}
              </span>
              <div v-if="attraction.rating > 0" class="bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 flex items-center space-x-1">
                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-medium text-gray-800">{{ formatRating(attraction.rating) }}</span>
              </div>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">{{ attraction.name }}</h1>
            
            <div class="flex items-center text-white/90 text-lg">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <span>{{ getDepartmentName() }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Columna principal -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Descripción -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">Descripción</h2>
              <p class="text-gray-700 leading-relaxed">
                {{ attraction.description || 'Descubre este increíble atractivo turístico de Bolivia.' }}
              </p>
            </div>

            <!-- Galería de imágenes -->
            <div v-if="getImages().length > 0" class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">Galería</h2>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="(image, index) in getImages()"
                  :key="index"
                  class="aspect-square overflow-hidden rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                  @click="openImageModal(index)"
                >
                  <img
                    :src="`/storage/${image.file_path}`"
                    :alt="image.alt_text || attraction.name"
                    class="w-full h-full object-cover"
                  >
                </div>
              </div>
            </div>

            <!-- Información práctica -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">Información Práctica</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="attraction.entry_price" class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                  <div>
                    <p class="font-medium text-gray-900">Precio de entrada</p>
                    <p class="text-gray-600">{{ formatPrice(attraction.entry_price) }}</p>
                  </div>
                </div>

                <div v-if="attraction.estimated_duration" class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div>
                    <p class="font-medium text-gray-900">Duración estimada</p>
                    <p class="text-gray-600">{{ formatDuration(attraction.estimated_duration) }}</p>
                  </div>
                </div>

                <div v-if="attraction.difficulty_level" class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                  <div>
                    <p class="font-medium text-gray-900">Dificultad</p>
                    <p class="text-gray-600">{{ attraction.difficulty_level }}</p>
                  </div>
                </div>

                <div v-if="attraction.best_season" class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                  </svg>
                  <div>
                    <p class="font-medium text-gray-900">Mejor época</p>
                    <p class="text-gray-600">{{ attraction.best_season }}</p>
                  </div>
                </div>
              </div>

              <!-- Ubicación -->
              <div v-if="attraction.address || attraction.city" class="mt-6">
                <h3 class="font-medium text-gray-900 mb-2">Ubicación</h3>
                <p class="text-gray-600">
                  {{ attraction.address ? attraction.address + ', ' : '' }}{{ attraction.city || getDepartmentName() }}
                </p>
              </div>
            </div>

            <!-- Reseñas -->
            <div v-if="attraction.reviews && attraction.reviews.length > 0" class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">Reseñas</h2>
              <div class="space-y-4">
                <div
                  v-for="review in attraction.reviews.slice(0, 5)"
                  :key="review.id"
                  class="border-b border-gray-200 pb-4 last:border-b-0"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                          {{ review.user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <span class="font-medium text-gray-900">{{ review.user.name }}</span>
                    </div>
                    <div class="flex items-center">
                      <div class="flex">
                        <svg
                          v-for="i in 5"
                          :key="i"
                          :class="[
                            'w-4 h-4',
                            i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                          ]"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <h4 v-if="review.title" class="font-medium text-gray-900 mb-1">{{ review.title }}</h4>
                  <p class="text-gray-600">{{ review.comment }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Información rápida -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Rápida</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Departamento:</span>
                  <span class="font-medium">{{ getDepartmentName() }}</span>
                </div>
                <div v-if="attraction.entry_price" class="flex justify-between">
                  <span class="text-gray-600">Precio:</span>
                  <span class="font-medium text-green-600">{{ formatPrice(attraction.entry_price) }}</span>
                </div>
                <div v-if="attraction.rating > 0" class="flex justify-between">
                  <span class="text-gray-600">Calificación:</span>
                  <span class="font-medium">{{ formatRating(attraction.rating) }} ⭐</span>
                </div>
                <div v-if="attraction.reviews_count > 0" class="flex justify-between">
                  <span class="text-gray-600">Reseñas:</span>
                  <span class="font-medium">{{ attraction.reviews_count }}</span>
                </div>
              </div>
            </div>

            <!-- Mapa (placeholder) -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Ubicación</h3>
              <div class="aspect-square bg-gray-200 rounded-lg flex items-center justify-center">
                <div class="text-center text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <p class="text-sm">Mapa interactivo</p>
                  <p class="text-xs">Próximamente</p>
                </div>
              </div>
            </div>

            <!-- Botón de acción principal -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <button
                @click="handlePlanVisit"
                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-semibold text-lg"
              >
                Planificar Visita
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de autenticación -->
    <AuthModal
      v-if="showAuthModal"
      :mode="authMode"
      @close="showAuthModal = false; pendingBooking = false"
      @switch-mode="switchAuthMode"
      @auth-success="handleAuthSuccess"
    />

    <!-- Modal de reserva -->
    <BookingForm
      v-if="showBookingModal"
      :attraction="attraction"
      @close="showBookingModal = false"
      @booking-created="handleBookingCreated"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BookingForm from '@/Components/BookingForm.vue'
import AuthModal from '@/Components/AuthModal.vue'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'

// Props
const props = defineProps({
  attraction: {
    type: Object,
    required: true
  }
})

// Composables
const { user, isAuthenticated } = useAuth()
const { showNotification } = useNotifications()

// Reactive data
const showBookingModal = ref(false)
const showAuthModal = ref(false)
const authMode = ref('login')
const pendingBooking = ref(false) // Flag para saber si debe abrir el booking después del login

// Methods
const getMainImage = () => {
  if (props.attraction.media && props.attraction.media.length > 0) {
    const firstImage = props.attraction.media.find(m => m.type === 'image')
    if (firstImage) {
      return `/storage/${firstImage.file_path}`
    }
  }
  
  const slug = props.attraction.slug || props.attraction.name.toLowerCase().replace(/\s+/g, '-')
  return `/images/attractions/${slug}.jpg`
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder.jpg'
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
    'archaeological': 'Arqueológico'
  }
  return types[props.attraction.type] || 'Turismo'
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

const formatPrice = (price) => {
  if (!price) return 'Gratis'
  
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 0
  }).format(price)
}

const formatDuration = (minutes) => {
  if (!minutes) return 'No especificado'
  
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  
  if (hours > 0) {
    return hours + 'h' + (mins > 0 ? ' ' + mins + 'm' : '')
  }
  
  return mins + ' minutos'
}

const getImages = () => {
  if (props.attraction.media) {
    return props.attraction.media.filter(m => m.type === 'image')
  }
  return []
}

const openImageModal = (index) => {
  // Implementar modal de galería de imágenes
  console.log('Abrir imagen en modal:', index)
}

const handlePlanVisit = () => {
  if (!isAuthenticated.value) {
    // Usuario no autenticado, marcar que debe abrir booking después del login
    pendingBooking.value = true
    authMode.value = 'login'
    showAuthModal.value = true
  } else {
    // Usuario autenticado, mostrar formulario de planificación
    showBookingModal.value = true
  }
}

const switchAuthMode = (mode) => {
  authMode.value = mode
}

const handleAuthSuccess = () => {
  showAuthModal.value = false
  
  // Si había una reserva pendiente, abrir el formulario de booking
  if (pendingBooking.value) {
    pendingBooking.value = false
    // Pequeño delay para que se actualice el estado de autenticación
    setTimeout(() => {
      showBookingModal.value = true
    }, 100)
  }
}

const handleBookingCreated = (booking) => {
  showBookingModal.value = false
  showNotification('Visita planificada exitosamente', 'success')
  // Redirigir al dashboard del usuario
  router.visit('/mis-viajes')
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