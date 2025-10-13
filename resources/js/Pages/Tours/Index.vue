<template>
  <Head title="Tours - Pacha Tour" />
  
  <AppLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
      <div class="absolute inset-0 bg-black opacity-40"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Tours por Bolivia
          </h1>
          <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            Descubre experiencias únicas con nuestros tours organizados. 
            Desde aventuras de un día hasta expediciones de varios días.
          </p>
          
          <!-- Barra de búsqueda -->
          <div class="mb-8 w-full flex justify-center">
            <div class="w-full max-w-2xl">
              <SearchBar
                placeholder="Buscar tours por nombre, tipo o destino..."
                @search="handleSearch"
                @select="handleSearchSelect"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filters Section -->
    <section class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select 
              v-model="filters.type" 
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="">Todos los tipos</option>
              <option value="cultural">Cultural</option>
              <option value="adventure">Aventura</option>
              <option value="nature">Naturaleza</option>
              <option value="historical">Histórico</option>
              <option value="gastronomic">Gastronómico</option>
            </select>
          </div>

          <!-- Difficulty Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Dificultad</label>
            <select 
              v-model="filters.difficulty" 
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="">Todas las dificultades</option>
              <option value="easy">Fácil</option>
              <option value="moderate">Moderada</option>
              <option value="hard">Difícil</option>
              <option value="extreme">Extrema</option>
            </select>
          </div>

          <!-- Duration Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Duración</label>
            <select 
              v-model="filters.duration_days" 
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="">Cualquier duración</option>
              <option value="1">1 día</option>
              <option value="2">2 días</option>
              <option value="3">3 días</option>
              <option value="4">4+ días</option>
            </select>
          </div>

          <!-- Price Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Precio máximo</label>
            <input 
              type="number" 
              v-model="filters.max_price" 
              @input="applyFilters"
              placeholder="Bs. 0"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
          </div>

          <!-- Sort -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
            <select 
              v-model="filters.sort_by" 
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="created_at">Más recientes</option>
              <option value="name">Nombre (A-Z)</option>
              <option value="price_per_person">Precio (menor a mayor)</option>
              <option value="rating">Mejor valorados</option>
              <option value="bookings_count">Más populares</option>
            </select>
          </div>
        </div>

        <!-- Featured Tours Toggle -->
        <div class="mt-4 flex items-center">
          <input 
            type="checkbox" 
            id="featured" 
            v-model="filters.featured"
            @change="applyFilters"
            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
          >
          <label for="featured" class="ml-2 text-sm text-gray-600">Solo tours destacados</label>
        </div>
      </div>
    </section>

    <!-- Tours Grid Section -->
    <section class="py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
        </div>

        <!-- Results Header -->
        <div v-else-if="tours.data" class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">
            Tours Disponibles
          </h2>
          <p class="text-gray-600">
            Mostrando {{ tours.data.length }} de {{ tours.total }} tours
            <span v-if="searchQuery"> para "{{ searchQuery }}"</span>
          </p>
        </div>

        <!-- Tours Grid -->
        <div v-if="tours.data && tours.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <TourCard
            v-for="tour in tours.data"
            :key="tour.id"
            :tour="tour"
            @view-details="handleViewTour"
            @book-tour="handleBookTour"
          />
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading" class="text-center py-16">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            No se encontraron tours
          </h3>
          <p class="text-gray-600 mb-4">
            <span v-if="searchQuery">
              No encontramos tours que coincidan con tu búsqueda "{{ searchQuery }}".
            </span>
            <span v-else>
              Ajusta los filtros para ver más resultados.
            </span>
          </p>
          <button 
            @click="clearFilters"
            class="text-purple-600 hover:text-purple-800 font-medium"
          >
            Limpiar filtros
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="tours.data && tours.last_page > 1" class="mt-12">
          <nav class="flex justify-center">
            <div class="flex space-x-2">
              <button
                v-if="tours.prev_page_url"
                @click="loadPage(tours.current_page - 1)"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50"
              >
                Anterior
              </button>
              
              <span 
                v-for="page in paginationPages" 
                :key="page"
                class="px-4 py-2 rounded-lg"
                :class="page === tours.current_page 
                  ? 'bg-purple-600 text-white' 
                  : 'border border-gray-300 text-gray-600 hover:bg-gray-50 cursor-pointer'"
                @click="page !== tours.current_page && loadPage(page)"
              >
                {{ page }}
              </span>
              
              <button
                v-if="tours.next_page_url"
                @click="loadPage(tours.current_page + 1)"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50"
              >
                Siguiente
              </button>
            </div>
          </nav>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchBar from '@/Components/SearchBar.vue'
import TourCard from '@/Components/TourCard.vue'

// Reactive data
const loading = ref(false)
const tours = ref({})
const searchQuery = ref('')

const filters = reactive({
  type: '',
  difficulty: '',
  duration_days: '',
  max_price: '',
  sort_by: 'created_at',
  featured: false
})

// Computed
const paginationPages = computed(() => {
  if (!tours.value.last_page) return []
  
  const current = tours.value.current_page
  const total = tours.value.last_page
  const pages = []
  
  // Always show first page
  if (total > 1) pages.push(1)
  
  // Show pages around current page
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    if (!pages.includes(i)) pages.push(i)
  }
  
  // Always show last page
  if (total > 1 && !pages.includes(total)) pages.push(total)
  
  return pages
})

// Methods
const loadTours = async (page = 1) => {
  loading.value = true
  
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '12',
      ...Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => 
          value !== '' && value !== false
        )
      )
    })

    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }

    const response = await fetch(`/api/tours?${params}`)
    const data = await response.json()
    
    if (data.success) {
      tours.value = data.data
    }
  } catch (error) {
    console.error('Error loading tours:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  loadTours()
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    if (key === 'sort_by') {
      filters[key] = 'created_at'
    } else if (key === 'featured') {
      filters[key] = false
    } else {
      filters[key] = ''
    }
  })
  searchQuery.value = ''
  loadTours()
}

const loadPage = (page) => {
  loadTours(page)
}

const handleSearch = (query) => {
  searchQuery.value = query
  loadTours()
}

const handleSearchSelect = (item) => {
  if (item.type === 'tour') {
    router.visit(`/tours/${item.slug}`)
  }
}

const handleViewTour = (tour) => {
  router.visit(`/tours/${tour.slug}`)
}

const handleBookTour = (tour) => {
  router.visit(`/tours/${tour.slug}#booking`)
}

// Lifecycle
onMounted(() => {
  loadTours()
})
</script>