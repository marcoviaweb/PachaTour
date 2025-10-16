<template>
  <Head :title="`${department?.name || 'Departamento'} - Pacha Tour`" />
  
  <AppLayout>
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando departamento...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="text-red-600 mb-4">
          <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h2 class="text-2xl font-bold mb-2">Departamento no encontrado</h2>
          <p class="text-gray-600 mb-4">{{ error }}</p>
          <Link 
            href="/"
            class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors"
          >
            Volver al inicio
          </Link>
        </div>
      </div>
    </div>

    <!-- Department Content -->
    <div v-else-if="department">
      <!-- Hero Section -->
      <section class="relative bg-gradient-to-r from-green-600 to-blue-600 text-white">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
          <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
              {{ department.name }}
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
              {{ department.short_description || department.description }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                <div class="text-2xl font-bold">{{ department.attractions_count || 0 }}</div>
                <div class="text-sm opacity-90">Atractivos</div>
              </div>
              <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                <div class="text-2xl font-bold">{{ department.capital }}</div>
                <div class="text-sm opacity-90">Capital</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Department Info -->
      <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <h2 class="text-3xl font-bold text-gray-900 mb-6">
                Descubre {{ department.name }}
              </h2>
              <div class="prose prose-lg text-gray-600">
                <p>{{ department.description }}</p>
              </div>
              
              <!-- Department Stats -->
              <div class="mt-8 grid grid-cols-2 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-green-600">{{ formatNumber(department.population) }}</div>
                  <div class="text-sm text-gray-600">Habitantes</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-green-600">{{ formatNumber(department.area_km2) }}</div>
                  <div class="text-sm text-gray-600">km²</div>
                </div>
              </div>
            </div>
            
            <!-- Department Map -->
            <DepartmentMap 
              v-if="department"
              :department-name="department.name"
              :department-slug="departmentSlug"
              :attractions="attractions"
            />
          </div>
        </div>
      </section>

      <!-- Attractions Section -->
      <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
              Atractivos de {{ department.name }}
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
              Descubre los mejores destinos turísticos que {{ department.name }} tiene para ofrecer
            </p>
          </div>

          <!-- Loading attractions -->
          <div v-if="loadingAttractions" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
          </div>

          <!-- Attractions Grid -->
          <div v-else-if="attractions.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <AttractionCard
              v-for="attraction in attractions"
              :key="attraction.id"
              :attraction="attraction"
              @view-details="handleViewAttraction"
              @book-tour="handleBookTour"
            />
          </div>

          <!-- No attractions -->
          <div v-else class="text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay atractivos disponibles</h3>
            <p class="text-gray-600">Los atractivos de este departamento se cargarán pronto.</p>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import AttractionCard from '@/Components/AttractionCard.vue'
import DepartmentMap from '@/Components/DepartmentMap.vue'
import axios from 'axios'

export default {
  name: 'DepartmentShow',
  components: {
    Head,
    Link,
    AppLayout,
    AttractionCard,
    DepartmentMap
  },
  props: {
    departmentSlug: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const department = ref(null)
    const attractions = ref([])
    const loading = ref(true)
    const loadingAttractions = ref(false)
    const error = ref(null)

    const fetchDepartment = async () => {
      loading.value = true
      error.value = null

      try {
        const response = await axios.get(`/api/departments/${props.departmentSlug}`)
        department.value = response.data.data
        
        // Fetch attractions for this department
        await fetchAttractions()
      } catch (err) {
        console.error('Error fetching department:', err)
        error.value = err.response?.data?.message || 'Error al cargar el departamento'
      } finally {
        loading.value = false
      }
    }

    const fetchAttractions = async () => {
      if (!department.value) return

      loadingAttractions.value = true
      try {
        const response = await axios.get(`/api/attractions/department/${props.departmentSlug}`)
        attractions.value = response.data.data?.attractions?.data || response.data.data || []
      } catch (err) {
        console.error('Error fetching attractions:', err)
        attractions.value = []
      } finally {
        loadingAttractions.value = false
      }
    }

    const handleViewAttraction = (attraction) => {
      router.visit(`/atractivos/${attraction.slug || attraction.id}`)
    }

    const handleBookTour = (attraction) => {
      router.visit(`/atractivos/${attraction.slug || attraction.id}/reservar`)
    }

    const formatNumber = (number) => {
      if (!number) return '0'
      return new Intl.NumberFormat('es-BO').format(number)
    }

    onMounted(() => {
      fetchDepartment()
    })

    return {
      department,
      attractions,
      loading,
      loadingAttractions,
      error,
      handleViewAttraction,
      handleBookTour,
      formatNumber
    }
  }
}
</script>