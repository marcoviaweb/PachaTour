<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <AppHeader 
      :user="$page.props.auth?.user || null" 
      @open-auth-modal="handleOpenAuthModal"
    />

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <AppFooter />

    <!-- Auth Modal -->
    <AuthModal 
      v-if="showAuthModal"
      :mode="authModalMode"
      @close="closeAuthModal"
      @switch-mode="switchAuthMode"
    />

    <!-- Notification Toast -->
    <NotificationToast 
      v-if="notification.show"
      :type="notification.type"
      :message="notification.message"
      @close="closeNotification"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import AuthModal from '@/Components/AuthModal.vue'
import NotificationToast from '@/Components/NotificationToast.vue'

export default {
  name: 'AppLayout',
  components: {
    AppHeader,
    AppFooter,
    AuthModal,
    NotificationToast
  },
  setup() {
    const page = usePage()
    const showAuthModal = ref(false)
    const authModalMode = ref('login') // 'login' or 'register'
    
    const notification = ref({
      show: false,
      type: 'success', // 'success', 'error', 'warning', 'info'
      message: ''
    })

    const handleOpenAuthModal = (mode = 'login') => {
      authModalMode.value = mode
      showAuthModal.value = true
    }

    const closeAuthModal = () => {
      showAuthModal.value = false
    }

    const switchAuthMode = (mode) => {
      authModalMode.value = mode
    }

    const showNotification = (type, message) => {
      notification.value = {
        show: true,
        type,
        message
      }
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        closeNotification()
      }, 5000)
    }

    const closeNotification = () => {
      notification.value.show = false
    }

    // Listen for flash messages from Laravel
    onMounted(() => {
      const flash = page.props.flash
      if (flash) {
        if (flash.success) {
          showNotification('success', flash.success)
        } else if (flash.error) {
          showNotification('error', flash.error)
        } else if (flash.warning) {
          showNotification('warning', flash.warning)
        } else if (flash.info) {
          showNotification('info', flash.info)
        }
      }
    })

    return {
      showAuthModal,
      authModalMode,
      notification,
      handleOpenAuthModal,
      closeAuthModal,
      switchAuthMode,
      showNotification,
      closeNotification
    }
  }
}
</script>

<style scoped>
/* Estilos específicos del layout si son necesarios */
</style>