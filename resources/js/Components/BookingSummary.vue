<template>
  <div class="booking-summary">
    <!-- Tour Summary -->
    <div class="summary-section">
      <h5>Detalles del Tour</h5>
      <div class="tour-summary">
        <div class="tour-info">
          <h6>{{ tour?.name }}</h6>
          <p class="tour-meta">
            <span class="duration">{{ tour?.formatted_duration }}</span>
            <span class="separator">•</span>
            <span class="meeting-point">{{ tour?.meeting_point }}</span>
          </p>
        </div>
        <div class="edit-link">
          <button type="button" class="btn-link" @click="$emit('edit-step', 1)">
            Cambiar
          </button>
        </div>
      </div>
    </div>

    <!-- Schedule Summary -->
    <div class="summary-section" v-if="schedule">
      <h5>Fecha y Horario</h5>
      <div class="schedule-summary">
        <div class="schedule-info">
          <p class="date">{{ formatDate(schedule.date) }}</p>
          <p class="time">
            {{ schedule.start_time }}
            <span v-if="schedule.end_time"> - {{ schedule.end_time }}</span>
          </p>
          <p v-if="schedule.guide_name" class="guide">
            Guía: {{ schedule.guide_name }}
          </p>
        </div>
        <div class="edit-link">
          <button type="button" class="btn-link" @click="$emit('edit-step', 1)">
            Cambiar
          </button>
        </div>
      </div>
    </div>

    <!-- Participants Summary -->
    <div class="summary-section">
      <h5>Participantes</h5>
      <div class="participants-summary">
        <div class="participants-info">
          <p class="count">{{ participantsCount }} {{ participantsCount === 1 ? 'persona' : 'personas' }}</p>
          <div v-if="participantDetails?.length > 0" class="participants-list">
            <div 
              v-for="(participant, index) in participantDetails" 
              :key="index"
              class="participant-item"
            >
              <span class="participant-name">{{ participant.name }}</span>
              <span v-if="participant.age" class="participant-age">({{ participant.age }} años)</span>
            </div>
          </div>
        </div>
        <div class="edit-link">
          <button type="button" class="btn-link" @click="$emit('edit-step', 2)">
            Cambiar
          </button>
        </div>
      </div>
    </div>

    <!-- Contact Summary -->
    <div class="summary-section" v-if="contactInfo">
      <h5>Información de Contacto</h5>
      <div class="contact-summary">
        <div class="contact-info">
          <p class="contact-name">{{ contactInfo.contact_name }}</p>
          <p class="contact-email">{{ contactInfo.contact_email }}</p>
          <p v-if="contactInfo.contact_phone" class="contact-phone">
            {{ contactInfo.contact_phone }}
          </p>
          <p v-if="contactInfo.emergency_contact_name" class="emergency-contact">
            Emergencia: {{ contactInfo.emergency_contact_name }}
            <span v-if="contactInfo.emergency_contact_phone">
              ({{ contactInfo.emergency_contact_phone }})
            </span>
          </p>
          <p v-if="contactInfo.special_requests" class="special-requests">
            <strong>Solicitudes especiales:</strong> {{ contactInfo.special_requests }}
          </p>
        </div>
        <div class="edit-link">
          <button type="button" class="btn-link" @click="$emit('edit-step', 3)">
            Cambiar
          </button>
        </div>
      </div>
    </div>

    <!-- Pricing Summary -->
    <div class="summary-section pricing-section" v-if="pricing">
      <h5>Resumen de Precios</h5>
      <div class="pricing-summary">
        <div class="pricing-breakdown">
          <div class="price-line">
            <span class="price-label">
              {{ formatPrice(pricing.price_per_person) }} × {{ pricing.participants_count }} 
              {{ pricing.participants_count === 1 ? 'persona' : 'personas' }}
            </span>
            <span class="price-amount">{{ formatPrice(pricing.subtotal) }}</span>
          </div>
          
          <!-- Additional fees could go here -->
          <div v-if="serviceFee > 0" class="price-line">
            <span class="price-label">Tarifa de servicio</span>
            <span class="price-amount">{{ formatPrice(serviceFee) }}</span>
          </div>
          
          <div class="price-line total-line">
            <span class="price-label total-label">Total</span>
            <span class="price-amount total-amount">{{ formatPrice(totalAmount) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="summary-section terms-section">
      <div class="terms-checkbox">
        <input
          id="accept-terms"
          v-model="termsAccepted"
          type="checkbox"
          class="checkbox-input"
        />
        <label for="accept-terms" class="checkbox-label">
          Acepto los 
          <a href="/terms" target="_blank" class="terms-link">términos y condiciones</a>
          y la 
          <a href="/privacy" target="_blank" class="terms-link">política de privacidad</a>
        </label>
      </div>
      
      <div class="cancellation-policy">
        <h6>Política de Cancelación</h6>
        <ul>
          <li>Cancelación gratuita hasta 24 horas antes del tour</li>
          <li>Cancelaciones con menos de 24 horas: 50% de reembolso</li>
          <li>No show: sin reembolso</li>
        </ul>
      </div>
    </div>

    <!-- Confirmation Button -->
    <div class="confirmation-section">
      <button
        type="button"
        class="btn btn-confirm"
        :disabled="!canConfirm || loading"
        @click="$emit('confirm')"
      >
        <span v-if="loading">
          <div class="btn-spinner"></div>
          Procesando...
        </span>
        <span v-else>
          Confirmar Reserva - {{ formatPrice(totalAmount) }}
        </span>
      </button>
      
      <p class="confirmation-note">
        Se te redirigirá a la página de pago después de confirmar
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'BookingSummary',
  props: {
    tour: {
      type: Object,
      default: null
    },
    schedule: {
      type: Object,
      default: null
    },
    participantsCount: {
      type: Number,
      required: true
    },
    participantDetails: {
      type: Array,
      default: () => []
    },
    contactInfo: {
      type: Object,
      default: null
    },
    pricing: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['confirm', 'edit-step'],
  setup(props) {
    // State
    const termsAccepted = ref(false)

    // Computed properties
    const serviceFee = computed(() => {
      // Could be calculated based on pricing or be a fixed amount
      return 0 // No service fee for now
    })

    const totalAmount = computed(() => {
      if (!props.pricing) return 0
      return props.pricing.total + serviceFee.value
    })

    const canConfirm = computed(() => {
      return termsAccepted.value && 
             props.tour && 
             props.schedule && 
             props.participantsCount > 0 && 
             props.contactInfo?.contact_name && 
             props.contactInfo?.contact_email &&
             props.pricing &&
             !props.loading
    })

    // Methods
    const formatPrice = (price) => {
      if (typeof price !== 'number') return 'Bs. 0'
      
      return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0
      }).format(price)
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      return date.toLocaleDateString('es-BO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    return {
      // State
      termsAccepted,
      
      // Computed
      serviceFee,
      totalAmount,
      canConfirm,
      
      // Methods
      formatPrice,
      formatDate
    }
  }
}
</script>

<style scoped>
.booking-summary {
  background: white;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.summary-section {
  padding: 1.5rem;
  border-bottom: 1px solid #f1f3f4;
}

.summary-section:last-child {
  border-bottom: none;
}

.summary-section h5 {
  margin: 0 0 1rem 0;
  color: #2c3e50;
  font-size: 1.125rem;
  font-weight: 600;
}

.summary-section h6 {
  margin: 0 0 0.5rem 0;
  color: #495057;
  font-size: 1rem;
  font-weight: 600;
}

.tour-summary,
.schedule-summary,
.participants-summary,
.contact-summary {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.tour-info h6 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.tour-meta {
  color: #6c757d;
  font-size: 0.875rem;
  margin: 0;
}

.separator {
  margin: 0 0.5rem;
}

.schedule-info .date {
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
  text-transform: capitalize;
}

.schedule-info .time {
  color: #495057;
  font-size: 1rem;
  margin: 0 0 0.25rem 0;
}

.schedule-info .guide {
  color: #6c757d;
  font-size: 0.875rem;
  margin: 0;
}

.participants-info .count {
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
}

.participants-list {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.participant-item {
  font-size: 0.875rem;
  color: #495057;
}

.participant-name {
  font-weight: 500;
}

.participant-age {
  color: #6c757d;
  margin-left: 0.5rem;
}

.contact-info p {
  margin: 0 0 0.25rem 0;
  color: #495057;
}

.contact-name {
  font-weight: 600;
  color: #2c3e50 !important;
}

.contact-email {
  color: #007bff !important;
}

.emergency-contact,
.special-requests {
  font-size: 0.875rem;
  color: #6c757d !important;
}

.special-requests {
  margin-top: 0.5rem !important;
}

.edit-link {
  flex-shrink: 0;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  text-decoration: underline;
  cursor: pointer;
  font-size: 0.875rem;
  padding: 0;
}

.btn-link:hover {
  color: #0056b3;
}

.pricing-section {
  background: #f8f9fa;
}

.pricing-breakdown {
  width: 100%;
}

.price-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.price-line:last-child {
  margin-bottom: 0;
}

.price-label {
  color: #495057;
  font-size: 0.875rem;
}

.price-amount {
  font-weight: 600;
  color: #2c3e50;
}

.total-line {
  border-top: 1px solid #dee2e6;
  padding-top: 0.75rem;
  margin-top: 0.75rem;
  margin-bottom: 0;
}

.total-label {
  font-weight: 600;
  font-size: 1rem;
  color: #2c3e50;
}

.total-amount {
  font-size: 1.25rem;
  color: #28a745;
}

.terms-section {
  background: #f8f9fa;
}

.terms-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.checkbox-input {
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.checkbox-label {
  font-size: 0.875rem;
  color: #495057;
  line-height: 1.4;
}

.terms-link {
  color: #007bff;
  text-decoration: none;
}

.terms-link:hover {
  text-decoration: underline;
}

.cancellation-policy h6 {
  color: #495057;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.cancellation-policy ul {
  margin: 0;
  padding-left: 1.25rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.cancellation-policy li {
  margin-bottom: 0.25rem;
}

.confirmation-section {
  padding: 1.5rem;
  text-align: center;
  background: #f8f9fa;
}

.btn-confirm {
  width: 100%;
  padding: 1rem 2rem;
  background: #28a745;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-confirm:hover:not(:disabled) {
  background: #1e7e34;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-confirm:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.confirmation-note {
  margin: 1rem 0 0 0;
  color: #6c757d;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .summary-section {
    padding: 1rem;
  }
  
  .tour-summary,
  .schedule-summary,
  .participants-summary,
  .contact-summary {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .edit-link {
    align-self: flex-start;
  }
  
  .btn-confirm {
    font-size: 1rem;
    padding: 0.875rem 1.5rem;
  }
}
</style>