<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Historial de Reservas</h2>
      <span class="text-sm text-gray-500">{{ bookings.length }} reserva{{ bookings.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="bookings.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No tienes historial de reservas</h3>
      <p class="text-gray-600">Cuando completes tus primeros tours, aparecerán aquí.</p>
    </div>

    <!-- Bookings List -->
    <div v-else class="space-y-4">
      <div
        v-for="booking in bookings"
        :key="booking.id"
        class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between">
          <!-- Booking Info -->
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ booking.tour_name }}</h3>
              <span 
                :class="getStatusBadgeClass(booking.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ booking.status_name }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600 mb-3">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>{{ formatDate(booking.tour_date) }}</span>
              </div>

              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ booking.tour_time }}</span>
              </div>

              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20h8v-2a3 3 0 00-3-3H9a3 3 0 00-3 3v2zM7 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ booking.participants_count }} persona{{ booking.participants_count !== 1 ? 's' : '' }}</span>
              </div>

              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <span class="font-medium">{{ formatCurrency(booking.total_amount, booking.currency) }}</span>
              </div>
            </div>

            <!-- Attraction Info -->
            <div v-if="booking.attraction_name" class="mb-3">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ booking.attraction_name }}</span>
                <span v-if="booking.department_name" class="ml-2 text-gray-500">• {{ booking.department_name }}</span>
              </div>
            </div>

            <!-- Review Status -->
            <div v-if="booking.status === 'completed'" class="mb-2">
              <div v-if="booking.has_review" class="flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                <span>Reseña escrita</span>
                <div class="ml-2 flex items-center">
                  <div class="flex text-yellow-400">
                    <svg 
                      v-for="star in 5" 
                      :key="star"
                      :class="star <= booking.review_rating ? 'text-yellow-400' : 'text-gray-300'"
                      class="w-4 h-4" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </div>
                </div>
              </div>
              <div v-else class="flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                <span>Pendiente de reseña</span>
              </div>
            </div>

            <!-- Booking Number -->
            <div class="text-xs text-gray-500">
              Reserva #{{ booking.booking_number }} • {{ formatRelativeDate(booking.created_at) }}
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col space-y-2 ml-4">
            <button
              @click="$emit('view-details', booking)"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              Ver Detalles
            </button>

            <button
              v-if="booking.status === 'completed' && !booking.has_review"
              @click="$emit('write-review', booking)"
              class="text-green-600 hover:text-green-800 text-sm font-medium"
            >
              Escribir Reseña
            </button>

            <button
              v-if="canRepeatBooking(booking)"
              @click="handleRepeatBooking(booking)"
              class="text-purple-600 hover:text-purple-800 text-sm font-medium"
            >
              Repetir Tour
            </button>
          </div>
        </div>

        <!-- Cancellation Info -->
        <div 
          v-if="booking.status === 'cancelled' && booking.cancellation_reason"
          class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg"
        >
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <div>
              <p class="text-sm text-red-800 font-medium">Reserva cancelada</p>
              <p class="text-sm text-red-700">{{ booking.cancellation_reason }}</p>
              <p v-if="booking.cancelled_at" class="text-xs text-red-600 mt-1">
                Cancelada el {{ formatDate(booking.cancelled_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Refund Info -->
        <div 
          v-if="booking.status === 'refunded'"
          class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"
        >
          <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
              <p class="text-sm text-blue-800 font-medium">
                Reembolso procesado: {{ formatCurrency(booking.refund_amount, booking.currency) }}
              </p>
              <p v-if="booking.refunded_at" class="text-xs text-blue-600">
                Procesado el {{ formatDate(booking.refunded_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="canLoadMore" class="text-center pt-6">
        <button
          @click="$emit('load-more')"
          class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors"
        >
          Cargar más reservas
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BookingHistory',
  props: {
    bookings: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    canLoadMore: {
      type: Boolean,
      default: false
    }
  },
  emits: ['view-details', 'write-review', 'load-more'],
  methods: {
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    formatRelativeDate(date) {
      if (!date) return ''
      const now = new Date()
      const bookingDate = new Date(date)
      const diffTime = Math.abs(now - bookingDate)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays === 1) {
        return 'hace 1 día'
      } else if (diffDays < 30) {
        return `hace ${diffDays} días`
      } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30)
        return `hace ${months} mes${months !== 1 ? 'es' : ''}`
      } else {
        const years = Math.floor(diffDays / 365)
        return `hace ${years} año${years !== 1 ? 's' : ''}`
      }
    },

    formatCurrency(amount, currency = 'BOB') {
      return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: currency
      }).format(amount)
    },

    getStatusBadgeClass(status) {
      const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'completed': 'bg-green-100 text-green-800',
        'refunded': 'bg-blue-100 text-blue-800',
        'no_show': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    canRepeatBooking(booking) {
      return ['completed', 'cancelled'].includes(booking.status)
    },

    handleRepeatBooking(booking) {
      // Redirect to attraction page for new booking
      if (booking.attraction_slug) {
        window.location.href = `/atractivos/${booking.attraction_slug}`
      }
    }
  }
}
</script>