<template>
  <footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Información de la empresa -->
        <div class="col-span-1 lg:col-span-2">
          <div class="flex items-center space-x-2 mb-4">
            <img 
              src="/images/logo-white.svg" 
              alt="Pacha Tour" 
              class="h-8 w-8"
              @error="handleLogoError"
            >
            <span class="text-xl font-bold text-green-400">Pacha Tour</span>
          </div>
          <p class="text-gray-300 mb-4 max-w-md">
            Descubre la riqueza cultural, natural e histórica de Bolivia. 
            Tu puerta de entrada a experiencias turísticas únicas en los nueve departamentos bolivianos.
          </p>
          <div class="flex space-x-4">
            <a 
              href="#" 
              class="text-gray-400 hover:text-green-400 transition-colors"
              aria-label="Facebook"
            >
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a 
              href="#" 
              class="text-gray-400 hover:text-green-400 transition-colors"
              aria-label="Instagram"
            >
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987c6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297L3.89 16.927c-.854-.854-1.297-2.025-1.297-3.323s.443-2.469 1.297-3.323l1.236-1.236c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297l1.236 1.236c.854.854 1.297 2.025 1.297 3.323s-.443 2.469-1.297 3.323l-1.236 1.236c-.875.807-2.026 1.297-3.323 1.297z"/>
              </svg>
            </a>
            <a 
              href="#" 
              class="text-gray-400 hover:text-green-400 transition-colors"
              aria-label="Twitter"
            >
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Enlaces rápidos -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Enlaces Rápidos</h3>
          <ul class="space-y-2">
            <li>
              <Link 
                href="/" 
                class="text-gray-300 hover:text-green-400 transition-colors"
              >
                Inicio
              </Link>
            </li>
            <li>
              <Link 
                href="/departamentos" 
                class="text-gray-300 hover:text-green-400 transition-colors"
              >
                Departamentos
              </Link>
            </li>
            <li>
              <Link 
                href="/atractivos" 
                class="text-gray-300 hover:text-green-400 transition-colors"
              >
                Atractivos
              </Link>
            </li>
            <li>
              <Link 
                href="/tours" 
                class="text-gray-300 hover:text-green-400 transition-colors"
              >
                Tours
              </Link>
            </li>
            <li>
              <Link 
                href="/recursos" 
                class="text-gray-300 hover:text-green-400 transition-colors"
              >
                Recursos para Viajeros
              </Link>
            </li>
          </ul>
        </div>

        <!-- Información de contacto -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Contacto</h3>
          <ul class="space-y-2 text-gray-300">
            <li class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              <a href="mailto:info@pachatour.bo" class="hover:text-green-400 transition-colors">
                info@pachatour.bo
              </a>
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
              <a href="tel:+59122345678" class="hover:text-green-400 transition-colors">
                +591 2 234-5678
              </a>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <span>La Paz, Bolivia</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Línea divisoria -->
      <div class="border-t border-gray-700 mt-8 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="text-gray-400 text-sm mb-4 md:mb-0">
            © {{ currentYear }} Pacha Tour. Todos los derechos reservados.
          </div>
          <div class="flex space-x-6 text-sm">
            <Link 
              href="/privacidad" 
              class="text-gray-400 hover:text-green-400 transition-colors"
            >
              Política de Privacidad
            </Link>
            <Link 
              href="/terminos" 
              class="text-gray-400 hover:text-green-400 transition-colors"
            >
              Términos de Uso
            </Link>
            <Link 
              href="/cookies" 
              class="text-gray-400 hover:text-green-400 transition-colors"
            >
              Política de Cookies
            </Link>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

export default {
  name: 'AppFooter',
  components: {
    Link
  },
  setup() {
    const currentYear = computed(() => new Date().getFullYear())

    const handleLogoError = (event) => {
      // Si no se puede cargar el logo blanco, ocultar la imagen
      event.target.style.display = 'none'
    }

    return {
      currentYear,
      handleLogoError
    }
  }
}
</script>