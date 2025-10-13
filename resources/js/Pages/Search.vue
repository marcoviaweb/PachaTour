<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="container mx-auto px-4 py-8">
        <!-- Search Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Buscar Atractivos Turísticos
          </h1>
          <p class="text-gray-600">
            Descubre los mejores destinos turísticos de Bolivia
          </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-8">
          <SearchBar
            v-model="searchQuery"
            placeholder="Buscar atractivos, departamentos o tipos de turismo..."
            @search="performSearch"
            @select="handleSuggestionSelect"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Filter Panel -->
          <div class="lg:col-span-1">
            <FilterPanel
              v-model="filters"
              @filter-change="handleFilterChange"
            />
          </div>

          <!-- Search Results -->
          <div class="lg:col-span-3">
            <SearchResults
              :attractions="attractions"
              :loading="loading"
              :search-query="searchQuery"
              :total="total"
              :current-page="currentPage"
              :total-pages="totalPages"
              :per-page="perPage"
              @attraction-select="handleAttractionSelect"
              @page-change="handlePageChange"
              @per-page-change="handlePerPageChange"
              @clear-search="clearSearch"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchBar from '@/Components/SearchBar.vue'
import FilterPanel from '@/Components/FilterPanel.vue'
import SearchResults from '@/Components/SearchResults.vue'
import axios from 'axios'

export default {
  name: 'SearchPage',
  components: {
    AppLayout,
    SearchBar,
    FilterPanel,
    SearchResults
  },
  setup() {
    const searchQuery = ref('')
    const filters = ref({})
    const attractions = ref([])
    const loading = ref(false)
    const total = ref(0)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const perPage = ref(12)

    const performSearch = async () => {
      loading.value = true
      
      try {
        const params = {
          q: searchQuery.value,
          page: currentPage.value,
          per_page: perPage.value,
          ...filters.value
        }

        const response = await axios.get('/api/search', { params })
        
        if (response.data.success) {
          attractions.value = response.data.data.data || response.data.data
          total.value = response.data.data.total || response.data.total || 0
          currentPage.value = response.data.data.current_page || 1
          totalPages.value = response.data.data.last_page || 1
        }
      } catch (error) {
        console.error('Error performing search:', error)
        attractions.value = []
        total.value = 0
      } finally {
        loading.value = false
      }
    }

    const handleSuggestionSelect = (suggestion) => {
      if (suggestion.type === 'attraction') {
        // Navigate to attraction detail usando slug
        if (suggestion.slug) {
          router.visit(`/atractivos/${suggestion.slug}`)
        } else {
          console.error('Sugerencia sin slug:', suggestion)
          router.visit('/atractivos')
        }
      } else if (suggestion.type === 'department') {
        // Set department filter and search
        filters.value.department_id = suggestion.id
        searchQuery.value = ''
        performSearch()
      } else if (suggestion.type === 'tourism_type') {
        // Set tourism type filter and search
        filters.value.tourism_types = [suggestion.id]
        searchQuery.value = ''
        performSearch()
      }
    }

    const handleFilterChange = (newFilters) => {
      filters.value = newFilters
      currentPage.value = 1 // Reset to first page when filters change
      performSearch()
    }

    const handleAttractionSelect = (attraction) => {
      // Asegurar que siempre usamos el slug, no el ID
      if (attraction.slug) {
        router.visit(`/atractivos/${attraction.slug}`)
      } else {
        console.error('Atractivo sin slug:', attraction)
        // Como fallback, redirigir a la lista de atractivos
        router.visit('/atractivos')
      }
    }

    const handlePageChange = (page) => {
      currentPage.value = page
      performSearch()
    }

    const handlePerPageChange = (newPerPage) => {
      perPage.value = newPerPage
      currentPage.value = 1 // Reset to first page
      performSearch()
    }

    const clearSearch = () => {
      searchQuery.value = ''
      filters.value = {}
      currentPage.value = 1
      performSearch()
    }

    // Watch for search query changes
    watch(searchQuery, (newQuery) => {
      if (newQuery.length === 0) {
        currentPage.value = 1
        performSearch()
      }
    })

    // Initial search on mount
    onMounted(() => {
      performSearch()
    })

    return {
      searchQuery,
      filters,
      attractions,
      loading,
      total,
      currentPage,
      totalPages,
      perPage,
      performSearch,
      handleSuggestionSelect,
      handleFilterChange,
      handleAttractionSelect,
      handlePageChange,
      handlePerPageChange,
      clearSearch
    }
  }
}
</script>