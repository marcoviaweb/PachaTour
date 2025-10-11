import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PaymentConfirmation from '@/Components/PaymentConfirmation.vue'

// Mock vue-router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush
  })
}))

describe('PaymentConfirmation', () => {
  let wrapper
  const mockPaymentResult = {
    payment_id: 'pay_123456789',
    status: 'completed',
    transaction_id: 'txn_987654321'
  }

  const mockBooking = {
    id: 1,
    tour_name: 'Tour La Paz - Valle de la Luna',
    booking_date: '2024-12-25',
    booking_time: '10:00',
    number_of_people: 2,
    total_amount: 150.00
  }

  beforeEach(() => {
    vi.clearAllMocks()
    
    wrapper = mount(PaymentConfirmation, {
      props: {
        paymentResult: mockPaymentResult,
        booking: mockBooking
      }
    })
  })

  it('renders confirmation modal correctly', () => {
    expect(wrapper.find('.fixed').exists()).toBe(true)
    expect(wrapper.find('h3').text()).toBe('¡Pago Exitoso!')
    expect(wrapper.text()).toContain('Tu reserva ha sido confirmada')
  })

  it('displays payment details correctly', () => {
    const paymentDetails = wrapper.find('.bg-gray-50')
    
    expect(paymentDetails.text()).toContain('pay_123456789')
    expect(paymentDetails.text()).toContain('txn_987654321')
    expect(paymentDetails.text()).toContain('Completado')
  })

  it('displays booking summary correctly', () => {
    const bookingSummary = wrapper.find('.bg-blue-50')
    
    expect(bookingSummary.text()).toContain('Tour La Paz - Valle de la Luna')
    expect(bookingSummary.text()).toContain('25 de diciembre de 2024')
    expect(bookingSummary.text()).toContain('10:00')
    expect(bookingSummary.text()).toContain('2')
    expect(bookingSummary.text()).toContain('150.00')
  })

  it('shows important information section', () => {
    const importantInfo = wrapper.find('.bg-yellow-50')
    
    expect(importantInfo.text()).toContain('Información Importante')
    expect(importantInfo.text()).toContain('Guarda este comprobante')
    expect(importantInfo.text()).toContain('Presenta tu documento de identidad')
    expect(importantInfo.text()).toContain('Llega 15 minutos antes')
    expect(importantInfo.text()).toContain('Para cancelaciones, contacta con 48 horas')
  })

  it('shows next steps section', () => {
    const nextSteps = wrapper.find('.bg-blue-50.border-blue-200')
    
    expect(nextSteps.text()).toContain('Próximos Pasos')
    expect(nextSteps.text()).toContain('Recibirás un email de confirmación')
    expect(nextSteps.text()).toContain('Puedes ver todos tus tours en "Mis Viajes"')
    expect(nextSteps.text()).toContain('El operador turístico te contactará')
  })

  it('has three action buttons', () => {
    const buttons = wrapper.findAll('button')
    expect(buttons).toHaveLength(3)
    
    expect(buttons[0].text()).toContain('Descargar Comprobante')
    expect(buttons[1].text()).toContain('Ver Mis Viajes')
    expect(buttons[2].text()).toContain('Cerrar')
  })

  it('emits close event when close button is clicked', async () => {
    const closeButton = wrapper.findAll('button')[2]
    await closeButton.trigger('click')
    
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  it('emits download-receipt event when download button is clicked', async () => {
    const downloadButton = wrapper.findAll('button')[0]
    await downloadButton.trigger('click')
    
    expect(wrapper.emitted('download-receipt')).toBeTruthy()
  })

  it('navigates to dashboard and closes modal when "Ver Mis Viajes" is clicked', async () => {
    const myTripsButton = wrapper.findAll('button')[1]
    await myTripsButton.trigger('click')
    
    expect(wrapper.emitted('close')).toBeTruthy()
    expect(mockPush).toHaveBeenCalledWith('/user/dashboard')
  })

  it('formats status text correctly', () => {
    expect(wrapper.text()).toContain('Completado')
  })

  it('formats date correctly', () => {
    expect(wrapper.text()).toContain('25 de diciembre de 2024')
  })

  it('formats price correctly', () => {
    expect(wrapper.text()).toContain('150.00')
  })

  it('shows success icon', () => {
    const successIcon = wrapper.find('.bg-green-100 svg')
    expect(successIcon.exists()).toBe(true)
  })

  it('handles payment result without transaction ID', () => {
    const paymentResultWithoutTxn = {
      payment_id: 'pay_123456789',
      status: 'completed'
      // No transaction_id
    }

    const wrapperWithoutTxn = mount(PaymentConfirmation, {
      props: {
        paymentResult: paymentResultWithoutTxn,
        booking: mockBooking
      }
    })

    // Should not show transaction ID row
    expect(wrapperWithoutTxn.text()).not.toContain('ID de Transacción')
  })

  it('displays different status correctly', () => {
    const pendingPaymentResult = {
      ...mockPaymentResult,
      status: 'pending'
    }

    const wrapperPending = mount(PaymentConfirmation, {
      props: {
        paymentResult: pendingPaymentResult,
        booking: mockBooking
      }
    })

    expect(wrapperPending.text()).toContain('Pendiente')
  })

  it('formats current date and time correctly', () => {
    // The component shows current date/time for the transaction
    const now = new Date()
    const expectedDate = now.toLocaleString('es-BO', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
    
    expect(wrapper.text()).toContain(expectedDate.split(' ')[2]) // Should contain the year
  })

  it('has proper modal styling and animations', () => {
    const modal = wrapper.find('.fixed')
    expect(modal.classes()).toContain('inset-0')
    expect(modal.classes()).toContain('bg-black')
    expect(modal.classes()).toContain('bg-opacity-50')
    expect(modal.classes()).toContain('z-50')
    
    const modalContent = wrapper.find('.bg-white')
    expect(modalContent.classes()).toContain('rounded-lg')
    expect(modalContent.classes()).toContain('shadow-xl')
  })

  it('shows proper button icons', () => {
    const buttons = wrapper.findAll('button')
    
    // Download button should have download icon
    expect(buttons[0].find('svg').exists()).toBe(true)
    
    // My trips button should have clipboard icon
    expect(buttons[1].find('svg').exists()).toBe(true)
  })

  it('handles long tour names properly', () => {
    const longTourName = 'Tour Muy Largo Con Nombre Extenso Que Podría Causar Problemas De Layout En La Interfaz'
    const bookingWithLongName = {
      ...mockBooking,
      tour_name: longTourName
    }

    const wrapperLongName = mount(PaymentConfirmation, {
      props: {
        paymentResult: mockPaymentResult,
        booking: bookingWithLongName
      }
    })

    expect(wrapperLongName.text()).toContain(longTourName)
  })
})