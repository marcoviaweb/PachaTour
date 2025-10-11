<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Mis Reseñas</h2>
      <span class="text-sm text-gray-500">{{ reviews.length }} reseña{{ reviews.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="reviews.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No has escrito reseñas aún</h3>
      <p class="text-gray-600">Después de completar tus tours, podrás escribir reseñas para ayudar a otros viajeros.</p>
    </div>

    <!-- Reviews List -->
    <div v-else class="space-y-6">
      <div
        v-for="review in reviews"
        :key="review.id"
        class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
      >
        <!-- Review Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ review.attraction_name }}</h3>
              <span 
                :class="getStatusBadgeClass(review.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ getStatusName(review.status) }}
              </span>
            </div>

            <!-- Rating -->
            <div class="flex items-center space-x-2 mb-2">
              <div class="flex text-yellow-400">
                <svg 
                  v-for="star in 5" 
                  :key="star"
                  :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                  class="w-5 h-5" 
                  fill="currentColor" 
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              </div>
              <span class="text-sm text-gray-600">{{ review.rating }}/5</span>
            </div>

            <!-- Review Title -->
            <h4 v-if="review.title" class="font-medium text-gray-900 mb-2">{{ review.title }}</h4>

            <!-- Review Content -->
            <div class="text-gray-700 mb-3">
              <p class="leading-relaxed">{{ review.comment }}</p>
            </div>

            <!-- Review Meta -->
            <div class="flex items-center space-x-4 text-sm text-gray-500">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>{{ formatDate(review.created_at) }}</span>
              </div>

              <div v-if="review.booking_date" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Visitado el {{ formatDate(review.booking_date) }}</span>
              </div>

              <div v-if="review.department_name" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>{{ review.department_name }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col space-y-2 ml-4">
            <button
              v-if="canEditReview(review)"
              @click="$emit('edit-review', review)"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              Editar
            </button>

            <button
              v-if="canDeleteReview(review)"
              @click="$emit('delete-review', review)"
              class="text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Eliminar
            </button>

            <Link
              :href="`/atractivos/${review.attraction_slug}`"
              class="text-green-600 hover:text-green-800 text-sm font-medium"
            >
              Ver Atractivo
            </Link>
          </div>
        </div>

        <!-- Moderation Status -->
        <div 
          v-if="review.status === 'pending'"
          class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
        >
          <div class="flex items-center">
            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm text-yellow-800">
              Tu reseña está siendo revisada por nuestro equipo de moderación.
            </span>
          </div>
        </div>

        <div 
          v-else-if="review.status === 'rejected'"
          class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg"
        >
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <div>
              <p class="text-sm text-red-800 font-medium">Reseña rechazada</p>
              <p v-if="review.moderation_notes" class="text-sm text-red-700 mt-1">
                {{ review.moderation_notes }}
              </p>
              <p class="text-xs text-red-600 mt-2">
                Puedes editar tu reseña para que cumpla con nuestras políticas.
              </p>
            </div>
          </div>
        </div>

        <div 
          v-else-if="review.status === 'approved'"
          class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg"
        >
          <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm text-green-800">
              Tu reseña ha sido publicada y está ayudando a otros viajeros.
            </span>
          </div>
        </div>

        <!-- Helpful Stats -->
        <div 
          v-if="review.status === 'approved' && (review.helpful_count > 0 || review.views_count > 0)"
          class="mt-4 flex items-center space-x-4 text-sm text-gray-600"
        >
          <div v-if="review.views_count > 0" class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <span>{{ review.views_count }} vista{{ review.views_count !== 1 ? 's' : '' }}</span>
          </div>

          <div v-if="review.helpful_count > 0" class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
            </svg>
            <span>{{ review.helpful_count }} persona{{ review.helpful_count !== 1 ? 's' : '' }} encontraron esto útil</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'UserReviews',
  components: {
    Link
  },
  props: {
    reviews: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit-review', 'delete-review'],
  methods: {
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    getStatusBadgeClass(status) {
      const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    getStatusName(status) {
      const names = {
        'pending': 'En revisión',
        'approved': 'Publicada',
        'rejected': 'Rechazada'
      }
      return names[status] || status
    },

    canEditReview(review) {
      // Can edit if pending or rejected, or approved within 30 days
      if (['pending', 'rejected'].includes(review.status)) {
        return true
      }
      
      if (review.status === 'approved') {
        const reviewDate = new Date(review.created_at)
        const now = new Date()
        const daysSinceReview = (now - reviewDate) / (1000 * 60 * 60 * 24)
        return daysSinceReview <= 30
      }
      
      return false
    },

    canDeleteReview(review) {
      // Can delete if not approved, or approved within 7 days
      if (['pending', 'rejected'].includes(review.status)) {
        return true
      }
      
      if (review.status === 'approved') {
        const reviewDate = new Date(review.created_at)
        const now = new Date()
        const daysSinceReview = (now - reviewDate) / (1000 * 60 * 60 * 24)
        return daysSinceReview <= 7
      }
      
      return false
    }
  }
}
</script>