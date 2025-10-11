<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              Modificar Reserva
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
        <form @submit.prevent="handleSubmit" class="bg-white">
          <div class="px-6 py-4 space-y-6">
            <!-- Current Booking Info -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-2">Reserva Actual</h4>
              <div class="text-sm text-gray-600 space-y-1">
                <p><span class="font-medium">Tour:</span> {{ booking.tour_name }}</p>
                <p><span class="font-medium">Fecha:</span> {{ formatDate(booking.tour_date) }}</p>
                <p><span class="font-medium">Hora:</span> {{ booking.tour_time }}</p>
                <p><span class="font-medium">Participantes:</span> {{ booking.participants_count }}</p>
              </div>
            </div>

            <!-- New Date Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nueva Fecha *
              </label>
              <input
                v-model="form.tour_date"
                type="date"
                :min="minDate"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.tour_date }"
              />
              <p v-if="errors.tour_date" class="mt-1 text-sm text-red-600">{{ errors.tour_date }}</p>
            </div>

            <!-- Available Times -->
            <div v-if="availableTimes.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Horario Disponible *
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="time in availableTimes"
                  :key="time.id"
                  type="button"
                  @click="selectTime(time)"
                  :class="[
                    'p-3 text-sm border rounded-lg transition-colors',
                    form.tour_schedule_id === time.id
                      ? 'border-green-500 bg-green-50 text-green-700'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                  :disabled="time.available_spots < form.participants_count"
                >
                  <div class="font-medium">{{ time.start_time }}</div>
                  <div class="text-xs text-gray-500">
                    {{ time.available_spots }} cupos disponibles
                  </div>
                </button>
              </div>
              <p v-if="errors.tour_schedule_id" class="mt-1 text-sm text-red-600">{{ errors.tour_schedule_id }}</p>
            </div>

            <!-- Loading Times -->
            <div v-else-if="loadingTimes" class="text-center py-4">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mx-auto"></div>
              <p class="text-sm text-gray-600 mt-2">Cargando horarios disponibles...</p>
            </div>

            <!-- No Times Available -->
            <div v-else-if="form.tour_date" class="text-center py-4">
              <p class="text-sm text-gray-600">No hay horarios disponibles para esta fecha.</p>
            </div>

            <!-- Number of Participants -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Número de Participantes *
              </label>
              <select
                v-model="form.participants_count"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.participants_count }"
                @change="checkAvailability"
              >
                <option value="">Seleccionar...</option>
                <option v-for="n in 10" :key="n" :value="n">{{ n }} persona{{ n !== 1 ? 's' : '' }}</option>
              </select>
              <p v-if="errors.participants_count" class="mt-1 text-sm text-red-600">{{ errors.participants_count }}</p>
            </div>

            <!-- Contact Information -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Información de Contacto</h4>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nombre de Contacto *
                </label>
                <input
                  v-model="form.contact_name"
                  type="text"
                  required
                  maxlength="100"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.contact_name }"
                />
                <p v-if="errors.contact_name" class="mt-1 text-sm text-red-600">{{ errors.contact_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Email de Contacto *
                </label>
                <input
                  v-model="form.contact_email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.contact_email }"
                />
                <p v-if="errors.contact_email" class="mt-1 text-sm text-red-600">{{ errors.contact_email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Teléfono de Contacto
                </label>
                <input
                  v-model="form.contact_phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.contact_phone }"
                />
                <p v-if="errors.contact_phone" class="mt-1 text-sm text-red-600">{{ errors.contact_phone }}</p>
              </div>
            </div>

            <!-- Special Requests -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Solicitudes Especiales
              </label>
              <textarea
                v-model="form.special_requests"
                rows="3"
                maxlength="500"
                placeholder="Menciona cualquier requerimiento especial, alergias, o necesidades específicas..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.special_requests }"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">{{ form.special_requests.length }}/500 caracteres</p>
              <p v-if="errors.special_requests" class="mt-1 text-sm text-red-600">{{ errors.special_requests }}</p>
            </div>

            <!-- Price Difference -->
            <div v-if="priceDifference !== 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h5 class="font-medium text-blue-900 mb-2">Diferencia de Precio</h5>
              <p class="text-sm text-blue-700">
                <span v-if="priceDifference > 0">
                  Deberás pagar {{ formatCurrency(priceDifference) }} adicional.
                </span>
                <span v-else>
                  Se te reembolsará {{ formatCurrency(Math.abs(priceDifference)) }}.
                </span>
              </p>
            </div>

            <!-- Modification Policy -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <h5 class="font-medium text-yellow-900 mb-2">Política de Modificación</h5>
              <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Las modificaciones deben realizarse con al menos 24 horas de anticipación</li>
                <li>• Pueden aplicar diferencias de precio según la nueva fecha/horario</li>
                <li>• Los cambios están sujetos a disponibilidad</li>
                <li>• Se enviará una confirmación por email</li>
              </ul>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors text-sm font-medium"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!canSubmit || submitting"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Guardando...</span>
              <span v-else>Guardar Cambios</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ModifyBookingModal',
  props: {
    booking: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      form: {
        tour_date: '',
        tour_schedule_id: null,
        participants_count: this.booking?.participants_count || 1,
        contact_name: this.booking?.contact_name || '',
        contact_email: this.booking?.contact_email || '',
        contact_phone: this.booking?.contact_phone || '',
        special_requests: this.booking?.special_requests?.join('\n') || ''
      },
      availableTimes: [],
      loadingTimes: false,
      submitting: false,
      errors: {},
      priceDifference: 0
    }
  },
  computed: {
    minDate() {
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      return tomorrow.toISOString().split('T')[0]
    },

    canSubmit() {
      return this.form.tour_date && 
             this.form.tour_schedule_id && 
             this.form.participants_count && 
             this.form.contact_name && 
             this.form.contact_email &&
             !this.submitting
    }
  },
  watch: {
    'form.tour_date'() {
      this.loadAvailableTimes()
    },

    'form.participants_count'() {
      this.checkAvailability()
    }
  },
  methods: {
    async loadAvailableTimes() {
      if (!this.form.tour_date) {
        this.availableTimes = []
        return
      }

      this.loadingTimes = true
      try {
        const response = await axios.get(`/api/tours/${this.booking.tour_id}/availability`, {
          params: {
            date: this.form.tour_date,
            participants: this.form.participants_count
          }
        })
        this.availableTimes = response.data.data || response.data
        
        // Reset selected time if not available
        if (!this.availableTimes.find(t => t.id === this.form.tour_schedule_id)) {
          this.form.tour_schedule_id = null
        }
      } catch (error) {
        console.error('Error loading available times:', error)
        this.availableTimes = []
      } finally {
        this.loadingTimes = false
      }
    },

    selectTime(time) {
      if (time.available_spots >= this.form.participants_count) {
        this.form.tour_schedule_id = time.id
        this.calculatePriceDifference(time)
      }
    },

    checkAvailability() {
      // Filter available times based on participant count
      this.availableTimes = this.availableTimes.filter(time => 
        time.available_spots >= this.form.participants_count
      )
      
      // Reset selected time if not enough spots
      const selectedTime = this.availableTimes.find(t => t.id === this.form.tour_schedule_id)
      if (!selectedTime || selectedTime.available_spots < this.form.participants_count) {
        this.form.tour_schedule_id = null
      }
    },

    calculatePriceDifference(time) {
      // Calculate price difference based on new schedule and participant count
      const originalTotal = this.booking.total_amount
      const newTotal = time.price * this.form.participants_count
      this.priceDifference = newTotal - originalTotal
    },

    async handleSubmit() {
      this.errors = {}
      this.submitting = true

      try {
        // Validate form
        if (!this.validateForm()) {
          this.submitting = false
          return
        }

        // Prepare data
        const modificationData = {
          tour_schedule_id: this.form.tour_schedule_id,
          participants_count: parseInt(this.form.participants_count),
          contact_name: this.form.contact_name.trim(),
          contact_email: this.form.contact_email.trim(),
          contact_phone: this.form.contact_phone.trim(),
          special_requests: this.form.special_requests.trim() ? 
            this.form.special_requests.trim().split('\n').filter(r => r.trim()) : []
        }

        this.$emit('save', modificationData)
      } catch (error) {
        console.error('Error submitting modification:', error)
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
        } else {
          this.errors = { general: 'Error al procesar la modificación' }
        }
      } finally {
        this.submitting = false
      }
    },

    validateForm() {
      const errors = {}

      if (!this.form.tour_date) {
        errors.tour_date = 'La fecha es requerida'
      }

      if (!this.form.tour_schedule_id) {
        errors.tour_schedule_id = 'Debes seleccionar un horario'
      }

      if (!this.form.participants_count || this.form.participants_count < 1) {
        errors.participants_count = 'Debe haber al menos 1 participante'
      }

      if (!this.form.contact_name.trim()) {
        errors.contact_name = 'El nombre de contacto es requerido'
      }

      if (!this.form.contact_email.trim()) {
        errors.contact_email = 'El email de contacto es requerido'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.contact_email)) {
        errors.contact_email = 'El email no tiene un formato válido'
      }

      if (this.form.special_requests.length > 500) {
        errors.special_requests = 'Las solicitudes especiales no pueden exceder 500 caracteres'
      }

      this.errors = errors
      return Object.keys(errors).length === 0
    },

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
    }
  }
}
</script>