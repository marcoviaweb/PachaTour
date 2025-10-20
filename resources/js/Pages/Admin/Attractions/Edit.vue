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
      <form @submit.prevent="updateAttraction" class="space-y-8">
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
            <div v-if="form.latitude && form.longitude && isValidCoordinates()" class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Vista Previa del Mapa
              </label>
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center">
                    <MapPinIcon class="h-5 w-5 text-green-500 mr-2" />
                    <span class="text-sm font-medium text-gray-700">
                      Coordenadas: {{ parseFloat(form.latitude).toFixed(6) }}, {{ parseFloat(form.longitude).toFixed(6) }}
                    </span>
                  </div>
                  <button
                    type="button"
                    @click="openInGoogleMaps"
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                  >
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    Ver en Google Maps
                  </button>
                </div>
                
                <!-- Attraction Map Component -->
                <div class="h-64 bg-white rounded-md overflow-hidden border border-gray-300">
                  <AttractionMap 
                    :attraction="{
                      id: attraction.id,
                      name: attraction.name,
                      latitude: parseFloat(form.latitude),
                      longitude: parseFloat(form.longitude),
                      short_description: attraction.short_description,
                      image_url: attraction.image_url,
                      department: attraction.department
                    }"
                    :compact="true"
                  />
                </div>
                
                <div class="mt-3 text-xs text-gray-500 flex items-center justify-between">
                  <span>Ubicación exacta del atractivo turístico</span>
                  <span>Powered by OpenStreetMap</span>
                </div>
              </div>
            </div>
            
            <!-- Invalid coordinates message -->
            <div v-else-if="form.latitude || form.longitude" class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Vista Previa del Mapa
              </label>
              <div class="h-32 bg-yellow-50 border-2 border-dashed border-yellow-300 rounded-lg flex items-center justify-center">
                <div class="text-center">
                  <svg class="h-8 w-8 text-yellow-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  <p class="text-sm text-yellow-700">
                    Coordenadas inválidas. Verifica la latitud y longitud.
                  </p>
                  <p class="text-xs text-yellow-600 mt-1">
                    Lat: {{ form.latitude || 'No especificada' }}, Lng: {{ form.longitude || 'No especificada' }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- No coordinates message -->
            <div v-else class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Vista Previa del Mapa
              </label>
              <div class="h-32 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                <div class="text-center">
                  <MapPinIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
                  <p class="text-sm text-gray-500">
                    Ingresa las coordenadas para ver la vista previa del mapa
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
                  v-model="openingTime"
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
                  v-model="closingTime"
                  type="time"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.closing_hours }"
                />
                <p v-if="errors.closing_hours" class="mt-1 text-sm text-red-600">{{ errors.closing_hours }}</p>
              </div>
            </div>

            <!-- Best Season -->
            <div>
              <label for="best_season" class="block text-sm font-medium text-gray-700">
                Mejor Época para Visitar
              </label>
              <input
                id="best_season"
                v-model="form.best_season"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.best_season }"
                placeholder="Ej: Mayo a Septiembre (época seca)"
              />
              <p v-if="errors.best_season" class="mt-1 text-sm text-red-600">{{ errors.best_season }}</p>
            </div>

            <!-- Amenities -->
            <div>
              <label for="amenities" class="block text-sm font-medium text-gray-700">
                Servicios y Comodidades
              </label>
              <input
                id="amenities"
                v-model="amenitiesString"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.amenities }"
                placeholder="Ej: Estacionamiento, Restaurante, Guías turísticos, Baños"
              />
              <p class="mt-1 text-xs text-gray-500">Separa los servicios con comas</p>
              <p v-if="errors.amenities" class="mt-1 text-sm text-red-600">{{ errors.amenities }}</p>
            </div>

            <!-- Restrictions -->
            <div>
              <label for="restrictions" class="block text-sm font-medium text-gray-700">
                Restricciones y Requisitos
              </label>
              <input
                id="restrictions"
                v-model="restrictionsString"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-300': errors.restrictions }"
                placeholder="Ej: Edad mínima 12 años, No permitido para embarazadas, Llevar protector solar"
              />
              <p class="mt-1 text-xs text-gray-500">Separa las restricciones con comas</p>
              <p v-if="errors.restrictions" class="mt-1 text-sm text-red-600">{{ errors.restrictions }}</p>
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
                    :src="preview.preview"
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
import { ref, reactive } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import AttractionMap from '@/Components/AttractionMap.vue'
import axios from 'axios'
import {
  ArrowLeftIcon,
  EyeIcon,
  MapPinIcon,
  PhotoIcon,
  XMarkIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

export default {
  name: 'EditAttraction',
  components: {
    Head,
    Link,
    AdminLayout,
    AttractionMap,
    ArrowLeftIcon,
    EyeIcon,
    MapPinIcon,
    PhotoIcon,
    XMarkIcon,
    TrashIcon,
  },

  props: {
    attraction: {
      type: Object,
      required: true
    },
    departments: {
      type: Array,
      default: () => []
    },
    types: {
      type: Array,
      default: () => []
    }
  },

  setup(props) {
    const processing = ref(false)
    const errors = ref({})

    // Formulario reactivo con inicialización inmediata
    const form = reactive({
      name: props.attraction?.name || '',
      department_id: props.attraction?.department_id || null,
      type: props.attraction?.type || '',
      city: props.attraction?.city || '',
      description: props.attraction?.description || '',
      short_description: props.attraction?.short_description || '',
      address: props.attraction?.address || '',
      latitude: props.attraction?.latitude || null,
      longitude: props.attraction?.longitude || null,
      entry_price: props.attraction?.entry_price || null,
      currency: props.attraction?.currency || 'BOB',
      difficulty_level: props.attraction?.difficulty_level || 'easy',
      estimated_duration: props.attraction?.estimated_duration || null,
      best_season: props.attraction?.best_season || '',
      is_featured: props.attraction?.is_featured ?? false,
      is_active: props.attraction?.is_active ?? true,
    })

    // Campos de array como strings para el formulario
    const amenitiesString = ref(
      Array.isArray(props.attraction?.amenities) && props.attraction.amenities.length > 0
        ? props.attraction.amenities.join(', ') 
        : ''
    )
    
    const restrictionsString = ref(
      Array.isArray(props.attraction?.restrictions) && props.attraction.restrictions.length > 0
        ? props.attraction.restrictions.join(', ') 
        : ''
    )

    // Campos de horarios simplificados
    const openingTime = ref(
      props.attraction?.opening_hours?.open || ''
    )
    
    const closingTime = ref(
      props.attraction?.opening_hours?.close || ''
    )

    // Manejo de imágenes
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
          const reader = new FileReader()
          reader.onload = (e) => {
            imagesPreviews.value.push({
              file: file,
              preview: e.target.result
            })
          }
          reader.readAsDataURL(file)
        }
      })
    }

    const removeNewImage = (index) => {
      imagesPreviews.value.splice(index, 1)
    }

    const removeExistingImage = (mediaId) => {
      if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
        // Aquí podrías hacer una llamada API para eliminar la imagen
        console.log('Eliminar imagen con ID:', mediaId)
      }
    }

    const updateAttraction = async () => {
      processing.value = true
      errors.value = {}

      try {
        // Preparar datos con campos complejos
        const formData = {
          ...form,
          // Convertir strings a arrays
          amenities: amenitiesString.value 
            ? amenitiesString.value.split(',').map(item => item.trim()).filter(item => item) 
            : [],
          restrictions: restrictionsString.value 
            ? restrictionsString.value.split(',').map(item => item.trim()).filter(item => item) 
            : [],
          // Estructurar horarios
          opening_hours: {
            open: openingTime.value || null,
            close: closingTime.value || null
          }
        }

        const response = await axios.put(`/admin/attractions/${props.attraction.id}`, formData)
        alert('Atractivo actualizado exitosamente')
      } catch (error) {
        console.error('Error al actualizar atractivo:', error)
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          alert('Error al actualizar el atractivo')
        }
      } finally {
        processing.value = false
      }
    }

    // Map related functions
    const openInGoogleMaps = () => {
      const lat = parseFloat(form.latitude)
      const lng = parseFloat(form.longitude)
      const url = `https://www.google.com/maps?q=${lat},${lng}`
      window.open(url, '_blank')
    }

    // Validate coordinates
    const isValidCoordinates = () => {
      const lat = parseFloat(form.latitude)
      const lng = parseFloat(form.longitude)
      
      return (
        !isNaN(lat) && 
        !isNaN(lng) && 
        lat >= -90 && 
        lat <= 90 && 
        lng >= -180 && 
        lng <= 180
      )
    }

    return {
      form,
      processing,
      errors,
      amenitiesString,
      restrictionsString,
      openingTime,
      closingTime,
      imagesPreviews,
      handleFileSelect,
      handleDrop,
      removeNewImage,
      removeExistingImage,
      updateAttraction,
      openInGoogleMaps,
      isValidCoordinates
    }
  }
}
</script>