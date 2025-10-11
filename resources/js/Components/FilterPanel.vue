<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
      <button
        @click="clearAllFilters"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        Limpiar todo
      </button>
    </div>

    <div class="space-y-6">
      <!-- Department Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Departamento
        </label>
        <select
          v-model="filters.department_id"
          @change="updateFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Todos los departamentos</option>
          <option
            v-for="department in departments"
            :key="department.id"
            :value="department.id"
          >
            {{ department.name }}
          </option>
        </select>
      </div>

      <!-- Tourism Type Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Tipo de turismo
        </label>
        <div class="space-y-2">
          <label
            v-for="type in tourismTypes"
            :key="type.value"
            class="flex items-center"
          >
            <input
              v-model="filters.tourism_types"
              :value="type.value"
              @change="updateFilters"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <span class="ml-2 text-sm text-gray-700">{{ type.label }}</span>
          </label>
        </div>
      </div>

      <!-- Price Range Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Rango de precio (Bs.)
        </label>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <input
              v-model.number="filters.price_min"
              @input="updateFilters"
              type="number"
              min="0"
              placeholder="Mín"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <input
              v-model.number="filters.price_max"
              @input="updateFilters"
              type="number"
              min="0"
              placeholder="Máx"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>

      <!-- Rating Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Valoración mínima
        </label>
        <div class="flex items-center space-x-2">
          <div class="flex space-x-1">
            <button
              v-for="star in 5"
              :key="star"
              @click="setRating(star)"
              :class="[
                'w-6 h-6 focus:outline-none',
                star <= filters.min_rating ? 'text-yellow-400' : 'text-gray-300'
              ]"
            >
              <svg fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </button>
          </div>
          <span class="text-sm text-gray-600">
            {{ filters.min_rating > 0 ? `${filters.min_rating} estrella${filters.min_rating > 1 ? 's' : ''}` : 'Cualquiera' }}
          </span>
        </div>
      </div>

      <!-- Distance Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Distancia máxima (km)
        </label>
        <div class="px-3">
          <input
            v-model.number="filters.max_distance"
            @input="updateFilters"
            type="range"
            min="0"
            max="500"
            step="10"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
          />
          <div class="flex justify-between text-xs text-gray-500 mt-1">
            <span>0 km</span>
            <span class="font-medium">{{ filters.max_distance || 500 }} km</span>
            <span>500 km</span>
          </div>
        </div>
      </div>

      <!-- Accessibility Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Adecuación
        </label>
        <div class="space-y-2">
          <label
            v-for="accessibility in accessibilityOptions"
            :key="accessibility.value"
            class="flex items-center"
          >
            <input
              v-model="filters.accessibility"
              :value="accessibility.value"
              @change="updateFilters"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <span class="ml-2 text-sm text-gray-700">{{ accessibility.label }}</span>
          </label>
        </div>
      </div>

      <!-- Sort Options -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ordenar por
        </label>
        <select
          v-model="filters.sort_by"
          @change="updateFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="name">Nombre (A-Z)</option>
          <option value="name_desc">Nombre (Z-A)</option>
          <option value="rating">Mejor valorados</option>
          <option value="price_asc">Precio (menor a mayor)</option>
          <option value="price_desc">Precio (mayor a menor)</option>
          <option value="distance">Más cercanos</option>
          <option value="created_at">Más recientes</option>
        </select>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div v-if="hasActiveFilters" class="mt-6 pt-4 border-t border-gray-200">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Filtros activos:</h4>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="filter in activeFiltersList"
          :key="filter.key"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
        >
          {{ filter.label }}
          <button
            @click="removeFilter(filter.key)"
            class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'FilterPanel',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:modelValue', 'filter-change'],
  setup(props, { emit }) {
    const departments = ref([])
    const loading = ref(false)

    const filters = ref({
      department_id: '',
      tourism_types: [],
      price_min: null,
      price_max: null,
      min_rating: 0,
      max_distance: 500,
      accessibility: [],
      sort_by: 'name',
      ...props.modelValue
    })

    const tourismTypes = ref([
      { value: 'cultural', label: 'Cultural' },
      { value: 'natural', label: 'Natural' },
      { value: 'adventure', label: 'Aventura' },
      { value: 'historical', label: 'Histórico' },
      { value: 'religious', label: 'Religioso' },
      { value: 'gastronomic', label: 'Gastronómico' },
      { value: 'ecological', label: 'Ecológico' }
    ])

    const accessibilityOptions = ref([
      { value: 'wheelchair', label: 'Accesible en silla de ruedas' },
      { value: 'elderly', label: 'Adecuado para adultos mayores' },
      { value: 'children', label: 'Adecuado para niños' },
      { value: 'pets', label: 'Permite mascotas' }
    ])

    const hasActiveFilters = computed(() => {
      return filters.value.department_id ||
             filters.value.tourism_types.length > 0 ||
             filters.value.price_min ||
             filters.value.price_max ||
             filters.value.min_rating > 0 ||
             filters.value.max_distance < 500 ||
             filters.value.accessibility.length > 0 ||
             filters.value.sort_by !== 'name'
    })

    const activeFiltersList = computed(() => {
      const list = []
      
      if (filters.value.department_id) {
        const dept = departments.value.find(d => d.id == filters.value.department_id)
        if (dept) {
          list.push({ key: 'department_id', label: dept.name })
        }
      }
      
      if (filters.value.tourism_types.length > 0) {
        filters.value.tourism_types.forEach(type => {
          const typeObj = tourismTypes.value.find(t => t.value === type)
          if (typeObj) {
            list.push({ key: `tourism_type_${type}`, label: typeObj.label })
          }
        })
      }
      
      if (filters.value.price_min) {
        list.push({ key: 'price_min', label: `Precio mín: Bs. ${filters.value.price_min}` })
      }
      
      if (filters.value.price_max) {
        list.push({ key: 'price_max', label: `Precio máx: Bs. ${filters.value.price_max}` })
      }
      
      if (filters.value.min_rating > 0) {
        list.push({ key: 'min_rating', label: `${filters.value.min_rating}+ estrellas` })
      }
      
      if (filters.value.max_distance < 500) {
        list.push({ key: 'max_distance', label: `Hasta ${filters.value.max_distance} km` })
      }
      
      if (filters.value.accessibility.length > 0) {
        filters.value.accessibility.forEach(acc => {
          const accObj = accessibilityOptions.value.find(a => a.value === acc)
          if (accObj) {
            list.push({ key: `accessibility_${acc}`, label: accObj.label })
          }
        })
      }
      
      return list
    })

    const fetchDepartments = async () => {
      try {
        const response = await axios.get('/api/departments')
        departments.value = response.data.data || []
      } catch (error) {
        console.error('Error fetching departments:', error)
      }
    }

    const updateFilters = () => {
      emit('update:modelValue', { ...filters.value })
      emit('filter-change', { ...filters.value })
    }

    const setRating = (rating) => {
      filters.value.min_rating = filters.value.min_rating === rating ? 0 : rating
      updateFilters()
    }

    const removeFilter = (key) => {
      if (key === 'department_id') {
        filters.value.department_id = ''
      } else if (key.startsWith('tourism_type_')) {
        const type = key.replace('tourism_type_', '')
        filters.value.tourism_types = filters.value.tourism_types.filter(t => t !== type)
      } else if (key === 'price_min') {
        filters.value.price_min = null
      } else if (key === 'price_max') {
        filters.value.price_max = null
      } else if (key === 'min_rating') {
        filters.value.min_rating = 0
      } else if (key === 'max_distance') {
        filters.value.max_distance = 500
      } else if (key.startsWith('accessibility_')) {
        const acc = key.replace('accessibility_', '')
        filters.value.accessibility = filters.value.accessibility.filter(a => a !== acc)
      }
      updateFilters()
    }

    const clearAllFilters = () => {
      filters.value = {
        department_id: '',
        tourism_types: [],
        price_min: null,
        price_max: null,
        min_rating: 0,
        max_distance: 500,
        accessibility: [],
        sort_by: 'name'
      }
      updateFilters()
    }

    // Watch for external changes to modelValue
    watch(() => props.modelValue, (newValue) => {
      filters.value = { ...filters.value, ...newValue }
    }, { deep: true })

    onMounted(() => {
      fetchDepartments()
    })

    return {
      filters,
      departments,
      tourismTypes,
      accessibilityOptions,
      hasActiveFilters,
      activeFiltersList,
      loading,
      updateFilters,
      setRating,
      removeFilter,
      clearAllFilters
    }
  }
}
</script>

<style scoped>
.slider::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
}
</style>