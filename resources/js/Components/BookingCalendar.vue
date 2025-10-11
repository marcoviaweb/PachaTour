<template>
  <div class="booking-calendar">
    <!-- Calendar Header -->
    <div class="calendar-header">
      <button 
        type="button" 
        class="nav-btn"
        :disabled="!canGoPrevious"
        @click="previousMonth"
      >
        &#8249;
      </button>
      
      <div class="month-year">
        <h3>{{ currentMonthName }} {{ currentYear }}</h3>
      </div>
      
      <button 
        type="button" 
        class="nav-btn"
        :disabled="!canGoNext"
        @click="nextMonth"
      >
        &#8250;
      </button>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-grid">
      <!-- Day Headers -->
      <div class="day-header" v-for="day in dayHeaders" :key="day">
        {{ day }}
      </div>
      
      <!-- Calendar Days -->
      <div
        v-for="day in calendarDays"
        :key="`${day.date}-${day.month}`"
        class="calendar-day"
        :class="{
          'other-month': day.isOtherMonth,
          'past': day.isPast,
          'today': day.isToday,
          'available': day.isAvailable,
          'selected': day.isSelected,
          'weekend': day.isWeekend,
          'has-availability': day.hasAvailability
        }"
        @click="selectDate(day)"
      >
        <span class="day-number">{{ day.day }}</span>
        <div v-if="day.availability" class="availability-indicator">
          <span class="spots-count">{{ day.availability.total_spots }}</span>
          <span class="price-range" v-if="day.availability.min_price">
            {{ formatPrice(day.availability.min_price) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="calendar-legend">
      <div class="legend-item">
        <div class="legend-color available"></div>
        <span>Disponible</span>
      </div>
      <div class="legend-item">
        <div class="legend-color selected"></div>
        <span>Seleccionado</span>
      </div>
      <div class="legend-item">
        <div class="legend-color past"></div>
        <span>No disponible</span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="calendar-loading">
      <div class="spinner"></div>
      <p>Cargando disponibilidad...</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { bookingApi } from '@/services/api'

export default {
  name: 'BookingCalendar',
  props: {
    tourId: {
      type: [String, Number],
      required: true
    },
    selectedDate: {
      type: String,
      default: null
    },
    minDate: {
      type: String,
      default: () => new Date().toISOString().split('T')[0]
    },
    maxDate: {
      type: String,
      default: () => {
        const maxDate = new Date()
        maxDate.setMonth(maxDate.getMonth() + 6)
        return maxDate.toISOString().split('T')[0]
      }
    }
  },
  emits: ['date-selected', 'availability-loaded'],
  setup(props, { emit }) {
    // State
    const loading = ref(false)
    const currentDate = ref(new Date())
    const availability = ref({})
    
    // Set initial date to today or minDate if today is in the past
    const today = new Date()
    const minDateObj = new Date(props.minDate)
    if (today < minDateObj) {
      currentDate.value = new Date(minDateObj)
    }

    // Computed properties
    const currentYear = computed(() => currentDate.value.getFullYear())
    const currentMonth = computed(() => currentDate.value.getMonth())
    
    const currentMonthName = computed(() => {
      return currentDate.value.toLocaleDateString('es-BO', { month: 'long' })
    })

    const dayHeaders = computed(() => {
      return ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
    })

    const canGoPrevious = computed(() => {
      const firstDayOfMonth = new Date(currentYear.value, currentMonth.value, 1)
      const minDateObj = new Date(props.minDate)
      return firstDayOfMonth >= minDateObj
    })

    const canGoNext = computed(() => {
      const firstDayOfNextMonth = new Date(currentYear.value, currentMonth.value + 1, 1)
      const maxDateObj = new Date(props.maxDate)
      return firstDayOfNextMonth <= maxDateObj
    })

    const calendarDays = computed(() => {
      const year = currentYear.value
      const month = currentMonth.value
      const today = new Date()
      const minDateObj = new Date(props.minDate)
      const maxDateObj = new Date(props.maxDate)
      
      // First day of the month and how many days in the month
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const daysInMonth = lastDay.getDate()
      
      // Start from Sunday of the week containing the first day
      const startDate = new Date(firstDay)
      startDate.setDate(startDate.getDate() - firstDay.getDay())
      
      const days = []
      const current = new Date(startDate)
      
      // Generate 6 weeks (42 days) to fill the calendar grid
      for (let i = 0; i < 42; i++) {
        const dateStr = current.toISOString().split('T')[0]
        const dayAvailability = availability.value[dateStr]
        
        const day = {
          date: dateStr,
          day: current.getDate(),
          month: current.getMonth(),
          year: current.getFullYear(),
          isOtherMonth: current.getMonth() !== month,
          isPast: current < today.setHours(0, 0, 0, 0) || current < minDateObj || current > maxDateObj,
          isToday: current.toDateString() === today.toDateString(),
          isWeekend: current.getDay() === 0 || current.getDay() === 6,
          isSelected: dateStr === props.selectedDate,
          hasAvailability: dayAvailability?.available || false,
          isAvailable: dayAvailability?.available && current >= minDateObj && current <= maxDateObj,
          availability: dayAvailability
        }
        
        days.push(day)
        current.setDate(current.getDate() + 1)
      }
      
      return days
    })

    // Methods
    const loadAvailability = async () => {
      try {
        loading.value = true
        
        const response = await bookingApi.getCalendarAvailability(props.tourId, {
          year: currentYear.value,
          month: currentMonth.value + 1 // API expects 1-based month
        })
        
        // Convert array to object for easier lookup
        const availabilityMap = {}
        response.data.calendar.forEach(day => {
          availabilityMap[day.date] = {
            available: day.is_available,
            total_spots: day.total_spots,
            min_price: day.min_price,
            schedules_count: day.schedules_count
          }
        })
        
        availability.value = availabilityMap
        
        // Emit availability data for parent component
        emit('availability-loaded', response.data.calendar)
        
      } catch (error) {
        console.error('Error loading availability:', error)
        availability.value = {}
      } finally {
        loading.value = false
      }
    }

    const selectDate = (day) => {
      // Don't allow selection of past dates, other months, or unavailable dates
      if (day.isPast || day.isOtherMonth || !day.isAvailable) {
        return
      }
      
      emit('date-selected', day.date)
    }

    const previousMonth = () => {
      if (canGoPrevious.value) {
        currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1)
      }
    }

    const nextMonth = () => {
      if (canGoNext.value) {
        currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1)
      }
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0
      }).format(price)
    }

    // Watchers
    watch([currentYear, currentMonth], loadAvailability)
    
    watch(() => props.tourId, () => {
      availability.value = {}
      loadAvailability()
    })

    // Lifecycle
    onMounted(() => {
      loadAvailability()
    })

    return {
      // State
      loading,
      currentDate,
      availability,
      
      // Computed
      currentYear,
      currentMonth,
      currentMonthName,
      dayHeaders,
      canGoPrevious,
      canGoNext,
      calendarDays,
      
      // Methods
      selectDate,
      previousMonth,
      nextMonth,
      formatPrice
    }
  }
}
</script>

<style scoped>
.booking-calendar {
  background: white;
  border-radius: 8px;
  border: 1px solid #e9ecef;
  overflow: hidden;
  position: relative;
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}

.nav-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.25rem;
  color: #495057;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-btn:hover:not(:disabled) {
  background: #007bff;
  color: white;
  transform: scale(1.05);
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.month-year h3 {
  margin: 0;
  color: #2c3e50;
  text-transform: capitalize;
  font-size: 1.25rem;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.day-header {
  padding: 0.75rem 0.5rem;
  text-align: center;
  font-weight: 600;
  color: #6c757d;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  font-size: 0.875rem;
}

.calendar-day {
  min-height: 80px;
  padding: 0.5rem;
  border-right: 1px solid #f1f3f4;
  border-bottom: 1px solid #f1f3f4;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  position: relative;
}

.calendar-day:hover:not(.past):not(.other-month) {
  background: #f8f9ff;
}

.calendar-day.other-month {
  color: #ced4da;
  background: #fafafa;
  cursor: not-allowed;
}

.calendar-day.past {
  color: #ced4da;
  background: #f8f9fa;
  cursor: not-allowed;
}

.calendar-day.today {
  background: #fff3cd;
  border-color: #ffc107;
}

.calendar-day.available {
  background: #d4edda;
  border-color: #28a745;
}

.calendar-day.available:hover {
  background: #c3e6cb;
  transform: scale(1.02);
}

.calendar-day.selected {
  background: #cce7ff;
  border-color: #007bff;
  box-shadow: inset 0 0 0 2px #007bff;
}

.calendar-day.weekend:not(.past):not(.other-month) {
  background: #f0f8ff;
}

.day-number {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.calendar-day.past .day-number,
.calendar-day.other-month .day-number {
  color: #ced4da;
}

.availability-indicator {
  margin-top: auto;
  font-size: 0.75rem;
}

.spots-count {
  display: block;
  color: #28a745;
  font-weight: 600;
  margin-bottom: 0.125rem;
}

.price-range {
  display: block;
  color: #6c757d;
  font-weight: 500;
}

.calendar-legend {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #495057;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1px solid #dee2e6;
}

.legend-color.available {
  background: #d4edda;
  border-color: #28a745;
}

.legend-color.selected {
  background: #cce7ff;
  border-color: #007bff;
}

.legend-color.past {
  background: #f8f9fa;
  border-color: #ced4da;
}

.calendar-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .calendar-day {
    min-height: 60px;
    padding: 0.25rem;
  }
  
  .day-number {
    font-size: 0.875rem;
  }
  
  .availability-indicator {
    font-size: 0.625rem;
  }
  
  .calendar-legend {
    flex-wrap: wrap;
    gap: 1rem;
  }
  
  .legend-item {
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .calendar-header {
    padding: 0.75rem;
  }
  
  .month-year h3 {
    font-size: 1rem;
  }
  
  .nav-btn {
    width: 32px;
    height: 32px;
    font-size: 1rem;
  }
  
  .calendar-day {
    min-height: 50px;
  }
  
  .day-header {
    padding: 0.5rem 0.25rem;
    font-size: 0.75rem;
  }
}
</style>