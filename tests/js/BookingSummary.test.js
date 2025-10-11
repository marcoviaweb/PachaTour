import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import BookingSummary from '@/Components/BookingSummary.vue'

describe('BookingSummary', () => {
  let wrapper
  
  const mockTour = {
    id: 1,
    name: 'Salar de Uyuni Tour',
    formatted_duration: '3 días',
    meeting_point: 'Plaza Principal, Uyuni',
    price_per_person: 150
  }
  
  const mockSchedule = {
    id: 1,
    date: '2024-01-15',
    start_time: '09:00',
    end_time: '18:00',
    guide_name: 'Carlos Mendoza'
  }
  
  const mockParticipantDetails = [
    { name: 'John Doe', age: 30 },
    { name: 'Jane Doe', age: 28 }
  ]
  
  const mockContactInfo = {
    contact_name: 'John Doe',
    contact_email: 'john@example.com',
    contact_phone: '+591 12345678',
    emergency_contact_name: 'Emergency Contact',
    emergency_contact_phone: '+591 87654321',
    special_requests: 'Vegetarian meals'
  }
  
  const mockPricing = {
    price_per_person: 150,
    participants_count: 2,
    subtotal: 300,
    total: 300,
    currency: 'BOB'
  }

  const createWrapper = (props = {}) => {
    return mount(BookingSummary, {
      props: {
        tour: mockTour,
        schedule: mockSchedule,
        participantsCount: 2,
        participantDetails: mockParticipantDetails,
        contactInfo: mockContactInfo,
        pricing: mockPricing,
        loading: false,
        ...props
      }
    })
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders all summary sections', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('.booking-summary').exists()).toBe(true)
    expect(wrapper.findAll('.summary-section')).toHaveLength(6) // Tour, Schedule, Participants, Contact, Pricing, Terms
  })

  it('displays tour information correctly', () => {
    wrapper = createWrapper()
    
    const tourSection = wrapper.findAll('.summary-section')[0]
    expect(tourSection.text()).toContain('Salar de Uyuni Tour')
    expect(tourSection.text()).toContain('3 días')
    expect(tourSection.text()).toContain('Plaza Principal, Uyuni')
  })

  it('displays schedule information correctly', () => {
    wrapper = createWrapper()
    
    const scheduleSection = wrapper.findAll('.summary-section')[1]
    expect(scheduleSection.text()).toContain('09:00')
    expect(scheduleSection.text()).toContain('18:00')
    expect(scheduleSection.text()).toContain('Carlos Mendoza')
  })

  it('displays participants information correctly', () => {
    wrapper = createWrapper()
    
    const participantsSection = wrapper.findAll('.summary-section')[2]
    expect(participantsSection.text()).toContain('2 personas')
    expect(participantsSection.text()).toContain('John Doe')
    expect(participantsSection.text()).toContain('Jane Doe')
    expect(participantsSection.text()).toContain('(30 años)')
    expect(participantsSection.text()).toContain('(28 años)')
  })

  it('displays contact information correctly', () => {
    wrapper = createWrapper()
    
    const contactSection = wrapper.findAll('.summary-section')[3]
    expect(contactSection.text()).toContain('John Doe')
    expect(contactSection.text()).toContain('john@example.com')
    expect(contactSection.text()).toContain('+591 12345678')
    expect(contactSection.text()).toContain('Emergency Contact')
    expect(contactSection.text()).toContain('Vegetarian meals')
  })

  it('displays pricing breakdown correctly', () => {
    wrapper = createWrapper()
    
    const pricingSection = wrapper.findAll('.summary-section')[4]
    expect(pricingSection.text()).toContain('Bs. 150')
    expect(pricingSection.text()).toContain('2 personas')
    expect(pricingSection.text()).toContain('Bs. 300')
  })

  it('formats prices correctly', () => {
    wrapper = createWrapper()
    
    const formattedPrice = wrapper.vm.formatPrice(150)
    expect(formattedPrice).toMatch(/Bs\.?\s*150/)
  })

  it('formats dates correctly', () => {
    wrapper = createWrapper()
    
    const formattedDate = wrapper.vm.formatDate('2024-01-15')
    expect(formattedDate).toContain('enero')
    expect(formattedDate).toContain('2024')
  })

  it('calculates total amount with service fee', () => {
    wrapper = createWrapper()
    
    // No service fee by default
    expect(wrapper.vm.totalAmount).toBe(300)
  })

  it('enables confirm button when terms are accepted', async () => {
    wrapper = createWrapper()
    
    const confirmButton = wrapper.find('.btn-confirm')
    expect(confirmButton.attributes('disabled')).toBeDefined()
    
    const termsCheckbox = wrapper.find('#accept-terms')
    await termsCheckbox.setChecked(true)
    
    expect(confirmButton.attributes('disabled')).toBeUndefined()
  })

  it('disables confirm button when loading', async () => {
    wrapper = createWrapper({ loading: true })
    
    const termsCheckbox = wrapper.find('#accept-terms')
    await termsCheckbox.setChecked(true)
    
    const confirmButton = wrapper.find('.btn-confirm')
    expect(confirmButton.attributes('disabled')).toBeDefined()
  })

  it('shows loading state in confirm button', () => {
    wrapper = createWrapper({ loading: true })
    
    const confirmButton = wrapper.find('.btn-confirm')
    expect(confirmButton.text()).toContain('Procesando...')
    expect(confirmButton.find('.btn-spinner').exists()).toBe(true)
  })

  it('emits confirm event when button is clicked', async () => {
    wrapper = createWrapper()
    
    const termsCheckbox = wrapper.find('#accept-terms')
    await termsCheckbox.setChecked(true)
    
    const confirmButton = wrapper.find('.btn-confirm')
    await confirmButton.trigger('click')
    
    expect(wrapper.emitted('confirm')).toBeTruthy()
  })

  it('emits edit-step event when edit buttons are clicked', async () => {
    wrapper = createWrapper()
    
    const editButtons = wrapper.findAll('.btn-link')
    
    // Click first edit button (tour section)
    await editButtons[0].trigger('click')
    
    expect(wrapper.emitted('edit-step')).toBeTruthy()
    expect(wrapper.emitted('edit-step')[0]).toEqual([1])
  })

  it('handles missing optional data gracefully', () => {
    wrapper = createWrapper({
      schedule: { ...mockSchedule, guide_name: null, end_time: null },
      contactInfo: {
        contact_name: 'John Doe',
        contact_email: 'john@example.com'
        // Missing optional fields
      }
    })
    
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('john@example.com')
    // Should not crash with missing optional fields
  })

  it('shows singular form for single participant', () => {
    wrapper = createWrapper({
      participantsCount: 1,
      participantDetails: [{ name: 'John Doe', age: 30 }],
      pricing: {
        ...mockPricing,
        participants_count: 1,
        subtotal: 150,
        total: 150
      }
    })
    
    expect(wrapper.text()).toContain('1 persona')
    expect(wrapper.text()).not.toContain('personas')
  })

  it('displays cancellation policy', () => {
    wrapper = createWrapper()
    
    const termsSection = wrapper.findAll('.summary-section')[5]
    expect(termsSection.text()).toContain('Política de Cancelación')
    expect(termsSection.text()).toContain('24 horas')
    expect(termsSection.text()).toContain('50% de reembolso')
    expect(termsSection.text()).toContain('sin reembolso')
  })

  it('requires terms acceptance for confirmation', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.canConfirm).toBe(false)
    
    wrapper.vm.termsAccepted = true
    expect(wrapper.vm.canConfirm).toBe(true)
  })

  it('validates required props for confirmation', () => {
    // Missing tour
    wrapper = createWrapper({ tour: null })
    wrapper.vm.termsAccepted = true
    expect(wrapper.vm.canConfirm).toBe(false)
    
    // Missing schedule
    wrapper = createWrapper({ schedule: null })
    wrapper.vm.termsAccepted = true
    expect(wrapper.vm.canConfirm).toBe(false)
    
    // Missing contact info
    wrapper = createWrapper({ contactInfo: null })
    wrapper.vm.termsAccepted = true
    expect(wrapper.vm.canConfirm).toBe(false)
  })

  it('shows confirmation note', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Se te redirigirá a la página de pago')
  })

  it('handles zero participants gracefully', () => {
    wrapper = createWrapper({
      participantsCount: 0,
      participantDetails: [],
      pricing: {
        ...mockPricing,
        participants_count: 0,
        subtotal: 0,
        total: 0
      }
    })
    
    expect(wrapper.text()).toContain('0 personas')
    expect(wrapper.vm.canConfirm).toBe(false) // Should not allow confirmation with 0 participants
  })
})