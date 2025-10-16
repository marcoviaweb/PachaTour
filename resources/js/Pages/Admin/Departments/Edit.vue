<template>
  <Head title="Editar Departamento - Admin" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Departamento: {{ department.name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Modifica la información del departamento
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.departments.show', department.id)"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <EyeIcon class="w-5 h-5 mr-2" />
            Ver Detalle
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

    <div class="max-w-4xl mx-auto">
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Información Básica</h3>
            <p class="mt-1 text-sm text-gray-600">
              Datos principales del departamento
            </p>
          </div>
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Nombre del Departamento *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.name }"
                  placeholder="Ej: Santa Cruz"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>

              <!-- Capital -->
              <div>
                <label for="capital" class="block text-sm font-medium text-gray-700">
                  Capital *
                </label>
                <input
                  id="capital"
                  v-model="form.capital"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.capital }"
                  placeholder="Ej: Santa Cruz de la Sierra"
                />
                <p v-if="errors.capital" class="mt-1 text-sm text-red-600">{{ errors.capital }}</p>
              </div>
            </div>

            <!-- Short Description -->
            <div>
              <label for="short_description" class="block text-sm font-medium text-gray-700">
                Descripción Corta
              </label>
              <input
                id="short_description"
                v-model="form.short_description"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.short_description }"
                placeholder="Breve descripción que aparecerá en listados"
                maxlength="255"
              />
              <p v-if="errors.short_description" class="mt-1 text-sm text-red-600">{{ errors.short_description }}</p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">
                Descripción Completa *
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.description }"
                placeholder="Descripción detallada del departamento (mínimo 50 caracteres)"
              />
              <p class="mt-1 text-sm text-gray-500">
                {{ form.description?.length || 0 }} / 50 caracteres mínimo
              </p>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>
          </div>
        </div>

        <!-- Geographic Information -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Información Geográfica</h3>
            <p class="mt-1 text-sm text-gray-600">
              Coordenadas y datos de ubicación
            </p>
          </div>
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Latitude -->
              <div>
                <label for="latitude" class="block text-sm font-medium text-gray-700">
                  Latitud
                </label>
                <input
                  id="latitude"
                  v-model.number="form.latitude"
                  type="number"
                  step="any"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.latitude }"
                  placeholder="-16.5000"
                />
                <p v-if="errors.latitude" class="mt-1 text-sm text-red-600">{{ errors.latitude }}</p>
              </div>

              <!-- Longitude -->
              <div>
                <label for="longitude" class="block text-sm font-medium text-gray-700">
                  Longitud
                </label>
                <input
                  id="longitude"
                  v-model.number="form.longitude"
                  type="number"
                  step="any"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.longitude }"
                  placeholder="-68.1500"
                />
                <p v-if="errors.longitude" class="mt-1 text-sm text-red-600">{{ errors.longitude }}</p>
              </div>

              <!-- Population -->
              <div>
                <label for="population" class="block text-sm font-medium text-gray-700">
                  Población
                </label>
                <input
                  id="population"
                  v-model.number="form.population"
                  type="number"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.population }"
                  placeholder="2800000"
                />
                <p v-if="errors.population" class="mt-1 text-sm text-red-600">{{ errors.population }}</p>
              </div>
            </div>

            <!-- Map Preview -->
            <div v-if="form.latitude && form.longitude" class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Vista Previa del Mapa
              </label>
              <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                <div class="text-center">
                  <MapPinIcon class="h-8 w-8 text-green-500 mx-auto mb-2" />
                  <p class="text-sm text-gray-600">
                    Coordenadas: {{ form.latitude }}, {{ form.longitude }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    Vista previa del mapa se mostrará aquí
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Current Images -->
        <div class="bg-white shadow rounded-lg" v-if="department.media && department.media.length > 0">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Imágenes Actuales</h3>
            <p class="mt-1 text-sm text-gray-600">
              Imágenes ya asociadas a este departamento
            </p>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div
                v-for="media in department.media"
                :key="media.id"
                class="relative group"
              >
                <img
                  :src="media.url"
                  :alt="media.name"
                  class="h-24 w-full object-cover rounded-lg"
                />
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                  <button
                    type="button"
                    @click="removeExistingImage(media.id)"
                    class="bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- New Images -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Agregar Nuevas Imágenes</h3>
            <p class="mt-1 text-sm text-gray-600">
              Sube nuevas imágenes para el departamento
            </p>
          </div>
          <div class="px-6 py-4">
            <!-- Image Upload Area -->
            <div
              @drop="handleDrop"
              @dragover.prevent
              @dragenter.prevent
              class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
            >
              <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
              <div class="mt-4">
                <label for="images" class="cursor-pointer">
                  <span class="mt-2 block text-sm font-medium text-gray-900">
                    Arrastra imágenes aquí o haz clic para seleccionar
                  </span>
                  <input
                    id="images"
                    type="file"
                    multiple
                    accept="image/*"
                    class="hidden"
                    @change="handleFileSelect"
                  />
                </label>
                <p class="mt-1 text-xs text-gray-500">
                  PNG, JPG, GIF hasta 10MB cada una
                </p>
              </div>
            </div>

            <!-- New Images Preview -->
            <div v-if="imagesPreviews.length > 0" class="mt-4">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                  v-for="(preview, index) in imagesPreviews"
                  :key="index"
                  class="relative group"
                >
                  <img
                    :src="preview"
                    class="h-24 w-full object-cover rounded-lg"
                  />
                  <button
                    type="button"
                    @click="removeNewImage(index)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <XMarkIcon class="h-3 w-3" />
                  </button>
                </div>
              </div>
            </div>

            <p v-if="errors.images" class="mt-2 text-sm text-red-600">{{ errors.images }}</p>
          </div>
        </div>

        <!-- Additional Options -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Opciones Adicionales</h3>
          </div>
          <div class="px-6 py-4 space-y-4">
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Departamento activo (visible en el sitio público)
              </label>
            </div>

            <div>
              <label for="sort_order" class="block text-sm font-medium text-gray-700">
                Orden de Visualización
              </label>
              <input
                id="sort_order"
                v-model.number="form.sort_order"
                type="number"
                class="mt-1 block w-24 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                placeholder="1"
              />
              <p class="mt-1 text-sm text-gray-500">
                Orden en que aparece en listados (menor número = mayor prioridad)
              </p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
          <Link
            :href="route('admin.departments.index')"
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
          >
            <template v-if="processing">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Actualizando...
            </template>
            <template v-else>
              Actualizar Departamento
            </template>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import {
  ArrowLeftIcon,
  EyeIcon,
  MapPinIcon,
  PhotoIcon,
  XMarkIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Head,
    Link,
    AdminLayout,
    ArrowLeftIcon,
    EyeIcon,
    MapPinIcon,
    PhotoIcon,
    XMarkIcon,
    TrashIcon,
  },

  props: {
    department: Object,
    errors: Object,
  },

  setup(props) {
    const { data: form, patch, processing } = useForm({
      name: props.department.name,
      capital: props.department.capital,
      description: props.department.description,
      short_description: props.department.short_description,
      latitude: props.department.latitude,
      longitude: props.department.longitude,
      population: props.department.population,
      is_active: props.department.is_active,
      sort_order: props.department.sort_order,
      images: [],
    })

    const imagesPreviews = ref([])

    const handleFileSelect = (event) => {
      handleFiles(event.target.files)
    }

    const handleDrop = (event) => {
      event.preventDefault()
      handleFiles(event.dataTransfer.files)
    }

    const handleFiles = (files) => {
      Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
          form.images.push(file)
          
          const reader = new FileReader()
          reader.onload = (e) => {
            imagesPreviews.value.push(e.target.result)
          }
          reader.readAsDataURL(file)
        }
      })
    }

    const removeNewImage = (index) => {
      form.images.splice(index, 1)
      imagesPreviews.value.splice(index, 1)
    }

    const removeExistingImage = (mediaId) => {
      if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
        router.delete(route('admin.departments.remove-media', {
          department: props.department.id,
          media: mediaId
        }), {
          preserveScroll: true,
        })
      }
    }

    const submit = () => {
      patch(route('admin.departments.update', props.department.id))
    }

    return {
      form,
      processing,
      imagesPreviews,
      handleFileSelect,
      handleDrop,
      removeNewImage,
      removeExistingImage,
      submit,
    }
  },
}
</script>