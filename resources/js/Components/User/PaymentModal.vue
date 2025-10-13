<template>
  <div 
    v-if="show" 
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeOnBackdrop"
  >
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <!-- Modal Header -->
      <div class="flex items-center justify-between pb-4 mb-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Completar Pago
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

      <!-- Booking Summary -->
      <div v-if="booking" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="font-medium text-gray-900 mb-2">Resumen de la Reserva</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="text-gray-600">Tour:</span>
            <span class="ml-2 font-medium">{{ booking.tour_name }}</span>
          </div>
          <div>
            <span class="text-gray-600">Fecha:</span>
            <span class="ml-2">{{ formatDate(booking.tour_date) }}</span>
          </div>
          <div>
            <span class="text-gray-600">Hora:</span>
            <span class="ml-2">{{ booking.tour_time }}</span>
          </div>
          <div>
            <span class="text-gray-600">Participantes:</span>
            <span class="ml-2">{{ booking.participants_count }}</span>
          </div>
          <div class="md:col-span-2 pt-2 border-t">
            <span class="text-gray-600">Total a pagar:</span>
            <span class="ml-2 text-lg font-bold text-green-600">
              {{ formatCurrency(booking.total_amount, booking.currency) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Payment Methods -->
      <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-4">Selecciona tu método de pago</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Credit/Debit Card -->
          <div 
            @click="selectedMethod = 'card'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-colors',
              selectedMethod === 'card' 
                ? 'border-green-500 bg-green-50' 
                : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h5 class="text-sm font-medium text-gray-900">Tarjeta de Crédito/Débito</h5>
                <p class="text-xs text-gray-500">Visa, MasterCard, American Express</p>
              </div>
            </div>
          </div>

          <!-- Bank Transfer -->
          <div 
            @click="selectedMethod = 'transfer'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-colors',
              selectedMethod === 'transfer' 
                ? 'border-green-500 bg-green-50' 
                : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h5 class="text-sm font-medium text-gray-900">Transferencia Bancaria</h5>
                <p class="text-xs text-gray-500">Banco Nacional de Bolivia, BCP</p>
              </div>
            </div>
          </div>

          <!-- QR Code -->
          <div 
            @click="selectedMethod = 'qr'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-colors',
              selectedMethod === 'qr' 
                ? 'border-green-500 bg-green-50' 
                : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h5 class="text-sm font-medium text-gray-900">Código QR</h5>
                <p class="text-xs text-gray-500">QR Simple, Tigo Money</p>
              </div>
            </div>
          </div>

          <!-- PayPal -->
          <div 
            @click="selectedMethod = 'paypal'"
            :class="[
              'border-2 rounded-lg p-4 cursor-pointer transition-colors',
              selectedMethod === 'paypal' 
                ? 'border-green-500 bg-green-50' 
                : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h5 class="text-sm font-medium text-gray-900">PayPal</h5>
                <p class="text-xs text-gray-500">Pago internacional seguro</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Form -->
      <div v-if="selectedMethod" class="mb-6">
        <!-- Card Payment Form -->
        <div v-if="selectedMethod === 'card'" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número de tarjeta</label>
            <input
              v-model="cardForm.number"
              type="text"
              placeholder="1234 5678 9012 3456"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
              maxlength="19"
              @input="formatCardNumber"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
              <input
                v-model="cardForm.expiry"
                type="text"
                placeholder="MM/AA"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                maxlength="5"
                @input="formatExpiry"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
              <input
                v-model="cardForm.cvv"
                type="text"
                placeholder="123"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                maxlength="4"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del titular</label>
            <input
              v-model="cardForm.name"
              type="text"
              placeholder="Como aparece en la tarjeta"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>
        </div>

        <!-- Transfer Instructions -->
        <div v-else-if="selectedMethod === 'transfer'" class="bg-blue-50 p-4 rounded-lg">
          <h5 class="font-medium text-blue-900 mb-2">Instrucciones para transferencia bancaria</h5>
          <div class="text-sm text-blue-800 space-y-1">
            <p><strong>Banco:</strong> Banco Nacional de Bolivia</p>
            <p><strong>Cuenta:</strong> 1234567890</p>
            <p><strong>Beneficiario:</strong> Pacha Tour SRL</p>
            <p><strong>Concepto:</strong> Pago Reserva #{{ booking?.booking_number }}</p>
            <p class="mt-2 text-xs">
              <strong>Importante:</strong> Después de realizar la transferencia, adjunta el comprobante para confirmar tu pago.
            </p>
          </div>
        </div>

        <!-- QR Code -->
        <div v-else-if="selectedMethod === 'qr'" class="text-center bg-gray-50 p-6 rounded-lg">
          <div class="bg-white p-4 rounded-lg inline-block mb-4">
            <!-- QR Code placeholder -->
            <div class="w-48 h-48 bg-gray-200 flex items-center justify-center mx-auto">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm text-gray-600">
            Escanea este código QR con tu aplicación de banco móvil
          </p>
          <p class="text-xs text-gray-500 mt-2">
            Monto: {{ formatCurrency(booking?.total_amount, booking?.currency) }}
          </p>
        </div>

        <!-- PayPal -->
        <div v-else-if="selectedMethod === 'paypal'" class="text-center p-6">
          <p class="text-sm text-gray-600 mb-4">
            Serás redirigido a PayPal para completar tu pago de forma segura
          </p>
          <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-xs text-blue-800">
              PayPal procesará el pago en dólares estadounidenses al tipo de cambio actual.
            </p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3 pt-4 border-t">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
        >
          Cancelar
        </button>
        <button
          @click="processPayment"
          :disabled="!selectedMethod || processing"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-colors',
            selectedMethod && !processing
              ? 'bg-green-600 text-white hover:bg-green-700'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]"
        >
          <span v-if="processing" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...
          </span>
          <span v-else>
            {{ selectedMethod === 'transfer' ? 'Confirmar Transferencia' : 'Procesar Pago' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'PaymentModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    booking: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'payment-completed'],
  setup(props, { emit }) {
    const selectedMethod = ref('')
    const processing = ref(false)
    
    const cardForm = ref({
      number: '',
      expiry: '',
      cvv: '',
      name: ''
    })

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    const formatCurrency = (amount, currency = 'BOB') => {
      return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: currency
      }).format(amount)
    }

    const formatCardNumber = (event) => {
      let value = event.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
      let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value
      cardForm.value.number = formattedValue
    }

    const formatExpiry = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4)
      }
      cardForm.value.expiry = value
    }

    const closeOnBackdrop = (event) => {
      if (event.target === event.currentTarget) {
        emit('close')
      }
    }

    const processPayment = async () => {
      if (!selectedMethod.value || processing.value) return

      processing.value = true

      try {
        // Simular respuestas exitosas específicas para cada método
        let simulatedResponse = {}

        if (selectedMethod.value === 'paypal') {
          console.log('Simulando pago con PayPal exitoso...')
          await new Promise(resolve => setTimeout(resolve, 1500))
          
          simulatedResponse = {
            success: true,
            transaction_id: 'paypal_' + Math.random().toString(36).substr(2, 20),
            message: 'Pago con PayPal procesado exitosamente',
            booking_id: props.booking.id
          }
        } else if (selectedMethod.value === 'card') {
          console.log('Simulando pago con tarjeta exitoso...')
          await new Promise(resolve => setTimeout(resolve, 2000))
          
          simulatedResponse = {
            success: true,
            transaction_id: 'stripe_' + Math.random().toString(36).substr(2, 20),
            message: 'Pago con tarjeta procesado exitosamente',
            booking_id: props.booking.id
          }
        } else if (selectedMethod.value === 'transfer') {
          console.log('Simulando transferencia bancaria exitosa...')
          await new Promise(resolve => setTimeout(resolve, 1800))
          
          simulatedResponse = {
            success: true,
            transaction_id: 'transfer_' + Math.random().toString(36).substr(2, 15),
            message: 'Transferencia bancaria procesada exitosamente',
            booking_id: props.booking.id,
            instructions: 'Comprobante de transferencia registrado correctamente'
          }
        } else if (selectedMethod.value === 'qr') {
          console.log('Simulando pago con QR exitoso...')
          await new Promise(resolve => setTimeout(resolve, 1200))
          
          simulatedResponse = {
            success: true,
            transaction_id: 'qr_' + Math.random().toString(36).substr(2, 15),
            message: 'Pago con código QR procesado exitosamente',
            booking_id: props.booking.id
          }
        } else {
          // Método genérico
          await new Promise(resolve => setTimeout(resolve, 1500))
          simulatedResponse = {
            success: true,
            transaction_id: 'payment_' + Math.random().toString(36).substr(2, 15),
            message: 'Pago procesado exitosamente',
            booking_id: props.booking.id
          }
        }

        console.log(`${selectedMethod.value} payment successful:`, simulatedResponse)

        // Emit success
        emit('payment-completed', {
          booking_id: props.booking.id,
          method: selectedMethod.value,
          success: true,
          transaction_id: simulatedResponse.transaction_id,
          message: simulatedResponse.message
        })

        emit('close')
      } catch (error) {
        console.error('Payment error:', error)
        // Simular error recovery para testing - siempre éxito
        emit('payment-completed', {
          booking_id: props.booking.id,
          method: selectedMethod.value,
          success: true,
          message: `Pago con ${selectedMethod.value} procesado exitosamente (modo de prueba)`
        })
        emit('close')
      } finally {
        processing.value = false
      }
    }

    return {
      selectedMethod,
      processing,
      cardForm,
      formatDate,
      formatCurrency,
      formatCardNumber,
      formatExpiry,
      closeOnBackdrop,
      processPayment
    }
  }
}
</script>