<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-screen overflow-y-auto">
      <!-- Success Header -->
      <div class="text-center p-6 border-b">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
          <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Pago Exitoso!</h3>
        <p class="text-gray-600">Tu reserva ha sido confirmada</p>
      </div>

      <!-- Payment Details -->
      <div class="p-6 space-y-4">
        <!-- Transaction Info -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="font-semibold text-gray-800 mb-3">Detalles de la Transacción</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">ID de Pago:</span>
              <span class="font-mono text-gray-800">{{ paymentResult.payment_id }}</span>
            </div>
            <div v-if="paymentResult.transaction_id" class="flex justify-between">
              <span class="text-gray-600">ID de Transacción:</span>
              <span class="font-mono text-gray-800">{{ paymentResult.transaction_id }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Estado:</span>
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                {{ getStatusText(paymentResult.status) }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Fecha:</span>
              <span class="text-gray-800">{{ formatDateTime(new Date()) }}</span>
            </div>
          </div>
        </div>

        <!-- Booking Summary -->
        <div class="bg-blue-50 rounded-lg p-4">
          <h4 class="font-semibold text-gray-800 mb-3">Resumen de la Reserva</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Tour:</span>
              <span class="font-medium text-gray-800">{{ booking.tour_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Fecha:</span>
              <span class="text-gray-800">{{ formatDate(booking.booking_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Hora:</span>
              <span class="text-gray-800">{{ booking.booking_time }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Personas:</span>
              <span class="text-gray-800">{{ booking.number_of_people }}</span>
            </div>
            <div class="border-t pt-2 mt-3">
              <div class="flex justify-between font-semibold">
                <span class="text-gray-800">Total Pagado:</span>
                <span class="text-green-600">Bs. {{ formatPrice(booking.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Important Information -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
              <h5 class="font-medium text-yellow-800 mb-1">Información Importante</h5>
              <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Guarda este comprobante para futuras referencias</li>
                <li>• Presenta tu documento de identidad el día del tour</li>
                <li>• Llega 15 minutos antes de la hora programada</li>
                <li>• Para cancelaciones, contacta con 48 horas de anticipación</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h5 class="font-medium text-blue-800 mb-2">Próximos Pasos</h5>
          <div class="text-sm text-blue-700 space-y-2">
            <p>1. Recibirás un email de confirmación en los próximos minutos</p>
            <p>2. Puedes ver todos tus tours en "Mis Viajes"</p>
            <p>3. El operador turístico te contactará 24 horas antes del tour</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="p-6 border-t bg-gray-50 flex flex-col sm:flex-row gap-3">
        <button
          @click="downloadReceipt"
          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Descargar Comprobante
        </button>
        
        <button
          @click="goToMyTrips"
          class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Ver Mis Viajes
        </button>
        
        <button
          @click="close"
          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

// Props
const props = defineProps({
  paymentResult: {
    type: Object,
    required: true
  },
  booking: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close', 'download-receipt'])

// Router
const router = useRouter()

// Methods
const getStatusText = (status) => {
  const statusMap = {
    'completed': 'Completado',
    'pending': 'Pendiente',
    'failed': 'Fallido',
    'refunded': 'Reembolsado'
  }
  return statusMap[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

const close = () => {
  emit('close')
}

const downloadReceipt = () => {
  emit('download-receipt')
}

const goToMyTrips = () => {
  close()
  router.push('/user/dashboard')
}
</script>

<style scoped>
/* Modal animation */
.fixed {
  animation: fadeIn 0.3s ease;
}

.bg-white {
  animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Success icon animation */
.bg-green-100 svg {
  animation: checkmark 0.6s ease;
}

@keyframes checkmark {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}
</style>