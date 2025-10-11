<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              Detalles de la Reserva
            </h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white px-6 py-4 max-h-96 overflow-y-auto">
          <div v-if="booking" class="space-y-6">
            <!-- Booking Status -->
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-xl font-semibold text-gray-900">{{ booking.tour_name }}</h4>
                <p class="text-gray-600">Reserva #{{ booking.booking_number }}</p>
              </div>
              <span 
                :class="getStatusBadgeClass(booking.status)"
                class="px-3 py-1 text-sm font-medium rounded-full"
              >
                {{ booking.status_name }}
              </span>
            </div>

            <!-- Tour Information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Información del Tour</h5>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Fecha:</span>
                  <p class="font-medium">{{ formatDate(booking.tour_date) }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Hora:</span>
                  <p class="font-medium">{{ booking.tour_time }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Duración:</span>
                  <p class="font-medium">{{ booking.tour_duration || 'No especificada' }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Participantes:</span>
                  <p class="font-medium">{{ booking.participants_count }} persona{{ booking.participants_count !== 1 ? 's' : '' }}</p>
                </div>
              </div>
            </div>

            <!-- Location Information -->
            <div v-if="booking.attraction_name" class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Ubicación</h5>
              <div class="text-sm">
                <p class="font-medium">{{ booking.attraction_name }}</p>
                <p v-if="booking.department_name" class="text-gray-600">{{ booking.department_name }}</p>
                <p v-if="booking.meeting_point" class="text-gray-600 mt-2">
                  <span class="font-medium">Punto de encuentro:</span> {{ booking.meeting_point }}
                </p>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Información de Contacto</h5>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Nombre:</span>
                  <p class="font-medium">{{ booking.contact_name }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Email:</span>
                  <p class="font-medium">{{ booking.contact_email }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Teléfono:</span>
                  <p class="font-medium">{{ booking.contact_phone || 'No proporcionado' }}</p>
                </div>
                <div v-if="booking.emergency_contact_name">
                  <span class="text-gray-600">Contacto de emergencia:</span>
                  <p class="font-medium">{{ booking.emergency_contact_name }}</p>
                  <p v-if="booking.emergency_contact_phone" class="text-gray-600">{{ booking.emergency_contact_phone }}</p>
                </div>
              </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Información de Pago</h5>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Total:</span>
                  <p class="font-medium text-lg">{{ formatCurrency(booking.total_amount, booking.currency) }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Estado del pago:</span>
                  <p class="font-medium" :class="getPaymentStatusClass(booking.payment_status)">
                    {{ booking.payment_status_name }}
                  </p>
                </div>
                <div v-if="booking.payment_method">
                  <span class="text-gray-600">Método de pago:</span>
                  <p class="font-medium">{{ booking.payment_method }}</p>
                </div>
                <div v-if="booking.payment_reference">
                  <span class="text-gray-600">Referencia:</span>
                  <p class="font-medium">{{ booking.payment_reference }}</p>
                </div>
              </div>
            </div>

            <!-- Special Requests -->
            <div v-if="booking.special_requests && booking.special_requests.length > 0" class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Solicitudes Especiales</h5>
              <ul class="text-sm text-gray-700 space-y-1">
                <li v-for="request in booking.special_requests" :key="request">
                  • {{ request }}
                </li>
              </ul>
            </div>

            <!-- Participant Details -->
            <div v-if="booking.participant_details && booking.participant_details.length > 0" class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Detalles de Participantes</h5>
              <div class="space-y-3">
                <div 
                  v-for="(participant, index) in booking.participant_details" 
                  :key="index"
                  class="border-l-4 border-green-500 pl-3"
                >
                  <p class="font-medium text-sm">{{ participant.name }}</p>
                  <p v-if="participant.age" class="text-xs text-gray-600">Edad: {{ participant.age }} años</p>
                  <p v-if="participant.document" class="text-xs text-gray-600">Documento: {{ participant.document }}</p>
                </div>
              </div>
            </div>

            <!-- Booking Timeline -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Historial</h5>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Reserva creada:</span>
                  <span class="font-medium">{{ formatDateTime(booking.created_at) }}</span>
                </div>
                <div v-if="booking.confirmed_at" class="flex justify-between">
                  <span class="text-gray-600">Confirmada:</span>
                  <span class="font-medium">{{ formatDateTime(booking.confirmed_at) }}</span>
                </div>
                <div v-if="booking.cancelled_at" class="flex justify-between">
                  <span class="text-gray-600">Cancelada:</span>
                  <span class="font-medium">{{ formatDateTime(booking.cancelled_at) }}</span>
                </div>
                <div v-if="booking.refunded_at" class="flex justify-between">
                  <span class="text-gray-600">Reembolsada:</span>
                  <span class="font-medium">{{ formatDateTime(booking.refunded_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Cancellation/Refund Info -->
            <div v-if="booking.status === 'cancelled' && booking.cancellation_reason" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <h5 class="font-medium text-red-900 mb-2">Motivo de Cancelación</h5>
              <p class="text-sm text-red-700">{{ booking.cancellation_reason }}</p>
            </div>

            <div v-if="booking.status === 'refunded'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h5 class="font-medium text-blue-900 mb-2">Información de Reembolso</h5>
              <p class="text-sm text-blue-700">
                Monto reembolsado: {{ formatCurrency(booking.refund_amount, booking.currency) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
          <div class="flex space-x-3">
            <button
              v-if="canModifyBooking(booking)"
              @click="$emit('modify', booking)"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
            >
              Modificar Reserva
            </button>
            
            <button
              v-if="canCancelBooking(booking)"
              @click="$emit('cancel', booking)"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
            >
              Cancelar Reserva
            </button>
          </div>

          <button
            @click="$emit('close')"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors text-sm font-medium"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BookingDetailsModal',
  props: {
    booking: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'modify', 'cancel'],
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

    formatDateTime(date) {
      if (!date) return ''
      return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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
        'completed': 'bg-green-100 text-green-800',
        'refunded': 'bg-blue-100 text-blue-800',
        'no_show': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    getPaymentStatusClass(status) {
      const classes = {
        'pending': 'text-yellow-600',
        'partial': 'text-orange-600',
        'paid': 'text-green-600',
        'refunded': 'text-blue-600',
        'failed': 'text-red-600'
      }
      return classes[status] || 'text-gray-600'
    },

    canModifyBooking(booking) {
      if (!booking || !['pending', 'confirmed'].includes(booking.status)) {
        return false
      }
      
      const tourDate = new Date(booking.tour_date)
      const now = new Date()
      const hoursUntilTour = (tourDate - now) / (1000 * 60 * 60)
      
      return hoursUntilTour > 24
    },

    canCancelBooking(booking) {
      if (!booking || ['cancelled', 'completed', 'refunded'].includes(booking.status)) {
        return false
      }
      
      const tourDate = new Date(booking.tour_date)
      const now = new Date()
      const hoursUntilTour = (tourDate - now) / (1000 * 60 * 60)
      
      return hoursUntilTour > 24
    }
  }
}
</script>