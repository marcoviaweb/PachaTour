<template>
  <Head :title="`${department.name} - Admin`" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ department.name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Vista detallada del departamento
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.departments.edit', department.id)"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <PencilIcon class="w-5 h-5 mr-2" />
            Editar
          </Link>
          <Link
            :href="route('admin.departments.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Volver a Lista
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <EyeIcon class="h-6 w-6 text-blue-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Atractivos
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.total_attractions }}
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
                    Atractivos Activos
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.active_attractions }}
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
                <PhotoIcon class="h-6 w-6 text-purple-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Imágenes
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ department.media?.length || 0 }}
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
                <span
                  :class="[
                    'h-6 w-6 rounded-full flex items-center justify-center text-sm font-medium text-white',
                    department.is_active ? 'bg-green-500' : 'bg-red-500'
                  ]"
                >
                  {{ department.is_active ? '✓' : '✗' }}
                </span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Estado
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ department.is_active ? 'Activo' : 'Inactivo' }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Information -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Basic Information -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información Básica</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ department.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Capital</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ department.capital }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Slug</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ department.slug }}</dd>
                </div>
                <div v-if="department.population">
                  <dt class="text-sm font-medium text-gray-500">Población</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ department.population.toLocaleString() }} habitantes</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Orden</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ department.sort_order }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(department.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Descripción</h3>
            </div>
            <div class="px-6 py-4">
              <div v-if="department.short_description" class="mb-4">
                <dt class="text-sm font-medium text-gray-500 mb-2">Descripción Corta</dt>
                <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ department.short_description }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-2">Descripción Completa</dt>
                <dd class="text-sm text-gray-900 leading-relaxed">{{ department.description }}</dd>
              </div>
            </div>
          </div>

          <!-- Images Gallery -->
          <div v-if="department.media && department.media.length > 0" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Galería de Imágenes</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="media in department.media"
                  :key="media.id"
                  class="relative group cursor-pointer"
                  @click="openImageModal(media)"
                >
                  <img
                    :src="media.url"
                    :alt="media.name"
                    class="h-32 w-full object-cover rounded-lg group-hover:opacity-75 transition-opacity"
                  />
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <MagnifyingGlassIcon class="h-8 w-8 text-white" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Attractions List -->
          <div v-if="attractions && attractions.length > 0" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Atractivos del Departamento</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div
                  v-for="attraction in attractions"
                  :key="attraction.id"
                  class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                >
                  <div class="flex items-center space-x-3">
                    <img
                      v-if="attraction.media?.[0]"
                      :src="attraction.media[0].url"
                      :alt="attraction.name"
                      class="h-10 w-10 rounded-lg object-cover"
                    />
                    <div v-else class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                      <EyeIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">{{ attraction.name }}</h4>
                      <p class="text-xs text-gray-500">{{ attraction.location || 'Sin ubicación' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
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
                    <Link
                      :href="route('admin.attractions.show', attraction.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Ver atractivo"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
          <!-- Quick Actions -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
              <button
                @click="toggleStatus"
                :class="[
                  'w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition duration-150',
                  department.is_active
                    ? 'bg-red-600 hover:bg-red-700'
                    : 'bg-green-600 hover:bg-green-700'
                ]"
              >
                {{ department.is_active ? 'Desactivar' : 'Activar' }}
              </button>
              <Link
                :href="route('departments.show', department.slug)"
                target="_blank"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150"
              >
                <ArrowTopRightOnSquareIcon class="w-4 h-4 mr-2" />
                Ver en Sitio Público
              </Link>
            </div>
          </div>

          <!-- Location -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Ubicación</h3>
            </div>
            <div class="px-6 py-4">
              <div v-if="department.latitude && department.longitude" class="space-y-4">
                <div class="text-sm">
                  <div class="flex items-center space-x-2 mb-2">
                    <MapPinIcon class="h-4 w-4 text-green-500" />
                    <span class="font-medium">Coordenadas</span>
                  </div>
                  <div class="ml-6 space-y-1">
                    <div>Latitud: {{ department.latitude }}</div>
                    <div>Longitud: {{ department.longitude }}</div>
                  </div>
                </div>
                <!-- Map placeholder -->
                <div class="h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                  <div class="text-center">
                    <MapIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">Mapa interactivo</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8">
                <MapPinIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
                <p class="text-sm text-gray-500">Sin coordenadas establecidas</p>
                <Link
                  :href="route('admin.departments.edit', department.id)"
                  class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-900"
                >
                  Agregar coordenadas
                </Link>
              </div>
            </div>
          </div>

          <!-- Meta Information -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información del Sistema</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-gray-500">ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">#{{ department.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Creado</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(department.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(department.updated_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <Modal :show="showImageModal" @close="closeImageModal" max-width="4xl">
      <div v-if="selectedImage" class="p-6">
        <img
          :src="selectedImage.url"
          :alt="selectedImage.name"
          class="w-full h-auto max-h-96 object-contain mx-auto"
        />
        <div class="mt-4 text-center">
          <h3 class="text-lg font-medium text-gray-900">{{ selectedImage.name }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ selectedImage.size }} bytes</p>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
  ArrowLeftIcon,
  PencilIcon,
  EyeIcon,
  CheckCircleIcon,
  PhotoIcon,
  MapPinIcon,
  MapIcon,
  ArrowTopRightOnSquareIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Head,
    Link,
    AdminLayout,
    Modal,
    ArrowLeftIcon,
    PencilIcon,
    EyeIcon,
    CheckCircleIcon,
    PhotoIcon,
    MapPinIcon,
    MapIcon,
    ArrowTopRightOnSquareIcon,
    MagnifyingGlassIcon,
  },

  props: {
    department: Object,
    statistics: Object,
    attractions: Array,
  },

  setup(props) {
    const showImageModal = ref(false)
    const selectedImage = ref(null)

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const toggleStatus = () => {
      router.patch(route('admin.departments.toggle-status', props.department.id), {}, {
        preserveScroll: true,
      })
    }

    const openImageModal = (image) => {
      selectedImage.value = image
      showImageModal.value = true
    }

    const closeImageModal = () => {
      showImageModal.value = false
      selectedImage.value = null
    }

    return {
      showImageModal,
      selectedImage,
      formatDate,
      toggleStatus,
      openImageModal,
      closeImageModal,
    }
  },
}
</script>