<template>
  <Head title="Pacha Tour - Descubre Bolivia" />
  
  <AppLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-green-600 to-blue-600 text-white">
      <div class="absolute inset-0 bg-black opacity-40"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Descubre la Magia de Bolivia
          </h1>
          <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            Explora los nueve departamentos bolivianos y vive experiencias turísticas únicas 
            en el corazón de Sudamérica
          </p>
          
          <!-- Barra de búsqueda -->
          <div class="mb-8 w-full flex justify-center">
            <div class="w-full max-w-2xl">
              <SearchBar
                placeholder="Buscar atractivos, departamentos o tours..."
                @search="handleSearch"
                @select="handleSearchSelect"
              />
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link 
              href="/departamentos"
              class="bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
            >
              Explorar Departamentos
            </Link>
            <Link 
              href="/atractivos"
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-600 transition-colors"
            >
              Ver Atractivos
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Departamentos Grid -->
    <section class="py-16">
      <DepartmentGrid />
    </section>

    <!-- Atractivos Destacados -->
    <section class="py-16 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Atractivos Destacados
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Descubre algunos de los destinos más populares y fascinantes que Bolivia tiene para ofrecer
          </p>
        </div>

        <!-- Loading state para atractivos -->
        <div v-if="loadingAttractions" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
        </div>

        <!-- Grid de atractivos destacados -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <AttractionCard
            v-for="attraction in featuredAttractions"
            :key="attraction.id"
            :attraction="attraction"
            @view-details="handleViewAttraction"
            @book-tour="handleBookTour"
          />
        </div>

        <!-- Mensaje cuando no hay atractivos -->
        <div v-if="!loadingAttractions && featuredAttractions.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Próximamente</h3>
          <p class="text-gray-600">Los atractivos destacados se cargarán pronto.</p>
        </div>

        <!-- Ver todos los atractivos -->
        <div class="text-center mt-12">
          <Link 
            href="/atractivos"
            class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors inline-flex items-center"
          >
            Ver Todos los Atractivos
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
            </svg>
          </Link>
        </div>
      </div>
    </section>

    <!-- Características de la plataforma -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            ¿Por qué elegir Pacha Tour?
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Tu compañero perfecto para descubrir y planificar tu aventura boliviana
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Explora 9 Departamentos</h3>
            <p class="text-gray-600">Descubre la diversidad cultural y natural de todos los departamentos bolivianos en un solo lugar.</p>
          </div>

          <div class="text-center">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Reservas Fáciles</h3>
            <p class="text-gray-600">Programa y reserva tus tours favoritos con unos pocos clics. Proceso simple y seguro.</p>
          </div>

          <div class="text-center">
            <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Experiencias Auténticas</h3>
            <p class="text-gray-600">Conectamos con operadores locales para ofrecerte experiencias genuinas y memorables.</p>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import DepartmentGrid from '@/Components/DepartmentGrid.vue'
import AttractionCard from '@/Components/AttractionCard.vue'
import SearchBar from '@/Components/SearchBar.vue'
import axios from 'axios'

export default {
  name: 'Welcome',
  components: {
    Head,
    Link,
    AppLayout,
    DepartmentGrid,
    AttractionCard,
    SearchBar
  },
  props: {
    canLogin: Boolean,
    canRegister: Boolean,
  },
  setup() {
    const featuredAttractions = ref([])
    const loadingAttractions = ref(false)

    const fetchFeaturedAttractions = async () => {
      loadingAttractions.value = true
      try {
        const response = await axios.get('/api/attractions/featured?limit=6')
        featuredAttractions.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching featured attractions:', error)
        // En caso de error, usar datos de ejemplo
        featuredAttractions.value = []
      } finally {
        loadingAttractions.value = false
      }
    }

    const handleViewAttraction = (attraction) => {
      console.log('Viewing attraction:', attraction.name)
    }

    const handleBookTour = (attraction) => {
      console.log('Booking tour for:', attraction.name)
    }

    const handleSearch = (query) => {
      // Redirigir a la página de búsqueda con el query
      router.visit(`/buscar?q=${encodeURIComponent(query)}`)
    }

    const handleSearchSelect = (suggestion) => {
      if (suggestion.type === 'attraction') {
        router.visit(`/atractivos/${suggestion.slug || suggestion.id}`)
      } else if (suggestion.type === 'department') {
        router.visit(`/departamentos/${suggestion.slug || suggestion.id}`)
      } else {
        // Para otros tipos, ir a búsqueda
        router.visit(`/buscar?q=${encodeURIComponent(suggestion.name)}`)
      }
    }

    onMounted(() => {
      fetchFeaturedAttractions()
    })

    return {
      featuredAttractions,
      loadingAttractions,
      handleViewAttraction,
      handleBookTour,
      handleSearch,
      handleSearchSelect
    }
  }
}
</script>