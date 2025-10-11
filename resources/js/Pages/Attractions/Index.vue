<template>
  <Head title="Atractivos Turísticos - Pacha Tour" />
  
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
          Atractivos Turísticos de Bolivia
        </h1>
        <p class="text-gray-600 max-w-2xl">
          Descubre los increíbles destinos que Bolivia tiene para ofrecerte. 
          Desde paisajes naturales únicos hasta sitios históricos fascinantes.
        </p>
      </div>

      <!-- Search and Filters -->
      <div class="mb-8">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <SearchBar
              v-model="searchQuery"
              placeholder="Buscar atractivos por nombre, departamento o tipo..."
              @search="performSearch"
              @select="handleSuggestionSelect"
            />
          </div>
          <div class="lg:w-80">
            <FilterPanel
              :filters="filters"
              :active-filters="activeFilters"
              @filter-change="handleFilterChange"
              @clear-filters="clearFilters"
            />
          </div>
        </div>
      </div>

      <!-- Results -->
      <div class="mb-8">
        <SearchResults
          :attractions="attractions"
          :loading="loading"
          :search-query="searchQuery"
          :total-count="totalCount"
          :current-page="currentPage"
          :per-page="perPage"
          @view-attraction="handleViewAttraction"
          @book-tour="handleBookTour"
          @toggle-favorite="handleToggleFavorite"
        />
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center">
        <SearchPagination
          :current-page="currentPage"
          :total-pages="totalPages"
          :total-count="totalCount"
          :per-page="perPage"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchBar from '@/Components/SearchBar.vue'
import FilterPanel from '@/Components/FilterPanel.vue'
import SearchResults from '@/Components/SearchResults.vue'
import SearchPagination from '@/Components/SearchPagination.vue'
import axios from 'axios'

export default {
  name: 'AttractionsIndex',
  components: {
    Head,
    Link,
    AppLayout,
    SearchBar,
    FilterPanel,
    SearchResults,
    SearchPagination
  },
  setup() {
    const loading = ref(false)
    const searchQuery = ref('')
    const attractions = ref([])
    const totalCount = ref(0)
    const currentPage = ref(1)
    const perPage = ref(12)
    
    const activeFilters = ref({
      departments: [],
      tourism_types: [],
      price_ranges: [],
      difficulty_levels: [],
      has_tours: null
    })

    const filters = ref({
      departments: [],
      tourism_types: [
        { id: 'cultural', name: 'Cultural', count: 0 },
        { id: 'natural', name: 'Natural', count: 0 },
        { id: 'adventure', name: 'Aventura', count: 0 },
        { id: 'historical', name: 'Histórico', count: 0 },
        { id: 'religious', name: 'Religioso', count: 0 },
        { id: 'gastronomic', name: 'Gastronómico', count: 0 }
      ],
      price_ranges: [
        { id: 'free', name: 'Gratis', min: 0, max: 0, count: 0 },
        { id: 'low', name: 'Económico (1-100 Bs)', min: 1, max: 100, count: 0 },
        { id: 'medium', name: 'Moderado (101-300 Bs)', min: 101, max: 300, count: 0 },
        { id: 'high', name: 'Premium (301+ Bs)', min: 301, max: null, count: 0 }
      ],
      difficulty_levels: [
        { id: 'easy', name: 'Fácil', count: 0 },
        { id: 'moderate', name: 'Moderado', count: 0 },
        { id: 'difficult', name: 'Difícil', count: 0 }
      ]
    })

    // Computed
    const totalPages = computed(() => {
      return Math.ceil(totalCount.value / perPage.value)
    })

    // Methods
    const fetchAttractions = async (page = 1) => {
      loading.value = true
      
      try {
        const params = {
          page,
          per_page: perPage.value,
          search: searchQuery.value,
          ...activeFilters.value
        }

        const response = await axios.get('/api/attractions', { params })
        
        attractions.value = response.data.data
        totalCount.value = response.data.total
        currentPage.value = response.data.current_page
        
        // Update filter counts if available
        if (response.data.filters) {
          updateFilterCounts(response.data.filters)
        }
      } catch (error) {
        console.error('Error fetching attractions:', error)
        attractions.value = []
        totalCount.value = 0
      } finally {
        loading.value = false
      }
    }

    const fetchDepartments = async () => {
      try {
        const response = await axios.get('/api/departments')
        filters.value.departments = response.data.map(dept => ({
          id: dept.id,
          name: dept.name,
          slug: dept.slug,
          count: dept.attractions_count || 0
        }))
      } catch (error) {
        console.error('Error fetching departments:', error)
      }
    }

    const updateFilterCounts = (filterData) => {
      if (filterData.departments) {
        filters.value.departments.forEach(dept => {
          const found = filterData.departments.find(d => d.id === dept.id)
          if (found) dept.count = found.count
        })
      }
      
      if (filterData.tourism_types) {
        filters.value.tourism_types.forEach(type => {
          const found = filterData.tourism_types.find(t => t.id === type.id)
          if (found) type.count = found.count
        })
      }
    }

    const performSearch = (query) => {
      searchQuery.value = query
      currentPage.value = 1
      fetchAttractions(1)
    }

    const handleSuggestionSelect = (suggestion) => {
      if (suggestion.type === 'attraction') {
        router.visit(`/atractivos/${suggestion.slug}`)
      } else if (suggestion.type === 'department') {
        router.visit(`/departamentos/${suggestion.slug}`)
      }
    }

    const handleFilterChange = (filterType, value) => {
      if (Array.isArray(activeFilters.value[filterType])) {
        const index = activeFilters.value[filterType].indexOf(value)
        if (index > -1) {
          activeFilters.value[filterType].splice(index, 1)
        } else {
          activeFilters.value[filterType].push(value)
        }
      } else {
        activeFilters.value[filterType] = value
      }
      
      currentPage.value = 1
      fetchAttractions(1)
    }

    const clearFilters = () => {
      activeFilters.value = {
        departments: [],
        tourism_types: [],
        price_ranges: [],
        difficulty_levels: [],
        has_tours: null
      }
      currentPage.value = 1
      fetchAttractions(1)
    }

    const handlePageChange = (page) => {
      currentPage.value = page
      fetchAttractions(page)
      
      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    const handleViewAttraction = (attraction) => {
      router.visit(`/atractivos/${attraction.slug}`)
    }

    const handleBookTour = (attraction) => {
      router.visit(`/atractivos/${attraction.slug}`)
    }

    const handleToggleFavorite = async (attraction) => {
      try {
        if (attraction.is_favorite) {
          await axios.delete(`/api/user/favorites/${attraction.favorite_id}`)
          attraction.is_favorite = false
          attraction.favorite_id = null
        } else {
          const response = await axios.post('/api/user/favorites', {
            attraction_id: attraction.id
          })
          attraction.is_favorite = true
          attraction.favorite_id = response.data.favorite.id
        }
      } catch (error) {
        console.error('Error toggling favorite:', error)
      }
    }

    // Watchers
    watch(searchQuery, (newQuery) => {
      if (newQuery === '') {
        fetchAttractions(1)
      }
    })

    // Lifecycle
    onMounted(() => {
      fetchDepartments()
      fetchAttractions()
    })

    return {
      loading,
      searchQuery,
      attractions,
      totalCount,
      currentPage,
      perPage,
      totalPages,
      activeFilters,
      filters,
      performSearch,
      handleSuggestionSelect,
      handleFilterChange,
      clearFilters,
      handlePageChange,
      handleViewAttraction,
      handleBookTour,
      handleToggleFavorite
    }
  }
}
</script>