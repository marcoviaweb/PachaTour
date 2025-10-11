<template>
  <div class="department-grid">
    <!-- Título de la sección -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">
        Descubre Bolivia
      </h2>
      <p class="text-lg text-gray-600 max-w-2xl mx-auto">
        Explora los nueve departamentos de Bolivia y descubre la riqueza cultural, 
        natural e histórica que cada región tiene para ofrecer.
      </p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold mb-2">Error al cargar departamentos</h3>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <button 
          @click="fetchDepartments"
          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors"
        >
          Intentar nuevamente
        </button>
      </div>
    </div>

    <!-- Grid de departamentos -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="department in departments"
        :key="department.id"
        class="department-card group cursor-pointer transform transition-all duration-300 hover:scale-105"
        @click="goToDepartment(department)"
      >
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
          <!-- Imagen del departamento -->
          <div class="relative h-48 overflow-hidden">
            <img
              :src="getDepartmentImage(department)"
              :alt="department.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
              @error="handleImageError"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-4 left-4 text-white">
              <h3 class="text-xl font-bold mb-1">{{ department.name }}</h3>
              <p class="text-sm opacity-90">{{ getAttractionCount(department) }} atractivos</p>
            </div>
          </div>

          <!-- Contenido de la tarjeta -->
          <div class="p-4">
            <p class="text-gray-600 text-sm line-clamp-3 mb-4">
              {{ department.description || 'Descubre los maravillosos atractivos de este departamento.' }}
            </p>
            
            <!-- Estadísticas -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
              <div class="flex items-center space-x-4">
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  {{ getAttractionCount(department) }}
                </span>
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                  {{ getAverageRating(department) }}
                </span>
              </div>
            </div>

            <!-- Botón de acción -->
            <button class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors duration-200 font-medium">
              Explorar {{ department.name }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensaje cuando no hay departamentos -->
    <div v-if="!loading && !error && departments.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay departamentos disponibles</h3>
      <p class="text-gray-600">Los departamentos se cargarán pronto.</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  name: 'DepartmentGrid',
  props: {
    initialDepartments: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    const departments = ref(props.initialDepartments || [])
    const loading = ref(false)
    const error = ref(null)

    const fetchDepartments = async () => {
      if (departments.value.length > 0) return // Ya tenemos datos

      loading.value = true
      error.value = null

      try {
        const response = await axios.get('/api/departments')
        departments.value = response.data.data || response.data
      } catch (err) {
        console.error('Error fetching departments:', err)
        error.value = err.response?.data?.message || 'Error al cargar los departamentos'
      } finally {
        loading.value = false
      }
    }

    const goToDepartment = (department) => {
      router.visit(`/departamentos/${department.slug || department.id}`)
    }

    const getDepartmentImage = (department) => {
      if (department.image_path) {
        return `/storage/${department.image_path}`
      }
      // Imagen por defecto basada en el nombre del departamento
      return `/images/departments/${department.slug || department.name.toLowerCase()}.svg`
    }

    const handleImageError = (event) => {
      // Imagen de fallback
      event.target.src = '/images/placeholder.svg'
    }

    const getAttractionCount = (department) => {
      return department.attractions_count || department.attractions?.length || 0
    }

    const getAverageRating = (department) => {
      if (department.average_rating) {
        return parseFloat(department.average_rating).toFixed(1)
      }
      return '4.5' // Rating por defecto
    }

    onMounted(() => {
      fetchDepartments()
    })

    return {
      departments,
      loading,
      error,
      fetchDepartments,
      goToDepartment,
      getDepartmentImage,
      handleImageError,
      getAttractionCount,
      getAverageRating
    }
  }
}
</script>

<style scoped>
.department-grid {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8;
}

.department-card {
  @apply relative;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Animación de hover para las tarjetas */
.department-card:hover .department-card-overlay {
  @apply opacity-100;
}

.department-card-overlay {
  @apply absolute inset-0 bg-green-600 bg-opacity-10 opacity-0 transition-opacity duration-300 rounded-lg;
}
</style>