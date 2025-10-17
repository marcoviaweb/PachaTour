<template>
  <Head title="Editar Atractivo - Admin" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Atractivo: {{ attraction.name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Modifica la información del atractivo
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.attractions.show', attraction.id)"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium transition duration-150"
          >
            <EyeIcon class="w-5 h-5 mr-2" />
            Ver Detalle
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

    <div class="max-w-4xl mx-auto">
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Información Básica</h3>
            <p class="mt-1 text-sm text-gray-600">
              Datos principales del atractivo turístico
            </p>
          </div>
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Nombre del Atractivo *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.name }"
                  placeholder="Ej: Salar de Uyuni"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>

              <!-- Department -->
              <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">
                  Departamento *
                </label>
                <select
                  id="department_id"
                  v-model="form.department_id"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.department_id }"
                >
                  <option value="">Selecciona un departamento</option>
                  <option
                    v-for="department in departments"
                    :key="department.id"
                    :value="department.id"
                  >
                    {{ department.name }}
                  </option>
                </select>
                <p v-if="errors.department_id" class="mt-1 text-sm text-red-600">{{ errors.department_id }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Type -->
              <div>
                <label for="type" class="block text-sm font-medium text-gray-700">
                  Tipo de Atractivo *
                </label>
                <select
                  id="type"
                  v-model="form.type"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.type }"
                >
                  <option value="">Selecciona un tipo</option>
                  <option
                    v-for="type in types"
                    :key="type.value"
                    :value="type.value"
                  >
                    {{ type.label }}
                  </option>
                </select>
                <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
              </div>

              <!-- City -->
              <div>
                <label for="city" class="block text-sm font-medium text-gray-700">
                  Ciudad/Municipio *
                </label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.city }"
                  placeholder="Ej: Uyuni"
                />
                <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
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
                placeholder="Descripción detallada del atractivo (mínimo 50 caracteres)"
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
              Ubicación exacta del atractivo
            </p>
          </div>
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                  placeholder="-16.2902"
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
                  placeholder="-67.1097"
                />
                <p v-if="errors.longitude" class="mt-1 text-sm text-red-600">{{ errors.longitude }}</p>
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

        <!-- Additional Information -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Información Adicional</h3>
          </div>
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Entry Price -->
              <div>
                <label for="entry_price" class="block text-sm font-medium text-gray-700">
                  Precio de Entrada (Bs)
                </label>
                <input
                  id="entry_price"
                  v-model.number="form.entry_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.entry_price }"
                  placeholder="0.00"
                />
                <p v-if="errors.entry_price" class="mt-1 text-sm text-red-600">{{ errors.entry_price }}</p>
              </div>

              <!-- Duration -->
              <div>
                <label for="duration_hours" class="block text-sm font-medium text-gray-700">
                  Duración Recomendada (horas)
                </label>
                <input
                  id="duration_hours"
                  v-model.number="form.duration_hours"
                  type="number"
                  min="0"
                  step="0.5"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.duration_hours }"
                  placeholder="2.0"
                />
                <p v-if="errors.duration_hours" class="mt-1 text-sm text-red-600">{{ errors.duration_hours }}</p>
              </div>
            </div>

            <!-- Opening Hours -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="opening_hours" class="block text-sm font-medium text-gray-700">
                  Horario de Apertura
                </label>
                <input
                  id="opening_hours"
                  v-model="form.opening_hours"
                  type="time"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.opening_hours }"
                />
                <p v-if="errors.opening_hours" class="mt-1 text-sm text-red-600">{{ errors.opening_hours }}</p>
              </div>

              <div>
                <label for="closing_hours" class="block text-sm font-medium text-gray-700">
                  Horario de Cierre
                </label>
                <input
                  id="closing_hours"
                  v-model="form.closing_hours"
                  type="time"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.closing_hours }"
                />
                <p v-if="errors.closing_hours" class="mt-1 text-sm text-red-600">{{ errors.closing_hours }}</p>
              </div>
            </div>

            <!-- Best Time to Visit -->
            <div>
              <label for="best_time_to_visit" class="block text-sm font-medium text-gray-700">
                Mejor Época para Visitar
              </label>
              <input
                id="best_time_to_visit"
                v-model="form.best_time_to_visit"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.best_time_to_visit }"
                placeholder="Ej: Mayo a Septiembre (época seca)"
              />
              <p v-if="errors.best_time_to_visit" class="mt-1 text-sm text-red-600">{{ errors.best_time_to_visit }}</p>
            </div>
          </div>
        </div>

        <!-- Current Images -->
        <div class="bg-white shadow rounded-lg" v-if="attraction.media && attraction.media.length > 0">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Imágenes Actuales</h3>
            <p class="mt-1 text-sm text-gray-600">
              Imágenes ya asociadas a este atractivo
            </p>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div
                v-for="media in attraction.media"
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
              Agrega más imágenes del atractivo turístico
            </p>
          </div>
          <div class="px-6 py-4">
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

        <!-- Options -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Opciones</h3>
          </div>
          <div class="px-6 py-4 space-y-4">
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Atractivo activo (visible en el sitio público)
              </label>
            </div>

            <div class="flex items-center">
              <input
                id="is_featured"
                v-model="form.is_featured"
                type="checkbox"
                class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
              />
              <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                Atractivo destacado (aparece en la página principal)
              </label>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
          <Link
            :href="route('admin.attractions.index')"
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
              Actualizar Atractivo
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
    attraction: Object,
    departments: Array,
    types: Array,
    errors: Object,
  },

  setup(props) {
    const { data: form, patch, processing } = useForm({
      name: props.attraction.name,
      department_id: props.attraction.department_id,
      type: props.attraction.type,
      city: props.attraction.city,
      description: props.attraction.description,
      short_description: props.attraction.short_description,
      latitude: props.attraction.latitude,
      longitude: props.attraction.longitude,
      entry_price: props.attraction.entry_price,
      duration_hours: props.attraction.duration_hours,
      opening_hours: props.attraction.opening_hours,
      closing_hours: props.attraction.closing_hours,
      best_time_to_visit: props.attraction.best_time_to_visit,
      is_active: props.attraction.is_active,
      is_featured: props.attraction.is_featured,
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
        router.delete(route('admin.attractions.remove-media', {
          attraction: props.attraction.id,
          media: mediaId
        }), {
          preserveScroll: true,
        })
      }
    }

    const submit = () => {
      patch(route('admin.attractions.update', props.attraction.id))
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