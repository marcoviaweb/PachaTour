<template>
  <Head :title="`${attraction.name} - Admin`" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ attraction.name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Vista detallada del atractivo turístico
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.attractions.edit', attraction.id)"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <PencilIcon class="w-5 h-5 mr-2" />
            Editar
          </Link>
          <Link
            :href="route('admin.attractions.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Volver a Lista
          </Link>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Basic Information -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Departamento</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.department?.name }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                    <dd class="mt-1">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ getTypeLabel(attraction.type) }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Ciudad</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ attraction.city }}</dd>
                  </div>
                </div>
                <div v-if="attraction.short_description">
                  <dt class="text-sm font-medium text-gray-500">Descripción Corta</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.short_description }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Descripción Completa</dt>
                  <dd class="mt-1 text-sm text-gray-900 leading-relaxed">{{ attraction.description }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Geographic Information -->
          <div class="bg-white shadow rounded-lg" v-if="attraction.latitude && attraction.longitude">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Ubicación</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="grid grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Latitud</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.latitude }}°</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Longitud</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.longitude }}°</dd>
                </div>
              </dl>
              <div class="mt-4">
                <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                  <div class="text-center">
                    <MapPinIcon class="h-8 w-8 text-green-500 mx-auto mb-2" />
                    <p class="text-sm text-gray-600">
                      Coordenadas: {{ attraction.latitude }}, {{ attraction.longitude }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      Mapa interactivo se mostrará aquí
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información Adicional</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="grid grid-cols-2 gap-4">
                <div v-if="attraction.entry_price">
                  <dt class="text-sm font-medium text-gray-500">Precio de Entrada</dt>
                  <dd class="mt-1 text-sm text-gray-900">Bs {{ attraction.entry_price }}</dd>
                </div>
                <div v-if="attraction.duration_hours">
                  <dt class="text-sm font-medium text-gray-500">Duración Recomendada</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.duration_hours }} horas</dd>
                </div>
                <div v-if="attraction.opening_hours">
                  <dt class="text-sm font-medium text-gray-500">Horario de Apertura</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.opening_hours }}</dd>
                </div>
                <div v-if="attraction.closing_hours">
                  <dt class="text-sm font-medium text-gray-500">Horario de Cierre</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.closing_hours }}</dd>
                </div>
                <div v-if="attraction.best_time_to_visit" class="col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Mejor Época para Visitar</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.best_time_to_visit }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Images Gallery -->
          <div v-if="attraction.media && attraction.media.length > 0" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Galería de Imágenes</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="media in attraction.media"
                  :key="media.id"
                  class="relative group cursor-pointer"
                  @click="openImageModal(media)"
                >
                  <img
                    :src="media.url"
                    :alt="media.name"
                    class="h-32 w-full object-cover rounded-lg"
                  />
                  <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-lg flex items-center justify-center">
                    <MagnifyingGlassIcon class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Statistics Card -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Estadísticas</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Visitas Totales</dt>
                  <dd class="mt-1 text-2xl font-bold text-gray-900">{{ statistics.total_visits || 0 }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Calificación Promedio</dt>
                  <dd class="mt-1 flex items-center">
                    <span class="text-2xl font-bold text-gray-900">{{ statistics.average_rating || '0.0' }}</span>
                    <StarIcon class="h-5 w-5 text-yellow-400 ml-1" />
                    <span class="text-sm text-gray-500 ml-1">({{ statistics.total_reviews || 0 }} reseñas)</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tours Disponibles</dt>
                  <dd class="mt-1 text-2xl font-bold text-gray-900">{{ statistics.tours_count || 0 }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Imágenes</dt>
                  <dd class="mt-1 text-2xl font-bold text-gray-900">{{ statistics.media_count || 0 }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Status and Actions -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Estado y Acciones</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-2">Estado Actual</dt>
                <div class="flex items-center space-x-3">
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
                  <span
                    v-if="attraction.is_featured"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                  >
                    <StarIcon class="h-3 w-3 mr-1" />
                    Destacado
                  </span>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-200">
                <div class="space-y-2">
                  <button
                    @click="toggleStatus"
                    :class="[
                      'w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition duration-150',
                      attraction.is_active
                        ? 'bg-red-600 hover:bg-red-700'
                        : 'bg-green-600 hover:bg-green-700'
                    ]"
                  >
                    {{ attraction.is_active ? 'Desactivar' : 'Activar' }}
                  </button>
                  
                  <button
                    @click="toggleFeatured"
                    :class="[
                      'w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition duration-150',
                      attraction.is_featured
                        ? 'bg-gray-600 hover:bg-gray-700'
                        : 'bg-purple-600 hover:bg-purple-700'
                    ]"
                  >
                    <StarIcon class="h-4 w-4 mr-2" />
                    {{ attraction.is_featured ? 'Quitar Destacado' : 'Marcar Destacado' }}
                  </button>

                  <Link
                    :href="route('attractions.show', attraction.slug)"
                    target="_blank"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150"
                  >
                    <ArrowTopRightOnSquareIcon class="w-4 h-4 mr-2" />
                    Ver en Sitio Público
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Metadata -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Metadatos</h3>
            </div>
            <div class="px-6 py-4">
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-gray-500">ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">#{{ attraction.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Slug</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ attraction.slug }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Creado</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(attraction.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(attraction.updated_at) }}</dd>
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
          class="w-full h-auto rounded-lg"
        />
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
  StarIcon,
  MapPinIcon,
  MagnifyingGlassIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Head,
    Link,
    AdminLayout,
    Modal,
    ArrowLeftIcon,
    PencilIcon,
    StarIcon,
    MapPinIcon,
    MagnifyingGlassIcon,
    ArrowTopRightOnSquareIcon,
  },

  props: {
    attraction: Object,
    statistics: Object,
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
      router.patch(route('admin.attractions.toggle-status', props.attraction.id), {}, {
        preserveScroll: true,
      })
    }

    const toggleFeatured = () => {
      router.patch(route('admin.attractions.toggle-featured', props.attraction.id), {}, {
        preserveScroll: true,
      })
    }

    const openImageModal = (media) => {
      selectedImage.value = media
      showImageModal.value = true
    }

    const closeImageModal = () => {
      showImageModal.value = false
      selectedImage.value = null
    }

    const getTypeLabel = (type) => {
      const types = {
        'natural': 'Natural',
        'cultural': 'Cultural',
        'historical': 'Histórico',
        'religious': 'Religioso',
        'adventure': 'Aventura',
        'archaeological': 'Arqueológico',
        'museum': 'Museo',
        'park': 'Parque',
        'monument': 'Monumento',
        'other': 'Otro'
      }
      return types[type] || type
    }

    return {
      showImageModal,
      selectedImage,
      formatDate,
      toggleStatus,
      toggleFeatured,
      openImageModal,
      closeImageModal,
      getTypeLabel,
    }
  },
}
</script>