import axios from 'axios'

// Create axios instance with default config
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor to add CSRF token and handle auth
api.interceptors.request.use(
  (config) => {
    // For Inertia.js with Laravel Sanctum, we use CSRF token and session-based auth
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    // Ensure credentials are included for session-based auth
    config.withCredentials = true
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login with Inertia
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Tours API
export const toursApi = {
  // Get all tours
  getAll(params = {}) {
    return api.get('/tours', { params })
  },
  
  // Get single tour
  get(id) {
    return api.get(`/tours/${id}`)
  },
  
  // Search tours
  search(params) {
    return api.get('/tours/search', { params })
  }
}

// Booking API
export const bookingApi = {
  // Get tour for booking
  getTour(tourId) {
    return api.get(`/tours/${tourId}`)
  },
  
  // Check availability for a specific date
  checkAvailability(tourId, params) {
    return api.get(`/tours/${tourId}/availability/date`, { params })
  },
  
  // Get calendar availability for a month
  getCalendarAvailability(tourId, params) {
    return api.get(`/tours/${tourId}/availability/calendar`, { params })
  },
  
  // Check availability for date range
  checkAvailabilityRange(tourId, params) {
    return api.get(`/tours/${tourId}/availability/range`, { params })
  },
  
  // Check spots availability
  checkSpots(tourId, params) {
    return api.get(`/tours/${tourId}/availability/spots`, { params })
  },
  
  // Get next available dates
  getNextAvailable(tourId, params = {}) {
    return api.get(`/tours/${tourId}/availability/next`, { params })
  },
  
  // Get booking summary
  getBookingSummary(params) {
    return api.get('/bookings/summary', { params })
  },
  
  // Create booking
  createBooking(data) {
    return api.post('/bookings', data)
  },
  
  // Get user bookings
  getUserBookings(params = {}) {
    return api.get('/bookings', { params })
  },
  
  // Get single booking
  getBooking(id) {
    return api.get(`/bookings/${id}`)
  },
  
  // Update booking
  updateBooking(id, data) {
    return api.put(`/bookings/${id}`, data)
  },
  
  // Cancel booking
  cancelBooking(id, data) {
    return api.post(`/bookings/${id}/cancel`, data)
  },
  
  // Confirm booking
  confirmBooking(id) {
    return api.post(`/bookings/${id}/confirm`)
  },
  
  // Process payment
  processPayment(id, data) {
    return api.post(`/bookings/${id}/payment`, data)
  }
}

// Departments API
export const departmentsApi = {
  // Get all departments
  getAll() {
    return api.get('/departments')
  },
  
  // Get single department
  get(id) {
    return api.get(`/departments/${id}`)
  }
}

// Attractions API
export const attractionsApi = {
  // Get all attractions
  getAll(params = {}) {
    return api.get('/attractions', { params })
  },
  
  // Get single attraction
  get(id) {
    return api.get(`/attractions/${id}`)
  },
  
  // Search attractions
  search(params) {
    return api.get('/attractions/search', { params })
  }
}

// Search API
export const searchApi = {
  // General search
  search(params) {
    return api.get('/search', { params })
  },
  
  // Filter results
  filter(params) {
    return api.get('/search/filter', { params })
  }
}

// Auth API
export const authApi = {
  // Login
  login(credentials) {
    return api.post('/auth/login', credentials)
  },
  
  // Register
  register(userData) {
    return api.post('/auth/register', userData)
  },
  
  // Logout
  logout() {
    return api.post('/auth/logout')
  },
  
  // Get current user
  me() {
    return api.get('/auth/me')
  },
  
  // Refresh token
  refresh() {
    return api.post('/auth/refresh')
  }
}

// Reviews API
export const reviewsApi = {
  // Get reviews for attraction/tour
  getReviews(type, id, params = {}) {
    return api.get(`/${type}/${id}/reviews`, { params })
  },
  
  // Create review
  createReview(data) {
    return api.post('/reviews', data)
  },
  
  // Update review
  updateReview(id, data) {
    return api.put(`/reviews/${id}`, data)
  },
  
  // Delete review
  deleteReview(id) {
    return api.delete(`/reviews/${id}`)
  }
}

// Payments API
export const paymentsApi = {
  // Get available payment methods
  getPaymentMethods() {
    return api.get('/payments/methods')
  },
  
  // Process payment
  processPayment(data) {
    return api.post('/payments/process', data)
  },
  
  // Get payment status
  getPaymentStatus(paymentId) {
    return api.get(`/payments/${paymentId}/status`)
  },
  
  // Refund payment
  refundPayment(paymentId) {
    return api.post(`/payments/${paymentId}/refund`)
  }
}

export default api