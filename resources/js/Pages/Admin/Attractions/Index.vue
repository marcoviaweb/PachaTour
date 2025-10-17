<template>
  <Head title="Atractivos - Admin" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Atractivos
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Administra los atractivos turísticos del sistema
          </p>
        </div>
        <Link
          :href="route('admin.attractions.create')"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Crear Atractivo
        </Link>
      </div>
    </template>

    <div class="max-w-7xl mx-auto">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <MapIcon class="h-6 w-6 text-blue-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Atractivos</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ statistics.total }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Activos</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ statistics.active }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-yellow-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Inactivos</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ statistics.inactive }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <StarIcon class="h-6 w-6 text-purple-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Destacados</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ statistics.featured }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
        </div>
        <div class="px-6 py-4">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="searchForm.search"
                @input="debouncedSearch"
                type="text"
                placeholder="Buscar atractivos..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <!-- Department Filter -->
            <select
              v-model="searchForm.department"
              @change="search"
              class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
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

            <!-- Type Filter -->
            <select
              v-model="searchForm.type"
              @change="search"
              class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Todos los tipos</option>
              <option
                v-for="type in types"
                :key="type.value"
                :value="type.value"
              >
                {{ type.label }}
              </option>
            </select>

            <!-- Status Filter -->
            <select
              v-model="searchForm.status"
              @change="search"
              class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Todos los estados</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
            </select>

            <!-- Clear Filters -->
            <button
              @click="clearFilters"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150"
            >
              Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Attractions List -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    @change="toggleSelectAll"
                    :checked="selectedAttractions.length === attractions.data.length && attractions.data.length > 0"
                    class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Atractivo
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Departamento
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Calificación
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Visitas
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="attraction in attractions.data"
                :key="attraction.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="attraction.id"
                    v-model="selectedAttractions"
                    class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img
                        v-if="attraction.media && attraction.media.length > 0"
                        :src="attraction.media[0].url"
                        :alt="attraction.name"
                        class="h-12 w-12 rounded-lg object-cover"
                      />
                      <div
                        v-else
                        class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center"
                      >
                        <PhotoIcon class="h-6 w-6 text-gray-400" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 flex items-center">
                        {{ attraction.name }}
                        <StarIcon
                          v-if="attraction.is_featured"
                          class="h-4 w-4 text-yellow-400 ml-2"
                        />
                      </div>
                      <div class="text-sm text-gray-500">{{ attraction.city }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ attraction.department?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ getTypeLabel(attraction.type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      attraction.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ attraction.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center">
                    <span>{{ attraction.rating || '0.0' }}</span>
                    <StarIcon class="h-4 w-4 text-yellow-400 ml-1" />
                    <span class="text-gray-400 ml-1">({{ attraction.reviews_count || 0 }})</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ attraction.visits_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <Link
                      :href="route('admin.attractions.show', attraction.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Ver detalles"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="route('admin.attractions.edit', attraction.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Editar"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </Link>
                    <button
                      @click="confirmDelete(attraction)"
                      class="text-red-600 hover:text-red-900"
                      title="Eliminar"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="attractions.last_page > 1" class="px-6 py-4 border-t border-gray-200">
          <Pagination :links="attractions.links" />
        </div>
      </div>

      <!-- Bulk Actions -->
      <div
        v-if="selectedAttractions.length > 0"
        class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-4 shadow-lg"
      >
        <div class="flex items-center justify-between max-w-7xl mx-auto">
          <div class="text-sm text-gray-700">
            {{ selectedAttractions.length }} atractivo(s) seleccionado(s)
          </div>
          <div class="flex space-x-3">
            <button
              @click="bulkAction('activate')"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Activar
            </button>
            <button
              @click="bulkAction('deactivate')"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Desactivar
            </button>
            <button
              @click="bulkAction('feature')"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Destacar
            </button>
            <button
              @click="bulkAction('delete')"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteAttraction"
    >
      <template #title>
        Eliminar Atractivo
      </template>
      <template #content>
        ¿Estás seguro de que deseas eliminar el atractivo "{{ attractionToDelete?.name }}"?
        Esta acción no se puede deshacer y eliminará también todos los tours asociados.
      </template>
    </ConfirmationModal>
  </AdminLayout>
</template>

<script>
import { ref, reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  MapIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  StarIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Head,
    Link,
    AdminLayout,
    Pagination,
    ConfirmationModal,
    PlusIcon,
    MagnifyingGlassIcon,
    MapIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    StarIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PhotoIcon,
  },

  props: {
    attractions: Object,
    filters: Object,
    departments: Array,
    types: Array,
    statistics: Object,
  },

  setup(props) {
    const searchForm = reactive({
      search: props.filters.search || '',
      department: props.filters.department || '',
      type: props.filters.type || '',
      status: props.filters.status || '',
      sort: props.filters.sort || 'created_at',
      direction: props.filters.direction || 'desc',
    })

    const selectedAttractions = ref([])
    const showDeleteModal = ref(false)
    const attractionToDelete = ref(null)

    const search = () => {
      router.get(route('admin.attractions.index'), searchForm, {
        preserveState: true,
        replace: true,
      })
    }

    const debouncedSearch = debounce(search, 300)

    const clearFilters = () => {
      searchForm.search = ''
      searchForm.department = ''
      searchForm.type = ''
      searchForm.status = ''
      search()
    }

    const toggleSelectAll = () => {
      if (selectedAttractions.value.length === props.attractions.data.length) {
        selectedAttractions.value = []
      } else {
        selectedAttractions.value = props.attractions.data.map(attraction => attraction.id)
      }
    }

    const confirmDelete = (attraction) => {
      attractionToDelete.value = attraction
      showDeleteModal.value = true
    }

    const deleteAttraction = () => {
      if (attractionToDelete.value) {
        router.delete(route('admin.attractions.destroy', attractionToDelete.value.id), {
          onSuccess: () => {
            showDeleteModal.value = false
            attractionToDelete.value = null
          }
        })
      }
    }

    const bulkAction = (action) => {
      if (selectedAttractions.value.length === 0) return

      const message = {
        activate: '¿Activar los atractivos seleccionados?',
        deactivate: '¿Desactivar los atractivos seleccionados?',
        feature: '¿Marcar como destacados los atractivos seleccionados?',
        unfeature: '¿Desmarcar como destacados los atractivos seleccionados?',
        delete: '¿Eliminar los atractivos seleccionados? Esta acción no se puede deshacer.'
      }

      if (confirm(message[action])) {
        router.post(route('admin.attractions.bulk-action'), {
          action: action,
          attractions: selectedAttractions.value
        }, {
          onSuccess: () => {
            selectedAttractions.value = []
          }
        })
      }
    }

    const getTypeLabel = (type) => {
      const typeObj = props.types.find(t => t.value === type)
      return typeObj ? typeObj.label : type
    }

    // Watch for filter changes
    watch(() => props.filters, (newFilters) => {
      Object.assign(searchForm, newFilters)
    }, { deep: true })

    return {
      searchForm,
      selectedAttractions,
      showDeleteModal,
      attractionToDelete,
      search,
      debouncedSearch,
      clearFilters,
      toggleSelectAll,
      confirmDelete,
      deleteAttraction,
      bulkAction,
      getTypeLabel,
    }
  },
}
</script>