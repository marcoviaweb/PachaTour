<template>
  <Head title="Gestión de Departamentos - Admin" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Departamentos
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Administra los departamentos de Bolivia y sus información
          </p>
        </div>
        <Link
          :href="route('admin.departments.create')"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Nuevo Departamento
        </Link>
      </div>
    </template>

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
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Total Departamentos
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ statistics.total }}
                </dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Activos
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ statistics.active }}
                </dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Inactivos
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ statistics.inactive }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <EyeIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Total Atractivos
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ statistics.totalAttractions }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white shadow rounded-lg mb-8">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Buscar
            </label>
            <div class="relative">
              <input
                v-model="searchForm.search"
                type="text"
                placeholder="Buscar por nombre, capital o descripción..."
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 pl-10"
                @input="debouncedSearch"
              />
              <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400" />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Estado
            </label>
            <select
              v-model="searchForm.status"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
              @change="search"
            >
              <option value="">Todos</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
            </select>
          </div>

          <!-- Sort -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Ordenar por
            </label>
            <select
              v-model="searchForm.sort"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
              @change="search"
            >
              <option value="sort_order">Orden</option>
              <option value="name">Nombre</option>
              <option value="capital">Capital</option>
              <option value="created_at">Fecha Creación</option>
            </select>
          </div>
        </div>

        <!-- Clear Filters -->
        <div class="mt-4 flex justify-between items-center">
          <button
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Limpiar filtros
          </button>
          <div class="text-sm text-gray-500">
            {{ departments.total }} resultado(s) encontrado(s)
          </div>
        </div>
      </div>
    </div>

    <!-- Departments Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Departamento
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Capital
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Atractivos
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Coordenadas
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Acciones</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="department in departments.data" :key="department.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img
                        v-if="department.media?.[0]"
                        class="h-12 w-12 rounded-lg object-cover"
                        :src="department.media[0].url"
                        :alt="department.name"
                      />
                      <div
                        v-else
                        class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center"
                      >
                        <MapIcon class="h-6 w-6 text-gray-400" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ department.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ department.short_description }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ department.capital }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center space-x-2">
                    <span class="font-medium text-gray-900">{{ department.attractions_count }}</span>
                    <span class="text-gray-400">/</span>
                    <span class="text-green-600">{{ department.active_attractions_count }} activos</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStatus(department)"
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      department.is_active
                        ? 'bg-green-100 text-green-800 hover:bg-green-200'
                        : 'bg-red-100 text-red-800 hover:bg-red-200'
                    ]"
                  >
                    {{ department.is_active ? 'Activo' : 'Inactivo' }}
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div v-if="department.latitude && department.longitude" class="flex items-center space-x-1">
                    <MapPinIcon class="h-4 w-4 text-green-500" />
                    <span class="text-xs">{{ department.latitude.toFixed(4) }}, {{ department.longitude.toFixed(4) }}</span>
                  </div>
                  <span v-else class="text-gray-400 italic">Sin coordenadas</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link
                      :href="route('admin.departments.show', department.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Ver detalles"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="route('admin.departments.edit', department.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Editar"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </Link>
                    <button
                      @click="confirmDelete(department)"
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
        <div v-if="departments.last_page > 1" class="mt-6">
          <Pagination :links="departments.links" />
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteDepartment"
    >
      <template #title>
        Eliminar Departamento
      </template>
      <template #content>
        ¿Estás seguro de que deseas eliminar el departamento "{{ departmentToDelete?.name }}"?
        Esta acción no se puede deshacer y eliminará también todos los atractivos asociados.
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
  EyeIcon,
  MapPinIcon,
  PencilIcon,
  TrashIcon,
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
    EyeIcon,
    MapPinIcon,
    PencilIcon,
    TrashIcon,
  },

  props: {
    departments: Object,
    filters: Object,
    statistics: Object,
  },

  setup(props) {
    const searchForm = reactive({
      search: props.filters.search || '',
      status: props.filters.status || '',
      sort: props.filters.sort || 'sort_order',
      direction: props.filters.direction || 'asc',
    })

    const showDeleteModal = ref(false)
    const departmentToDelete = ref(null)

    const search = () => {
      router.get(route('admin.departments.index'), searchForm, {
        preserveState: true,
        replace: true,
      })
    }

    const debouncedSearch = debounce(search, 300)

    const clearFilters = () => {
      searchForm.search = ''
      searchForm.status = ''
      searchForm.sort = 'sort_order'
      searchForm.direction = 'asc'
      search()
    }

    const toggleStatus = (department) => {
      router.patch(route('admin.departments.toggle-status', department.id), {}, {
        preserveScroll: true,
      })
    }

    const confirmDelete = (department) => {
      departmentToDelete.value = department
      showDeleteModal.value = true
    }

    const deleteDepartment = () => {
      if (departmentToDelete.value) {
        router.delete(route('admin.departments.destroy', departmentToDelete.value.id), {
          onSuccess: () => {
            showDeleteModal.value = false
            departmentToDelete.value = null
          }
        })
      }
    }

    return {
      searchForm,
      showDeleteModal,
      departmentToDelete,
      search,
      debouncedSearch,
      clearFilters,
      toggleStatus,
      confirmDelete,
      deleteDepartment,
    }
  },
}
</script>