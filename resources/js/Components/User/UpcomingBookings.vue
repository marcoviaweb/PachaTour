<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Próximas Reservas</h2>
      <span class="text-sm text-gray-500">{{ bookings.length }} reserva{{ bookings.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="bookings.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No tienes reservas próximas</h3>
      <p class="text-gray-600 mb-4">¡Es hora de planificar tu próxima aventura boliviana!</p>
      <Link 
        href="/atractivos"
        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center"
      >
        Explorar Destinos
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
        </svg>
      </Link>
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
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

            <!-- Additional Info -->
            <div v-if="booking.attraction_name" class="mt-3">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ booking.attraction_name }}</span>
              </div>
            </div>

            <!-- Booking Number -->
            <div class="mt-2">
              <span class="text-xs text-gray-500">Reserva #{{ booking.booking_number }}</span>
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
              v-if="canModifyBooking(booking)"
              @click="$emit('modify', booking)"
              class="text-green-600 hover:text-green-800 text-sm font-medium"
            >
              Modificar
            </button>

            <button
              v-if="canCancelBooking(booking)"
              @click="$emit('cancel', booking)"
              class="text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Cancelar
            </button>
          </div>
        </div>

        <!-- Payment Status Alert -->
        <div 
          v-if="booking.payment_status === 'pending'"
          class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
        >
          <div class="flex items-center">
            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <span class="text-sm text-yellow-800">
              Pago pendiente - 
              <button class="font-medium underline hover:no-underline">
                Completar pago
              </button>
            </span>
          </div>
        </div>

        <!-- Upcoming Alert -->
        <div 
          v-if="isUpcomingSoon(booking.tour_date)"
          class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"
        >
          <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm text-blue-800">
              Tu tour es {{ getTimeUntilTour(booking.tour_date) }}. ¡No olvides llegar puntual!
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'UpcomingBookings',
  components: {
    Link
  },
  props: {
    bookings: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['view-details', 'modify', 'cancel'],
  methods: {
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
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
        'completed': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    canModifyBooking(booking) {
      // Can modify if booking is pending or confirmed and tour is more than 24 hours away
      if (!['pending', 'confirmed'].includes(booking.status)) {
        return false
      }
      
      const tourDate = new Date(booking.tour_date)
      const now = new Date()
      const hoursUntilTour = (tourDate - now) / (1000 * 60 * 60)
      
      return hoursUntilTour > 24
    },

    canCancelBooking(booking) {
      // Can cancel if booking is not already cancelled or completed
      if (['cancelled', 'completed', 'refunded'].includes(booking.status)) {
        return false
      }
      
      const tourDate = new Date(booking.tour_date)
      const now = new Date()
      const hoursUntilTour = (tourDate - now) / (1000 * 60 * 60)
      
      return hoursUntilTour > 24
    },

    isUpcomingSoon(tourDate) {
      const tour = new Date(tourDate)
      const now = new Date()
      const hoursUntilTour = (tour - now) / (1000 * 60 * 60)
      
      return hoursUntilTour > 0 && hoursUntilTour <= 48
    },

    getTimeUntilTour(tourDate) {
      const tour = new Date(tourDate)
      const now = new Date()
      const hoursUntilTour = (tour - now) / (1000 * 60 * 60)
      
      if (hoursUntilTour <= 24) {
        return 'mañana'
      } else if (hoursUntilTour <= 48) {
        return 'pasado mañana'
      } else {
        const days = Math.ceil(hoursUntilTour / 24)
        return `en ${days} días`
      }
    }
  }
}
</script>