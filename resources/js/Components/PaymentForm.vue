<template>
  <div class="payment-form bg-white rounded-lg shadow-lg p-6">
    <!-- Payment Header -->
    <div class="payment-header mb-6">
      <h3 class="text-2xl font-semibold text-gray-800 mb-2">Información de Pago</h3>
      <p class="text-gray-600">Selecciona tu método de pago preferido</p>
    </div>

    <!-- Payment Summary -->
    <div class="payment-summary bg-gray-50 rounded-lg p-4 mb-6">
      <h4 class="font-semibold text-gray-800 mb-3">Resumen de la Reserva</h4>
      <div class="space-y-2">
        <div class="flex justify-between">
          <span class="text-gray-600">Tour:</span>
          <span class="font-medium">{{ booking.tour_name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Fecha:</span>
          <span class="font-medium">{{ formatDate(booking.booking_date) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Personas:</span>
          <span class="font-medium">{{ booking.number_of_people }}</span>
        </div>
        <div class="border-t pt-2 mt-3">
          <div class="flex justify-between text-lg font-semibold">
            <span>Total:</span>
            <span class="text-green-600">Bs. {{ formatPrice(booking.total_amount) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="payment-methods mb-6">
      <h4 class="font-semibold text-gray-800 mb-4">Método de Pago</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="method in paymentMethods" 
          :key="method.id"
          @click="selectPaymentMethod(method.id)"
          :class="[
            'payment-method-card p-4 border-2 rounded-lg cursor-pointer transition-all',
            selectedMethod === method.id 
              ? 'border-blue-500 bg-blue-50' 
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <div class="flex items-center space-x-3">
            <div class="payment-method-icon">
              <component :is="getMethodIcon(method.id)" class="w-8 h-8" />
            </div>
            <div>
              <h5 class="font-medium text-gray-800">{{ method.name }}</h5>
              <p class="text-sm text-gray-600">{{ method.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Forms -->
    <div v-if="selectedMethod" class="payment-form-container">
      <!-- Credit/Debit Card Form -->
      <div v-if="selectedMethod === 'credit_card' || selectedMethod === 'debit_card'" class="card-form">
        <h5 class="font-semibold text-gray-800 mb-4">
          {{ selectedMethod === 'credit_card' ? 'Información de Tarjeta de Crédito' : 'Información de Tarjeta de Débito' }}
        </h5>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Número de Tarjeta *
            </label>
            <input
              v-model="cardForm.cardNumber"
              @input="formatCardNumber"
              type="text"
              placeholder="1234 5678 9012 3456"
              maxlength="19"
              :class="[
                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                errors.cardNumber ? 'border-red-500' : 'border-gray-300'
              ]"
            />
            <p v-if="errors.cardNumber" class="text-red-500 text-sm mt-1">{{ errors.cardNumber }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombre del Titular *
            </label>
            <input
              v-model="cardForm.cardholderName"
              type="text"
              placeholder="Juan Pérez"
              :class="[
                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                errors.cardholderName ? 'border-red-500' : 'border-gray-300'
              ]"
            />
            <p v-if="errors.cardholderName" class="text-red-500 text-sm mt-1">{{ errors.cardholderName }}</p>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mes *
              </label>
              <select
                v-model="cardForm.expiryMonth"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                  errors.expiryMonth ? 'border-red-500' : 'border-gray-300'
                ]"
              >
                <option value="">Mes</option>
                <option v-for="month in 12" :key="month" :value="month">
                  {{ String(month).padStart(2, '0') }}
                </option>
              </select>
              <p v-if="errors.expiryMonth" class="text-red-500 text-sm mt-1">{{ errors.expiryMonth }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Año *
              </label>
              <select
                v-model="cardForm.expiryYear"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                  errors.expiryYear ? 'border-red-500' : 'border-gray-300'
                ]"
              >
                <option value="">Año</option>
                <option v-for="year in getYearOptions()" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
              <p v-if="errors.expiryYear" class="text-red-500 text-sm mt-1">{{ errors.expiryYear }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CVV *
              </label>
              <input
                v-model="cardForm.cvv"
                type="text"
                placeholder="123"
                maxlength="4"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                  errors.cvv ? 'border-red-500' : 'border-gray-300'
                ]"
              />
              <p v-if="errors.cvv" class="text-red-500 text-sm mt-1">{{ errors.cvv }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bank Transfer Form -->
      <div v-else-if="selectedMethod === 'bank_transfer'" class="bank-form">
        <h5 class="font-semibold text-gray-800 mb-4">Información de Transferencia Bancaria</h5>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Banco *
            </label>
            <select
              v-model="bankForm.bankCode"
              :class="[
                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                errors.bankCode ? 'border-red-500' : 'border-gray-300'
              ]"
            >
              <option value="">Selecciona tu banco</option>
              <option value="BNB">Banco Nacional de Bolivia</option>
              <option value="BCP">Banco de Crédito del Perú</option>
              <option value="BISA">Banco BISA</option>
              <option value="BUN">Banco Unión</option>
              <option value="BME">Banco Mercantil Santa Cruz</option>
            </select>
            <p v-if="errors.bankCode" class="text-red-500 text-sm mt-1">{{ errors.bankCode }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Número de Cuenta *
            </label>
            <input
              v-model="bankForm.accountNumber"
              type="text"
              placeholder="1234567890"
              :class="[
                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                errors.accountNumber ? 'border-red-500' : 'border-gray-300'
              ]"
            />
            <p v-if="errors.accountNumber" class="text-red-500 text-sm mt-1">{{ errors.accountNumber }}</p>
          </div>
        </div>
      </div>

      <!-- QR Code Payment -->
      <div v-else-if="selectedMethod === 'qr_code'" class="qr-form">
        <h5 class="font-semibold text-gray-800 mb-4">Pago con Código QR</h5>
        <div class="text-center">
          <p class="text-gray-600 mb-4">
            Escanea el código QR con tu aplicación de banca móvil para completar el pago
          </p>
          <div v-if="qrCode" class="qr-code-container inline-block p-4 bg-white border rounded-lg">
            <img :src="qrCode" alt="Código QR" class="w-48 h-48 mx-auto" />
          </div>
          <div v-else class="qr-placeholder w-48 h-48 mx-auto bg-gray-200 rounded-lg flex items-center justify-center">
            <span class="text-gray-500">Generando QR...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Messages -->
    <div v-if="paymentError" class="error-message bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span class="text-red-700">{{ paymentError }}</span>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="payment-actions flex flex-col sm:flex-row gap-4">
      <button
        @click="$emit('back')"
        type="button"
        class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
      >
        Volver
      </button>
      
      <button
        @click="processPayment"
        :disabled="!canProcessPayment || processing"
        :class="[
          'flex-1 px-6 py-3 rounded-lg font-medium transition-colors',
          canProcessPayment && !processing
            ? 'bg-blue-600 text-white hover:bg-blue-700'
            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
        ]"
      >
        <span v-if="processing" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Procesando...
        </span>
        <span v-else>
          Pagar Bs. {{ formatPrice(booking.total_amount) }}
        </span>
      </button>
    </div>

    <!-- Payment Confirmation Modal -->
    <PaymentConfirmation
      v-if="showConfirmation"
      :payment-result="paymentResult"
      :booking="booking"
      @close="closeConfirmation"
      @download-receipt="downloadReceipt"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import PaymentConfirmation from './PaymentConfirmation.vue'
import api from '@/services/api'

// Icons (you can replace with actual icon components)
const CreditCardIcon = {
  template: `<svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
  </svg>`
}

const BankIcon = {
  template: `<svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z"/>
  </svg>`
}

const QRIcon = {
  template: `<svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zm2 2V5h1v1h-1z"/>
  </svg>`
}

// Props
const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['back', 'payment-success', 'payment-error'])

// Composables
const { showNotification } = useNotifications()

// Reactive data
const selectedMethod = ref('')
const paymentMethods = ref([])
const processing = ref(false)
const paymentError = ref('')
const showConfirmation = ref(false)
const paymentResult = ref(null)
const qrCode = ref('')

// Form data
const cardForm = reactive({
  cardNumber: '',
  cardholderName: '',
  expiryMonth: '',
  expiryYear: '',
  cvv: ''
})

const bankForm = reactive({
  bankCode: '',
  accountNumber: ''
})

// Validation errors
const errors = reactive({
  cardNumber: '',
  cardholderName: '',
  expiryMonth: '',
  expiryYear: '',
  cvv: '',
  bankCode: '',
  accountNumber: ''
})

// Computed properties
const canProcessPayment = computed(() => {
  if (!selectedMethod.value) return false
  
  switch (selectedMethod.value) {
    case 'credit_card':
    case 'debit_card':
      return cardForm.cardNumber && 
             cardForm.cardholderName && 
             cardForm.expiryMonth && 
             cardForm.expiryYear && 
             cardForm.cvv &&
             Object.values(errors).every(error => !error)
    
    case 'bank_transfer':
      return bankForm.bankCode && 
             bankForm.accountNumber &&
             !errors.bankCode && 
             !errors.accountNumber
    
    case 'qr_code':
      return true
    
    default:
      return false
  }
})

// Methods
const loadPaymentMethods = async () => {
  try {
    const response = await api.get('/payments/methods')
    paymentMethods.value = response.data.payment_methods.filter(method => method.enabled)
  } catch (error) {
    console.error('Error loading payment methods:', error)
    showNotification('Error al cargar métodos de pago', 'error')
  }
}

const selectPaymentMethod = (methodId) => {
  selectedMethod.value = methodId
  clearErrors()
  
  // Generate QR code for QR payment
  if (methodId === 'qr_code') {
    generateQRCode()
  }
}

const getMethodIcon = (methodId) => {
  switch (methodId) {
    case 'credit_card':
    case 'debit_card':
      return CreditCardIcon
    case 'bank_transfer':
      return BankIcon
    case 'qr_code':
      return QRIcon
    default:
      return CreditCardIcon
  }
}

const formatCardNumber = () => {
  // Remove all non-digits
  let value = cardForm.cardNumber.replace(/\D/g, '')
  
  // Add spaces every 4 digits
  value = value.replace(/(\d{4})(?=\d)/g, '$1 ')
  
  cardForm.cardNumber = value
  validateCardNumber()
}

const validateCardNumber = () => {
  const number = cardForm.cardNumber.replace(/\s/g, '')
  
  if (!number) {
    errors.cardNumber = 'El número de tarjeta es obligatorio'
  } else if (number.length < 13 || number.length > 19) {
    errors.cardNumber = 'El número de tarjeta debe tener entre 13 y 19 dígitos'
  } else if (!isValidCardNumber(number)) {
    errors.cardNumber = 'Número de tarjeta inválido'
  } else {
    errors.cardNumber = ''
  }
}

const isValidCardNumber = (number) => {
  // Luhn algorithm for card validation
  let sum = 0
  let alternate = false
  
  for (let i = number.length - 1; i >= 0; i--) {
    let n = parseInt(number.charAt(i), 10)
    
    if (alternate) {
      n *= 2
      if (n > 9) {
        n = (n % 10) + 1
      }
    }
    
    sum += n
    alternate = !alternate
  }
  
  return (sum % 10) === 0
}

const validateForm = () => {
  clearErrors()
  let isValid = true
  
  if (selectedMethod.value === 'credit_card' || selectedMethod.value === 'debit_card') {
    // Validate card form
    if (!cardForm.cardNumber) {
      errors.cardNumber = 'El número de tarjeta es obligatorio'
      isValid = false
    } else {
      validateCardNumber()
      if (errors.cardNumber) isValid = false
    }
    
    if (!cardForm.cardholderName) {
      errors.cardholderName = 'El nombre del titular es obligatorio'
      isValid = false
    } else if (cardForm.cardholderName.length < 2) {
      errors.cardholderName = 'El nombre debe tener al menos 2 caracteres'
      isValid = false
    }
    
    if (!cardForm.expiryMonth) {
      errors.expiryMonth = 'El mes es obligatorio'
      isValid = false
    }
    
    if (!cardForm.expiryYear) {
      errors.expiryYear = 'El año es obligatorio'
      isValid = false
    } else if (isCardExpired()) {
      errors.expiryYear = 'La tarjeta está vencida'
      isValid = false
    }
    
    if (!cardForm.cvv) {
      errors.cvv = 'El CVV es obligatorio'
      isValid = false
    } else if (!/^\d{3,4}$/.test(cardForm.cvv)) {
      errors.cvv = 'El CVV debe tener 3 o 4 dígitos'
      isValid = false
    }
  } else if (selectedMethod.value === 'bank_transfer') {
    // Validate bank form
    if (!bankForm.bankCode) {
      errors.bankCode = 'Selecciona un banco'
      isValid = false
    }
    
    if (!bankForm.accountNumber) {
      errors.accountNumber = 'El número de cuenta es obligatorio'
      isValid = false
    } else if (!/^\d{8,20}$/.test(bankForm.accountNumber)) {
      errors.accountNumber = 'El número de cuenta debe tener entre 8 y 20 dígitos'
      isValid = false
    }
  }
  
  return isValid
}

const isCardExpired = () => {
  const currentDate = new Date()
  const currentYear = currentDate.getFullYear()
  const currentMonth = currentDate.getMonth() + 1
  
  const expiryYear = parseInt(cardForm.expiryYear)
  const expiryMonth = parseInt(cardForm.expiryMonth)
  
  if (expiryYear < currentYear) return true
  if (expiryYear === currentYear && expiryMonth < currentMonth) return true
  
  return false
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  paymentError.value = ''
}

const getYearOptions = () => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = 0; i < 20; i++) {
    years.push(currentYear + i)
  }
  return years
}

const generateQRCode = async () => {
  try {
    // In a real implementation, this would call an API to generate a QR code
    // For now, we'll simulate it
    qrCode.value = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
  } catch (error) {
    console.error('Error generating QR code:', error)
  }
}

const processPayment = async () => {
  if (!validateForm()) {
    return
  }
  
  processing.value = true
  paymentError.value = ''
  
  try {
    const paymentData = getPaymentData()
    
    const response = await api.post('/payments/process', {
      booking_id: props.booking.id,
      payment_method: selectedMethod.value,
      payment_data: paymentData
    })
    
    if (response.data.success) {
      paymentResult.value = response.data
      showConfirmation.value = true
      emit('payment-success', response.data)
      showNotification('Pago procesado exitosamente', 'success')
    } else {
      throw new Error(response.data.message || 'Error al procesar el pago')
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message || 'Error al procesar el pago'
    paymentError.value = errorMessage
    emit('payment-error', errorMessage)
    showNotification(errorMessage, 'error')
  } finally {
    processing.value = false
  }
}

const getPaymentData = () => {
  switch (selectedMethod.value) {
    case 'credit_card':
    case 'debit_card':
      return {
        card_number: cardForm.cardNumber.replace(/\s/g, ''),
        cardholder_name: cardForm.cardholderName,
        expiry_month: cardForm.expiryMonth,
        expiry_year: cardForm.expiryYear,
        cvv: cardForm.cvv
      }
    
    case 'bank_transfer':
      return {
        bank_code: bankForm.bankCode,
        account_number: bankForm.accountNumber
      }
    
    case 'qr_code':
      return {}
    
    default:
      return {}
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

const closeConfirmation = () => {
  showConfirmation.value = false
}

const downloadReceipt = () => {
  // Implementation for downloading receipt
  showNotification('Descargando comprobante...', 'info')
}

// Watchers
watch(() => cardForm.cardholderName, () => {
  if (errors.cardholderName && cardForm.cardholderName.length >= 2) {
    errors.cardholderName = ''
  }
})

watch(() => cardForm.cvv, () => {
  if (errors.cvv && /^\d{3,4}$/.test(cardForm.cvv)) {
    errors.cvv = ''
  }
})

watch(() => bankForm.bankCode, () => {
  if (errors.bankCode && bankForm.bankCode) {
    errors.bankCode = ''
  }
})

watch(() => bankForm.accountNumber, () => {
  if (errors.accountNumber && /^\d{8,20}$/.test(bankForm.accountNumber)) {
    errors.accountNumber = ''
  }
})

// Lifecycle
onMounted(() => {
  loadPaymentMethods()
})
</script>

<style scoped>
.payment-method-card {
  transition: all 0.2s ease;
}

.payment-method-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.qr-code-container {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.error-message {
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Card number input styling */
input[type="text"]:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled button styling */
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Loading spinner */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>