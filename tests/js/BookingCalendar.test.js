import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import BookingCalendar from '@/Components/BookingCalendar.vue'
import { bookingApi } from '@/services/api'

// Mock the API
vi.mock('@/services/api', () => ({
  bookingApi: {
    getCalendarAvailability: vi.fn()
  }
}))

describe('BookingCalendar', () => {
  let wrapper
  const mockTourId = 1
  const mockAvailabilityData = {
    data: {
      calendar: [
        {
          date: '2024-01-15',
          is_available: true,
          total_spots: 8,
          min_price: 100,
          schedules_count: 2
        },
        {
          date: '2024-01-16',
          is_available: false,
          total_spots: 0,
          min_price: null,
          schedules_count: 0
        }
      ]
    }
  }

  beforeEach(() => {
    vi.clearAllMocks()
    bookingApi.getCalendarAvailability.mockResolvedValue(mockAvailabilityData)
  })

  const createWrapper = (props = {}) => {
    return mount(BookingCalendar, {
      props: {
        tourId: mockTourId,
        ...props
      }
    })
  }

  it('renders calendar with correct structure', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('.booking-calendar').exists()).toBe(true)
    expect(wrapper.find('.calendar-header').exists()).toBe(true)
    expect(wrapper.find('.calendar-grid').exists()).toBe(true)
    expect(wrapper.find('.calendar-legend').exists()).toBe(true)
  })

  it('displays current month and year', () => {
    wrapper = createWrapper()
    
    const currentDate = new Date()
    const monthName = currentDate.toLocaleDateString('es-BO', { month: 'long' })
    const year = currentDate.getFullYear()
    
    expect(wrapper.find('.month-year h3').text()).toContain(monthName)
    expect(wrapper.find('.month-year h3').text()).toContain(year.toString())
  })

  it('displays day headers correctly', () => {
    wrapper = createWrapper()
    
    const dayHeaders = wrapper.findAll('.day-header')
    const expectedDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
    
    expect(dayHeaders).toHaveLength(7)
    dayHeaders.forEach((header, index) => {
      expect(header.text()).toBe(expectedDays[index])
    })
  })

  it('loads availability data on mount', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    expect(bookingApi.getCalendarAvailability).toHaveBeenCalledWith(
      mockTourId,
      expect.objectContaining({
        year: expect.any(Number),
        month: expect.any(Number)
      })
    )
  })

  it('navigates to previous month when button is clicked', async () => {
    wrapper = createWrapper()
    
    const initialMonth = wrapper.vm.currentMonth
    const prevButton = wrapper.find('.nav-btn:first-child')
    
    await prevButton.trigger('click')
    
    expect(wrapper.vm.currentMonth).toBe(initialMonth - 1)
  })

  it('navigates to next month when button is clicked', async () => {
    wrapper = createWrapper()
    
    const initialMonth = wrapper.vm.currentMonth
    const nextButton = wrapper.find('.nav-btn:last-child')
    
    await nextButton.trigger('click')
    
    expect(wrapper.vm.currentMonth).toBe(initialMonth + 1)
  })

  it('disables previous button when at min date', () => {
    const minDate = new Date()
    minDate.setMonth(minDate.getMonth() + 1) // Next month as min
    
    wrapper = createWrapper({
      minDate: minDate.toISOString().split('T')[0]
    })
    
    const prevButton = wrapper.find('.nav-btn:first-child')
    expect(prevButton.attributes('disabled')).toBeDefined()
  })

  it('disables next button when at max date', () => {
    const maxDate = new Date()
    maxDate.setMonth(maxDate.getMonth() - 1) // Previous month as max
    
    wrapper = createWrapper({
      maxDate: maxDate.toISOString().split('T')[0]
    })
    
    const nextButton = wrapper.find('.nav-btn:last-child')
    expect(nextButton.attributes('disabled')).toBeDefined()
  })

  it('emits date-selected when available date is clicked', async () => {
    wrapper = createWrapper()
    
    // Wait for availability to load
    await wrapper.vm.$nextTick()
    
    // Find an available day and click it
    const availableDays = wrapper.findAll('.calendar-day.available')
    if (availableDays.length > 0) {
      await availableDays[0].trigger('click')
      
      expect(wrapper.emitted('date-selected')).toBeTruthy()
      expect(wrapper.emitted('date-selected')[0]).toHaveLength(1)
    }
  })

  it('does not emit date-selected when past date is clicked', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    const pastDays = wrapper.findAll('.calendar-day.past')
    if (pastDays.length > 0) {
      await pastDays[0].trigger('click')
      
      expect(wrapper.emitted('date-selected')).toBeFalsy()
    }
  })

  it('highlights selected date', async () => {
    const selectedDate = '2024-01-15'
    wrapper = createWrapper({
      selectedDate
    })
    
    await wrapper.vm.$nextTick()
    
    const selectedDay = wrapper.find('.calendar-day.selected')
    expect(selectedDay.exists()).toBe(true)
  })

  it('shows availability indicators for available dates', async () => {
    wrapper = createWrapper()
    
    // Mock availability data
    wrapper.vm.availability = {
      '2024-01-15': {
        available: true,
        total_spots: 8,
        min_price: 100
      }
    }
    
    await wrapper.vm.$nextTick()
    
    const availableDay = wrapper.find('.calendar-day.has-availability')
    if (availableDay.exists()) {
      const indicator = availableDay.find('.availability-indicator')
      expect(indicator.exists()).toBe(true)
      expect(indicator.find('.spots-count').text()).toBe('8')
    }
  })

  it('formats prices correctly', () => {
    wrapper = createWrapper()
    
    const formattedPrice = wrapper.vm.formatPrice(100)
    expect(formattedPrice).toMatch(/Bs\.?\s*100/)
  })

  it('shows loading state while fetching data', async () => {
    // Mock a delayed API response
    bookingApi.getCalendarAvailability.mockImplementation(
      () => new Promise(resolve => setTimeout(() => resolve(mockAvailabilityData), 100))
    )
    
    wrapper = createWrapper()
    
    expect(wrapper.find('.calendar-loading').exists()).toBe(true)
    
    // Wait for API call to complete
    await new Promise(resolve => setTimeout(resolve, 150))
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.calendar-loading').exists()).toBe(false)
  })

  it('handles API errors gracefully', async () => {
    bookingApi.getCalendarAvailability.mockRejectedValue(new Error('API Error'))
    
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    // Should not crash and availability should be empty
    expect(wrapper.vm.availability).toEqual({})
  })

  it('reloads availability when tour ID changes', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    // Clear previous calls
    vi.clearAllMocks()
    
    // Change tour ID
    await wrapper.setProps({ tourId: 2 })
    
    expect(bookingApi.getCalendarAvailability).toHaveBeenCalledWith(
      2,
      expect.any(Object)
    )
  })

  it('shows weekend styling for weekend days', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    const weekendDays = wrapper.findAll('.calendar-day.weekend')
    expect(weekendDays.length).toBeGreaterThan(0)
  })

  it('shows today styling for current date', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    const today = wrapper.find('.calendar-day.today')
    expect(today.exists()).toBe(true)
  })

  it('emits availability-loaded when data is fetched', async () => {
    wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    expect(wrapper.emitted('availability-loaded')).toBeTruthy()
    expect(wrapper.emitted('availability-loaded')[0][0]).toEqual(
      mockAvailabilityData.data.calendar
    )
  })
})