<template>
  <Head title="Mis Viajes - Pacha Tour" />
  
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header del Dashboard -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Mis Viajes</h1>
            <p class="text-gray-600 mt-1">Gestiona tus reservas y planifica tu aventura boliviana</p>
          </div>
          <div class="flex items-center space-x-4">
            <Link 
              href="/atractivos"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
            >
              Explorar Destinos
            </Link>
          </div>
        </div>
      </div>

      <!-- Estadísticas Rápidas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Reservas Activas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.activeBookings }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Tours Completados</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.completedBookings }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Reseñas Escritas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.reviewsCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Destinos Visitados</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.visitedDestinations }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs de Navegación -->
      <div class="mb-6">
        <nav class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-green-500 text-green-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Estado Vacío - Sin Viajes -->
      <div v-if="hasNoTrips && !loadingBookings" class="bg-white rounded-lg shadow p-12 text-center">
        <div class="max-w-md mx-auto">
          <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-4">¡Aún no tienes viajes planificados!</h3>
          <p class="text-gray-600 mb-8">
            Descubre los increíbles destinos que Bolivia tiene para ofrecerte. 
            Desde el majestuoso Salar de Uyuni hasta las ruinas de Tiwanaku, 
            hay aventuras esperándote en cada rincón del país.
          </p>
          <div class="space-y-4">
            <Link 
              href="/atractivos"
              class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold"
            >
              Explorar Destinos
            </Link>
            <div class="text-sm text-gray-500">
              <p>¿No sabes por dónde empezar?</p>
              <Link href="/departamentos" class="text-green-600 hover:text-green-700 font-medium">
                Explora por departamentos →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido de Tabs -->
      <div v-else class="bg-white rounded-lg shadow">
        <!-- Tab: Próximas Reservas -->
        <div v-if="activeTab === 'upcoming'" class="p-6">
          <UpcomingBookings 
            :bookings="upcomingBookings"
            :loading="loadingBookings"
            @modify="handleModifyBooking"
            @cancel="handleCancelBooking"
            @view-details="handleViewBookingDetails"
            @complete-payment="handleCompletePayment"
          />
        </div>

        <!-- Tab: Historial -->
        <div v-if="activeTab === 'history'" class="p-6">
          <BookingHistory 
            :bookings="bookingHistory"
            :loading="loadingHistory"
            @view-details="handleViewBookingDetails"
            @write-review="handleWriteReview"
            @load-more="loadMoreHistory"
          />
        </div>

        <!-- Tab: Reseñas -->
        <div v-if="activeTab === 'reviews'" class="p-6">
          <UserReviews 
            :reviews="userReviews"
            :loading="loadingReviews"
            @edit-review="handleEditReview"
            @delete-review="handleDeleteReview"
          />
        </div>

        <!-- Tab: Favoritos -->
        <div v-if="activeTab === 'favorites'" class="p-6">
          <UserFavorites 
            :favorites="userFavorites"
            :loading="loadingFavorites"
            @remove-favorite="handleRemoveFavorite"
            @book-tour="handleBookTour"
          />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <BookingDetailsModal
      v-if="showBookingModal"
      :booking="selectedBooking"
      @close="closeBookingModal"
      @modify="handleModifyBooking"
      @cancel="handleCancelBooking"
    />

    <ModifyBookingModal
      v-if="showModifyModal"
      :booking="selectedBooking"
      @close="closeModifyModal"
      @save="handleSaveModification"
    />

    <ReviewModal
      v-if="showReviewModal"
      :booking="selectedBooking"
      :review="selectedReview"
      @close="closeReviewModal"
      @save="handleSaveReview"
    />

    <PaymentModal
      :show="showPaymentModal"
      :booking="selectedBooking"
      @close="closePaymentModal"
      @payment-completed="handlePaymentCompleted"
    />
  </AppLayout>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import UpcomingBookings from '@/Components/User/UpcomingBookings.vue'
import BookingHistory from '@/Components/User/BookingHistory.vue'
import UserReviews from '@/Components/User/UserReviews.vue'
import UserFavorites from '@/Components/User/UserFavorites.vue'
import BookingDetailsModal from '@/Components/User/BookingDetailsModal.vue'
import ModifyBookingModal from '@/Components/User/ModifyBookingModal.vue'
import ReviewModal from '@/Components/User/ReviewModal.vue'
import PaymentModal from '@/Components/User/PaymentModal.vue'
import axios from 'axios'

export default {
  name: 'UserDashboard',
  components: {
    Head,
    Link,
    AppLayout,
    UpcomingBookings,
    BookingHistory,
    UserReviews,
    UserFavorites,
    BookingDetailsModal,
    ModifyBookingModal,
    ReviewModal,
    PaymentModal
  },
  setup() {
    const activeTab = ref('upcoming')
    const loadingBookings = ref(false)
    const loadingHistory = ref(false)
    const loadingReviews = ref(false)
    const loadingFavorites = ref(false)

    // Data
    const stats = ref({
      activeBookings: 0,
      completedBookings: 0,
      reviewsCount: 0,
      visitedDestinations: 0
    })

    const upcomingBookings = ref([])
    const bookingHistory = ref([])
    const userReviews = ref([])
    const userFavorites = ref([])

    // Modals
    const showBookingModal = ref(false)
    const showModifyModal = ref(false)
    const showReviewModal = ref(false)
    const showPaymentModal = ref(false)
    const selectedBooking = ref(null)
    const selectedReview = ref(null)

    const tabs = [
      { id: 'upcoming', name: 'Próximas Reservas' },
      { id: 'history', name: 'Historial' },
      { id: 'reviews', name: 'Mis Reseñas' },
      { id: 'favorites', name: 'Favoritos' }
    ]

    // Methods
    const fetchDashboardStats = async () => {
      try {
        const response = await axios.get('/api/user/dashboard/stats')
        stats.value = response.data
      } catch (error) {
        console.error('Error fetching dashboard stats:', error)
      }
    }

    const fetchUpcomingBookings = async () => {
      loadingBookings.value = true
      try {
        const response = await axios.get('/api/user/bookings/upcoming')
        upcomingBookings.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching upcoming bookings:', error)
        upcomingBookings.value = []
      } finally {
        loadingBookings.value = false
      }
    }

    const fetchBookingHistory = async () => {
      loadingHistory.value = true
      try {
        const response = await axios.get('/api/user/bookings/history')
        bookingHistory.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching booking history:', error)
        bookingHistory.value = []
      } finally {
        loadingHistory.value = false
      }
    }

    const fetchUserReviews = async () => {
      loadingReviews.value = true
      try {
        const response = await axios.get('/api/user/reviews')
        userReviews.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching user reviews:', error)
        userReviews.value = []
      } finally {
        loadingReviews.value = false
      }
    }

    const fetchUserFavorites = async () => {
      loadingFavorites.value = true
      try {
        const response = await axios.get('/api/user/favorites')
        userFavorites.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching user favorites:', error)
        userFavorites.value = []
      } finally {
        loadingFavorites.value = false
      }
    }

    // Event Handlers
    const handleViewBookingDetails = (booking) => {
      selectedBooking.value = booking
      showBookingModal.value = true
    }

    const handleModifyBooking = (booking) => {
      selectedBooking.value = booking
      showModifyModal.value = true
    }

    const handleCancelBooking = async (booking) => {
      if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
        return
      }

      try {
        await axios.post(`/api/bookings/${booking.id}/cancel`)
        // Refresh data
        await fetchUpcomingBookings()
        await fetchDashboardStats()
        alert('Reserva cancelada exitosamente')
      } catch (error) {
        console.error('Error canceling booking:', error)
        alert('Error al cancelar la reserva')
      }
    }

    const handleCompletePayment = (booking) => {
      selectedBooking.value = booking
      showPaymentModal.value = true
    }

    const handleWriteReview = (booking) => {
      selectedBooking.value = booking
      selectedReview.value = null
      showReviewModal.value = true
    }

    const handleEditReview = (review) => {
      selectedReview.value = review
      selectedBooking.value = null
      showReviewModal.value = true
    }

    const handleDeleteReview = async (review) => {
      if (!confirm('¿Estás seguro de que deseas eliminar esta reseña?')) {
        return
      }

      try {
        await axios.delete(`/api/reviews/${review.id}`)
        await fetchUserReviews()
        await fetchDashboardStats()
        alert('Reseña eliminada exitosamente')
      } catch (error) {
        console.error('Error deleting review:', error)
        alert('Error al eliminar la reseña')
      }
    }

    const handleRemoveFavorite = async (favorite) => {
      try {
        await axios.delete(`/api/user/favorites/${favorite.id}`)
        await fetchUserFavorites()
        alert('Eliminado de favoritos')
      } catch (error) {
        console.error('Error removing favorite:', error)
        alert('Error al eliminar de favoritos')
      }
    }

    const handleBookTour = (attraction) => {
      // Redirect to attraction page for booking
      window.location.href = `/atractivos/${attraction.slug}`
    }

    const handleSaveModification = async (modificationData) => {
      try {
        await axios.put(`/api/bookings/${selectedBooking.value.id}`, modificationData)
        closeModifyModal()
        await fetchUpcomingBookings()
        await fetchDashboardStats()
        alert('Reserva modificada exitosamente')
      } catch (error) {
        console.error('Error modifying booking:', error)
        alert('Error al modificar la reserva')
      }
    }

    const handleSaveReview = async (reviewData) => {
      try {
        if (selectedReview.value) {
          // Edit existing review
          await axios.put(`/api/reviews/${selectedReview.value.id}`, reviewData)
        } else {
          // Create new review
          await axios.post('/api/reviews', {
            ...reviewData,
            booking_id: selectedBooking.value.id
          })
        }
        closeReviewModal()
        await fetchUserReviews()
        await fetchBookingHistory()
        await fetchDashboardStats()
        alert('Reseña guardada exitosamente')
      } catch (error) {
        console.error('Error saving review:', error)
        alert('Error al guardar la reseña')
      }
    }

    const loadMoreHistory = async () => {
      // Implement pagination for history
      try {
        const response = await axios.get(`/api/user/bookings/history?page=${Math.floor(bookingHistory.value.length / 10) + 1}`)
        const newBookings = response.data.data || response.data
        bookingHistory.value = [...bookingHistory.value, ...newBookings]
      } catch (error) {
        console.error('Error loading more history:', error)
      }
    }

    // Modal handlers
    const closeBookingModal = () => {
      showBookingModal.value = false
      selectedBooking.value = null
    }

    const closeModifyModal = () => {
      showModifyModal.value = false
      selectedBooking.value = null
    }

    const closeReviewModal = () => {
      showReviewModal.value = false
      selectedBooking.value = null
      selectedReview.value = null
    }

    const closePaymentModal = () => {
      showPaymentModal.value = false
      selectedBooking.value = null
    }

    const handlePaymentCompleted = async (paymentResult) => {
      try {
        // Call backend to confirm payment
        await axios.post(`/api/payments/booking/${paymentResult.booking_id}/confirm`, {
          method: paymentResult.method,
          payment_data: paymentResult
        })

        // Refresh data
        await fetchUpcomingBookings()
        await fetchDashboardStats()
        
        alert('¡Pago completado exitosamente!')
      } catch (error) {
        console.error('Error confirming payment:', error)
        alert('Error al confirmar el pago. Por favor contacta con soporte.')
      }
    }

    // Load data based on active tab
    const loadTabData = () => {
      switch (activeTab.value) {
        case 'upcoming':
          if (upcomingBookings.value.length === 0) {
            fetchUpcomingBookings()
          }
          break
        case 'history':
          if (bookingHistory.value.length === 0) {
            fetchBookingHistory()
          }
          break
        case 'reviews':
          if (userReviews.value.length === 0) {
            fetchUserReviews()
          }
          break
        case 'favorites':
          if (userFavorites.value.length === 0) {
            fetchUserFavorites()
          }
          break
      }
    }

    // Computed property to check if user has no trips
    const hasNoTrips = computed(() => {
      return stats.value.activeBookings === 0 && 
             stats.value.completedBookings === 0 && 
             upcomingBookings.value.length === 0 && 
             bookingHistory.value.length === 0
    })

    // Watch active tab changes
    const watchActiveTab = computed(() => activeTab.value)
    watchActiveTab.value && loadTabData()

    onMounted(() => {
      fetchDashboardStats()
      fetchUpcomingBookings() // Load default tab
      fetchBookingHistory() // Always load history on mount
    })

    return {
      activeTab,
      tabs,
      stats,
      upcomingBookings,
      bookingHistory,
      userReviews,
      userFavorites,
      loadingBookings,
      loadingHistory,
      loadingReviews,
      loadingFavorites,
      showBookingModal,
      showModifyModal,
      showReviewModal,
      showPaymentModal,
      selectedBooking,
      selectedReview,
      hasNoTrips,
      handleViewBookingDetails,
      handleModifyBooking,
      handleCancelBooking,
      handleCompletePayment,
      handleWriteReview,
      handleEditReview,
      handleDeleteReview,
      handleRemoveFavorite,
      handleBookTour,
      handleSaveModification,
      handleSaveReview,
      handlePaymentCompleted,
      loadMoreHistory,
      closeBookingModal,
      closeModifyModal,
      closeReviewModal,
      closePaymentModal,
      loadTabData
    }
  }
}
</script>