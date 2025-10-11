import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import api from '@/services/api'

// Global auth state
const loading = ref(false)

export function useAuth() {
  const page = usePage()
  
  // Get user from Inertia page props
  const user = computed(() => page.props.auth?.user || null)
  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isTourist = computed(() => user.value?.role === 'tourist')
  
  const login = async (credentials) => {
    try {
      loading.value = true
      const response = await api.post('/login', credentials)
      
      // Inertia will handle the redirect and update the page props
      return { success: true, user: response.data.user }
    } catch (error) {
      console.error('Login error:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || 'Error al iniciar sesión' 
      }
    } finally {
      loading.value = false
    }
  }
  
  const register = async (userData) => {
    try {
      loading.value = true
      const response = await api.post('/register', userData)
      
      // Inertia will handle the redirect and update the page props
      return { success: true, user: response.data.user }
    } catch (error) {
      console.error('Register error:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || 'Error al registrarse' 
      }
    } finally {
      loading.value = false
    }
  }
  
  const logout = async () => {
    try {
      await api.post('/logout')
      // Inertia will handle the redirect and update the page props
    } catch (error) {
      console.error('Logout error:', error)
    }
  }
  
  // No need for these methods with Inertia.js
  // The user state is managed by the server and passed via page props
  
  return {
    // State
    user,
    loading,
    
    // Computed
    isAuthenticated,
    isAdmin,
    isTourist,
    
    // Methods
    login,
    register,
    logout
  }
}