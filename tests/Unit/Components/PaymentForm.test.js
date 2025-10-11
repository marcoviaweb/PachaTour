import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import PaymentForm from '@/Components/PaymentForm.vue'
import PaymentConfirmation from '@/Components/PaymentConfirmation.vue'
import api from '@/services/api'

// Mock the API
vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn()
  }
}))

// Mock composables
vi.mock('@/composables/useNotifications', () => ({
  useNotifications: () => ({
    showNotification: vi.fn()
  })
}))

describe('PaymentForm', () => {
  let wrapper
  const mockBooking = {
    id: 1,
    tour_name: 'Tour La Paz',
    booking_date: '2024-12-25',
    booking_time: '10:00',
    number_of_people: 2,
    total_amount: 150.00
  }

  const mockPaymentMethods = [
    {
      id: 'credit_card',
      name: 'Tarjeta de Crédito',
      description: 'Visa, MasterCard, American Express',
      enabled: true
    },
    {
      id: 'debit_card',
      name: 'Tarjeta de Débito',
      description: 'Débito bancario',
      enabled: true
    },
    {
      id: 'bank_transfer',
      name: 'Transferencia Bancaria',
      description: 'Transferencia directa',
      enabled: true
    },
    {
      id: 'qr_code',
      name: 'Código QR',
      description: 'Pago mediante QR',
      enabled: true
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    
    // Mock successful API responses
    api.get.mockResolvedValue({
      data: { payment_methods: mockPaymentMethods }
    })
    
    wrapper = mount(PaymentForm, {
      props: {
        booking: mockBooking
      },
      global: {
        stubs: {
          PaymentConfirmation: true
        }
      }
    })
  })

  it('renders payment form correctly', () => {
    expect(wrapper.find('.payment-form').exists()).toBe(true)
    expect(wrapper.find('.payment-header h3').text()).toBe('Información de Pago')
    expect(wrapper.find('.payment-summary').exists()).toBe(true)
  })

  it('displays booking summary correctly', () => {
    const summary = wrapper.find('.payment-summary')
    expect(summary.text()).toContain('Tour La Paz')
    expect(summary.text()).toContain('2')
    expect(summary.text()).toContain('150.00')
  })

  it('loads payment methods on mount', async () => {
    await nextTick()
    expect(api.get).toHaveBeenCalledWith('/payments/methods')
  })

  it('displays payment methods after loading', async () => {
    await nextTick()
    const methodCards = wrapper.findAll('.payment-method-card')
    expect(methodCards).toHaveLength(4)
    expect(wrapper.text()).toContain('Tarjeta de Crédito')
    expect(wrapper.text()).toContain('Tarjeta de Débito')
    expect(wrapper.text()).toContain('Transferencia Bancaria')
    expect(wrapper.text()).toContain('Código QR')
  })

  it('selects payment method when clicked', async () => {
    await nextTick()
    
    const creditCardMethod = wrapper.findAll('.payment-method-card')[0]
    await creditCardMethod.trigger('click')
    
    expect(creditCardMethod.classes()).toContain('border-blue-500')
    expect(wrapper.find('.card-form').exists()).toBe(true)
  })

  it('shows credit card form when credit card is selected', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    expect(wrapper.find('.card-form').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="1234 5678 9012 3456"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="Juan Pérez"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="123"]').exists()).toBe(true)
  })

  it('shows bank transfer form when bank transfer is selected', async () => {
    await nextTick()
    
    // Select bank transfer
    await wrapper.findAll('.payment-method-card')[2].trigger('click')
    
    expect(wrapper.find('.bank-form').exists()).toBe(true)
    expect(wrapper.find('select').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="1234567890"]').exists()).toBe(true)
  })

  it('shows QR form when QR code is selected', async () => {
    await nextTick()
    
    // Select QR code
    await wrapper.findAll('.payment-method-card')[3].trigger('click')
    
    expect(wrapper.find('.qr-form').exists()).toBe(true)
    expect(wrapper.text()).toContain('Escanea el código QR')
  })

  it('formats card number with spaces', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    const cardNumberInput = wrapper.find('input[placeholder="1234 5678 9012 3456"]')
    await cardNumberInput.setValue('4111111111111111')
    
    expect(cardNumberInput.element.value).toBe('4111 1111 1111 1111')
  })

  it('validates required fields for credit card', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    // Try to process payment without filling fields
    const payButton = wrapper.find('button:last-child')
    expect(payButton.attributes('disabled')).toBeDefined()
  })

  it('enables pay button when all required fields are filled', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    // Fill all required fields
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    
    const monthSelect = wrapper.findAll('select')[0]
    await monthSelect.setValue('12')
    
    const yearSelect = wrapper.findAll('select')[1]
    await yearSelect.setValue('2025')
    
    await nextTick()
    
    const payButton = wrapper.find('button:last-child')
    expect(payButton.attributes('disabled')).toBeUndefined()
  })

  it('validates card number format', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    const cardNumberInput = wrapper.find('input[placeholder="1234 5678 9012 3456"]')
    await cardNumberInput.setValue('123')
    await cardNumberInput.trigger('input')
    
    await nextTick()
    
    expect(wrapper.text()).toContain('El número de tarjeta debe tener entre 13 y 19 dígitos')
  })

  it('validates CVV format', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    const cvvInput = wrapper.find('input[placeholder="123"]')
    await cvvInput.setValue('12')
    
    await nextTick()
    
    expect(wrapper.text()).toContain('El CVV debe tener 3 o 4 dígitos')
  })

  it('validates cardholder name', async () => {
    await nextTick()
    
    // Select credit card
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    
    const nameInput = wrapper.find('input[placeholder="Juan Pérez"]')
    await nameInput.setValue('A')
    
    await nextTick()
    
    expect(wrapper.text()).toContain('El nombre debe tener al menos 2 caracteres')
  })

  it('processes payment successfully', async () => {
    const mockPaymentResult = {
      success: true,
      payment_id: 'pay_123',
      status: 'completed',
      transaction_id: 'txn_456'
    }
    
    api.post.mockResolvedValue({
      data: mockPaymentResult
    })
    
    await nextTick()
    
    // Select credit card and fill form
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    await wrapper.findAll('select')[0].setValue('12')
    await wrapper.findAll('select')[1].setValue('2025')
    
    await nextTick()
    
    // Process payment
    const payButton = wrapper.find('button:last-child')
    await payButton.trigger('click')
    
    await nextTick()
    
    expect(api.post).toHaveBeenCalledWith('/payments/process', {
      booking_id: 1,
      payment_method: 'credit_card',
      payment_data: {
        card_number: '4111111111111111',
        cardholder_name: 'Juan Pérez',
        expiry_month: '12',
        expiry_year: '2025',
        cvv: '123'
      }
    })
  })

  it('shows error message on payment failure', async () => {
    const errorMessage = 'Payment failed'
    api.post.mockRejectedValue({
      response: {
        data: { message: errorMessage }
      }
    })
    
    await nextTick()
    
    // Select credit card and fill form
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    await wrapper.findAll('select')[0].setValue('12')
    await wrapper.findAll('select')[1].setValue('2025')
    
    await nextTick()
    
    // Process payment
    const payButton = wrapper.find('button:last-child')
    await payButton.trigger('click')
    
    await nextTick()
    
    expect(wrapper.find('.error-message').exists()).toBe(true)
    expect(wrapper.text()).toContain(errorMessage)
  })

  it('shows confirmation modal on successful payment', async () => {
    const mockPaymentResult = {
      success: true,
      payment_id: 'pay_123',
      status: 'completed'
    }
    
    api.post.mockResolvedValue({
      data: mockPaymentResult
    })
    
    await nextTick()
    
    // Select and process payment
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    await wrapper.findAll('select')[0].setValue('12')
    await wrapper.findAll('select')[1].setValue('2025')
    
    await nextTick()
    
    const payButton = wrapper.find('button:last-child')
    await payButton.trigger('click')
    
    await nextTick()
    
    expect(wrapper.findComponent(PaymentConfirmation).exists()).toBe(true)
  })

  it('emits back event when back button is clicked', async () => {
    const backButton = wrapper.find('button:first-child')
    await backButton.trigger('click')
    
    expect(wrapper.emitted('back')).toBeTruthy()
  })

  it('emits payment-success event on successful payment', async () => {
    const mockPaymentResult = {
      success: true,
      payment_id: 'pay_123',
      status: 'completed'
    }
    
    api.post.mockResolvedValue({
      data: mockPaymentResult
    })
    
    await nextTick()
    
    // Process payment
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    await wrapper.findAll('select')[0].setValue('12')
    await wrapper.findAll('select')[1].setValue('2025')
    
    await nextTick()
    
    const payButton = wrapper.find('button:last-child')
    await payButton.trigger('click')
    
    await nextTick()
    
    expect(wrapper.emitted('payment-success')).toBeTruthy()
    expect(wrapper.emitted('payment-success')[0][0]).toEqual(mockPaymentResult)
  })

  it('shows loading state during payment processing', async () => {
    // Mock a delayed response
    api.post.mockImplementation(() => new Promise(resolve => {
      setTimeout(() => resolve({ data: { success: true } }), 100)
    }))
    
    await nextTick()
    
    // Fill form and click pay
    await wrapper.findAll('.payment-method-card')[0].trigger('click')
    await wrapper.find('input[placeholder="1234 5678 9012 3456"]').setValue('4111111111111111')
    await wrapper.find('input[placeholder="Juan Pérez"]').setValue('Juan Pérez')
    await wrapper.find('input[placeholder="123"]').setValue('123')
    await wrapper.findAll('select')[0].setValue('12')
    await wrapper.findAll('select')[1].setValue('2025')
    
    await nextTick()
    
    const payButton = wrapper.find('button:last-child')
    await payButton.trigger('click')
    
    // Should show loading state
    expect(payButton.text()).toContain('Procesando...')
    expect(payButton.attributes('disabled')).toBeDefined()
  })

  it('validates bank transfer fields', async () => {
    await nextTick()
    
    // Select bank transfer
    await wrapper.findAll('.payment-method-card')[2].trigger('click')
    
    // Try to process without filling fields
    const payButton = wrapper.find('button:last-child')
    expect(payButton.attributes('disabled')).toBeDefined()
    
    // Fill bank code only
    await wrapper.find('select').setValue('BNB')
    await nextTick()
    
    // Still disabled without account number
    expect(payButton.attributes('disabled')).toBeDefined()
    
    // Fill account number
    await wrapper.find('input[placeholder="1234567890"]').setValue('1234567890')
    await nextTick()
    
    // Now should be enabled
    expect(payButton.attributes('disabled')).toBeUndefined()
  })

  it('formats price correctly', () => {
    expect(wrapper.text()).toContain('150.00')
  })

  it('formats date correctly', () => {
    expect(wrapper.text()).toContain('diciembre')
  })
})