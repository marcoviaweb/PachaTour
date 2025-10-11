<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" @click.stop>
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-900">Planificar Visita</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Attraction Info -->
      <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-2">{{ attraction.name }}</h4>
        <p class="text-sm text-gray-600 mb-3">{{ getDepartmentName() }}</p>
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-600">Precio de entrada:</span>
          <span class="font-semibold text-green-600">
            {{ attraction.entry_price ? formatPrice(attraction.entry_price) : 'Gratis' }}
          </span>
        </div>
      </div>

      <!-- Información del usuario autenticado -->
      <div v-if="user" class="mb-6 bg-green-50 rounded-lg p-4 border border-green-200">
        <h4 class="text-sm font-medium text-green-800 mb-3">Información de contacto</h4>
        <div class="space-y-2">
          <div class="flex items-center">
            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-sm text-gray-700">Nombre:</span>
            <span class="text-sm font-medium text-gray-900 ml-2">{{ user.name }}</span>
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v10a2 2 0 002 2z"></path>
            </svg>
            <span class="text-sm text-gray-700">Email:</span>
            <span class="text-sm font-medium text-gray-900 ml-2">{{ user.email }}</span>
          </div>
        </div>
      </div>

      <!-- Simple Form -->
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label for="visit-date" class="block text-sm font-medium text-gray-700 mb-1">
            Fecha de visita
          </label>
          <input
            id="visit-date"
            v-model="form.date"
            type="date"
            :min="minDate"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
        </div>

        <div>
          <label for="visitors" class="block text-sm font-medium text-gray-700 mb-1">
            Número de visitantes
          </label>
          <select
            id="visitors"
            v-model="form.visitors"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
            <option value="">Seleccionar...</option>
            <option v-for="n in 10" :key="n" :value="n">{{ n }} {{ n === 1 ? 'persona' : 'personas' }}</option>
          </select>
        </div>

        <div>
          <label for="contact-phone" class="block text-sm font-medium text-gray-700 mb-1">
            Teléfono (opcional)
          </label>
          <input
            id="contact-phone"
            v-model="form.phone"
            type="tel"
            placeholder="+591 XXXXXXXX"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
        </div>

        <div>
          <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
            Notas adicionales (opcional)
          </label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            placeholder="Solicitudes especiales, alergias, etc."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          ></textarea>
        </div>

        <!-- Total -->
        <div v-if="totalPrice > 0" class="bg-gray-50 rounded-lg p-4">
          <div class="flex justify-between items-center">
            <span class="font-medium text-gray-900">Total estimado:</span>
            <span class="text-xl font-bold text-green-600">{{ formatPrice(totalPrice) }}</span>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            {{ form.visitors }} {{ form.visitors === 1 ? 'persona' : 'personas' }} × {{ formatPrice(attraction.entry_price) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="!canSubmit || submitting"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="submitting">Guardando...</span>
            <span v-else>Guardar Planificación</span>
          </button>
        </div>
      </form>

      <!-- Info Note -->
      <div class="mt-4 p-3 bg-blue-50 rounded-lg">
        <p class="text-sm text-blue-800">
          <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
          Esta es una planificación inicial. Podrás confirmar los detalles y realizar el pago posteriormente.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useNotifications } from '@/composables/useNotifications'
import { useAuth } from '@/composables/useAuth'

// Props
const props = defineProps({
  attraction: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close', 'booking-created'])

// Composables
const { showNotification } = useNotifications()
const { user, isAuthenticated } = useAuth()

// Reactive data
const submitting = ref(false)

const form = reactive({
  date: '',
  visitors: '',
  phone: '',
  notes: ''
})

// Initialize form with user data
const initializeForm = () => {
  if (user.value) {
    form.phone = user.value.phone || ''
  }
}

// Computed
const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const totalPrice = computed(() => {
  if (!props.attraction.entry_price || !form.visitors) return 0
  return props.attraction.entry_price * parseInt(form.visitors)
})

const canSubmit = computed(() => {
  return form.date && 
         form.visitors && 
         isAuthenticated.value &&
         !submitting.value
})

// Methods
const getDepartmentName = () => {
  return props.attraction.department?.name || 'Bolivia'
}

const formatPrice = (price) => {
  if (!price) return 'Gratis'
  
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 0
  }).format(price)
}

const closeModal = () => {
  emit('close')
}

const submitForm = async () => {
  console.log('🚀 INICIANDO submitForm()')
  console.log('canSubmit:', canSubmit.value)
  console.log('isAuthenticated:', isAuthenticated.value)
  console.log('user:', user.value)
  console.log('form data:', form)
  console.log('attraction:', props.attraction)

  if (!canSubmit.value) {
    console.log('❌ No se puede enviar - canSubmit es false')
    return
  }

  // Verificar autenticación
  if (!isAuthenticated.value) {
    console.log('❌ Usuario no autenticado')
    showNotification('Debes iniciar sesión para planificar una visita', 'error')
    emit('close')
    return
  }

  try {
    submitting.value = true
    console.log('✅ Iniciando proceso de envío...')

    const planningData = {
      attraction_id: props.attraction.id,
      visit_date: form.date,
      visitors_count: parseInt(form.visitors),
      contact_name: user.value.name,
      contact_email: user.value.email,
      contact_phone: form.phone,
      notes: form.notes,
      estimated_total: totalPrice.value
    }

    console.log('📦 Datos de planificación preparados:', planningData)

    // Verificar CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    console.log('🔐 CSRF Token:', csrfToken ? 'PRESENTE' : 'AUSENTE')

    const headers = {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    }

    console.log('📋 Headers preparados:', headers)
    console.log('🌐 Enviando petición a /planificar-visita...')

    // Use native fetch instead of Inertia router to avoid issues
    const response = await fetch('/planificar-visita', {
      method: 'POST',
      headers: headers,
      credentials: 'include',
      body: JSON.stringify(planningData)
    })

    console.log('📡 Respuesta recibida:')
    console.log('   - Status:', response.status)
    console.log('   - StatusText:', response.statusText)
    console.log('   - Headers:', Object.fromEntries(response.headers.entries()))

    let result
    try {
      result = await response.json()
      console.log('📄 Contenido de la respuesta:', result)
    } catch (jsonError) {
      console.error('❌ Error parseando JSON:', jsonError)
      const textResponse = await response.text()
      console.log('📄 Respuesta como texto:', textResponse)
      throw new Error('Respuesta no es JSON válido: ' + textResponse)
    }

    if (response.ok) {
      console.log('✅ ¡ÉXITO! Planificación guardada')
      console.log('   - Booking ID:', result.data?.id)
      console.log('   - Número:', result.data?.booking_number)
      
      showNotification('Planificación guardada exitosamente', 'success')
      emit('booking-created', planningData)
      emit('close')
      
      // Optionally redirect to dashboard
      // router.visit('/mis-viajes')
    } else {
      console.error('❌ ERROR EN LA RESPUESTA:')
      console.error('   - Status:', response.status)
      console.error('   - Mensaje:', result.message)
      console.error('   - Errores:', result.errors)
      
      const errorMessage = result.message || Object.values(result.errors || {})[0] || 'Error al guardar la planificación'
      showNotification(errorMessage, 'error')
    }

  } catch (error) {
    console.error('❌ EXCEPCIÓN CAPTURADA:')
    console.error('   - Tipo:', error.constructor.name)
    console.error('   - Mensaje:', error.message)
    console.error('   - Stack:', error.stack)
    
    showNotification('Error de conexión: ' + error.message, 'error')
  } finally {
    submitting.value = false
    console.log('🏁 Proceso finalizado - submitting:', submitting.value)
  }
}

// Lifecycle
onMounted(() => {
  initializeForm()
})
</script>

<style scoped>
/* Modal backdrop animation */
.fixed {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Modal content animation */
.bg-white {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>