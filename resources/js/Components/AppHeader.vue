<template>
  <header class="bg-white shadow-lg sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo y nombre -->
        <div class="flex items-center">
          <Link href="/" class="flex items-center space-x-2">
            <img 
              src="/images/logo.svg" 
              alt="Pacha Tour" 
              class="h-8 w-8"
              @error="handleLogoError"
            >
            <span class="text-xl font-bold text-green-600">Pacha Tour</span>
          </Link>
        </div>

        <!-- Navegación principal - Desktop -->
        <div class="hidden md:flex items-center space-x-8">
          <Link 
            href="/" 
            class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="{ 'text-green-600 font-semibold': isCurrentRoute('/') }"
          >
            Inicio
          </Link>
          <Link 
            href="/departamentos" 
            class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="{ 'text-green-600 font-semibold': isCurrentRoute('/departamentos') }"
          >
            Departamentos
          </Link>
          <Link 
            href="/atractivos" 
            class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="{ 'text-green-600 font-semibold': isCurrentRoute('/atractivos') }"
          >
            Atractivos
          </Link>
          <Link 
            href="/tours" 
            class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="{ 'text-green-600 font-semibold': isCurrentRoute('/tours') }"
          >
            Tours
          </Link>
        </div>

        <!-- Autenticación y menú usuario -->
        <div class="hidden md:flex items-center space-x-4">
          <template v-if="!user">
            <button 
              @click="openAuthModal('login')"
              class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Iniciar Sesión
            </button>
            <button 
              @click="openAuthModal('register')"
              class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition-colors"
            >
              Registrarse
            </button>
          </template>
          <template v-else>
            <div class="relative" ref="userMenuRef">
              <button 
                @click="toggleUserMenu"
                class="flex items-center space-x-2 text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              >
                <span>{{ user.name }}</span>
                <svg 
                  class="w-4 h-4 transition-transform" 
                  :class="{ 'rotate-180': showUserMenu }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
              
              <!-- Dropdown menu -->
              <div 
                v-show="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border"
              >
                <Link 
                  href="/mis-viajes" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="closeUserMenu"
                >
                  Mis Viajes
                </Link>
                <Link 
                  href="/perfil" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="closeUserMenu"
                >
                  Mi Perfil
                </Link>
                <template v-if="user.role === 'admin'">
                  <hr class="my-1">
                  <Link 
                    href="/admin" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="closeUserMenu"
                  >
                    Administración
                  </Link>
                </template>
                <hr class="my-1">
                <button 
                  @click="logout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Cerrar Sesión
                </button>
              </div>
            </div>
          </template>
        </div>

        <!-- Botón menú móvil -->
        <div class="md:hidden">
          <button 
            @click="toggleMobileMenu"
            class="text-gray-700 hover:text-green-600 p-2"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path 
                v-if="!showMobileMenu"
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M4 6h16M4 12h16M4 18h16"
              ></path>
              <path 
                v-else
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Menú móvil -->
      <div v-show="showMobileMenu" class="md:hidden border-t border-gray-200">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <Link 
            href="/" 
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
            @click="closeMobileMenu"
          >
            Inicio
          </Link>
          <Link 
            href="/departamentos" 
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
            @click="closeMobileMenu"
          >
            Departamentos
          </Link>
          <Link 
            href="/atractivos" 
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
            @click="closeMobileMenu"
          >
            Atractivos
          </Link>
          <Link 
            href="/tours" 
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
            @click="closeMobileMenu"
          >
            Tours
          </Link>
          
          <div class="border-t border-gray-200 pt-4">
            <template v-if="!user">
              <button 
                @click="openAuthModal('login')"
                class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
              >
                Iniciar Sesión
              </button>
              <button 
                @click="openAuthModal('register')"
                class="block w-full text-left px-3 py-2 rounded-md text-base font-medium bg-green-600 text-white hover:bg-green-700 mt-2"
              >
                Registrarse
              </button>
            </template>
            <template v-else>
              <div class="px-3 py-2 text-sm text-gray-500">
                {{ user.name }}
              </div>
              <Link 
                href="/mis-viajes" 
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
                @click="closeMobileMenu"
              >
                Mis Viajes
              </Link>
              <Link 
                href="/perfil" 
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
                @click="closeMobileMenu"
              >
                Mi Perfil
              </Link>
              <template v-if="user.role === 'admin'">
                <Link 
                  href="/admin" 
                  class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
                  @click="closeMobileMenu"
                >
                  Administración
                </Link>
              </template>
              <button 
                @click="logout"
                class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50"
              >
                Cerrar Sesión
              </button>
            </template>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'AppHeader',
  components: {
    Link
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  },
  emits: ['open-auth-modal'],
  setup(props, { emit }) {
    const showMobileMenu = ref(false)
    const showUserMenu = ref(false)
    const userMenuRef = ref(null)

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value
    }

    const closeMobileMenu = () => {
      showMobileMenu.value = false
    }

    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value
    }

    const closeUserMenu = () => {
      showUserMenu.value = false
    }

    const openAuthModal = (mode) => {
      closeMobileMenu()
      emit('open-auth-modal', mode)
    }

    const logout = () => {
      router.post('/logout')
      closeUserMenu()
      closeMobileMenu()
    }

    const isCurrentRoute = (path) => {
      return window.location.pathname === path
    }

    const handleLogoError = (event) => {
      // Si no se puede cargar el logo, ocultar la imagen
      event.target.style.display = 'none'
    }

    // Cerrar menús al hacer click fuera
    const handleClickOutside = (event) => {
      if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        showUserMenu.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      showMobileMenu,
      showUserMenu,
      userMenuRef,
      toggleMobileMenu,
      closeMobileMenu,
      toggleUserMenu,
      closeUserMenu,
      openAuthModal,
      logout,
      isCurrentRoute,
      handleLogoError
    }
  }
}
</script>