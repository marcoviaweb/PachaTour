<template>
  <Head title="Editar Departamento - Admin" />

  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Departamento: {{ department?.name || 'Cargando...' }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Modifica la información del departamento
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            v-if="department?.slug"
            :href="route('admin.departments.show', department.slug)"
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
      <form @submit.prevent="updateDepartment" class="space-y-8">
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

              <!-- Area -->
              <div>
                <label for="area_km2" class="block text-sm font-medium text-gray-700">
                  Área (km²)
                </label>
                <input
                  id="area_km2"
                  v-model.number="form.area_km2"
                  type="number"
                  step="0.01"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.area_km2 }"
                  placeholder="370621"
                />
                <p v-if="errors.area_km2" class="mt-1 text-sm text-red-600">{{ errors.area_km2 }}</p>
              </div>

              <!-- Climate -->
              <div>
                <label for="climate" class="block text-sm font-medium text-gray-700">
                  Clima
                </label>
                <input
                  id="climate"
                  v-model="form.climate"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-300': errors.climate }"
                  placeholder="Tropical, húmedo"
                />
                <p v-if="errors.climate" class="mt-1 text-sm text-red-600">{{ errors.climate }}</p>
              </div>
            </div>

            <!-- Languages -->
            <div>
              <label for="languages" class="block text-sm font-medium text-gray-700">
                Idiomas Principales
              </label>
              <div class="mt-2 space-y-2">
                <div v-for="(language, index) in form.languages" :key="index" class="flex items-center space-x-2">
                  <input
                    v-model="form.languages[index]"
                    type="text"
                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                    placeholder="Ej: Español, Quechua"
                  />
                  <button
                    type="button"
                    @click="removeLanguage(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <XMarkIcon class="h-5 w-5" />
                  </button>
                </div>
                <button
                  type="button"
                  @click="addLanguage"
                  class="text-green-600 hover:text-green-800 text-sm font-medium"
                >
                  + Agregar idioma
                </button>
              </div>
              <p v-if="errors.languages" class="mt-1 text-sm text-red-600">{{ errors.languages }}</p>
            </div>
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
import { Head, Link } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import axios from 'axios'
import {
  ArrowLeftIcon,
  EyeIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

export default {
  name: 'EditDepartment',
  components: {
    Head,
    Link,
    AdminLayout,
    ArrowLeftIcon,
    EyeIcon,
    XMarkIcon,
  },
  props: {
    department: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const processing = ref(false)
    const errors = ref({})

    // Initialize form directly with props (immediate assignment)
    const form = reactive({
      name: props.department?.name || '',
      capital: props.department?.capital || '',
      description: props.department?.description || '',
      short_description: props.department?.short_description || '',
      latitude: props.department?.latitude || null,
      longitude: props.department?.longitude || null,
      population: props.department?.population || null,
      area_km2: props.department?.area_km2 || null,
      climate: props.department?.climate || '',
      languages: props.department?.languages?.length > 0 ? [...props.department.languages] : [''],
      is_active: props.department?.is_active ?? true,
      sort_order: props.department?.sort_order || 0
    })

    const updateDepartment = async () => {
      processing.value = true
      errors.value = {}

      try {
        const response = await axios.put(`/admin/departments/${props.department.slug}`, form)
        alert('Departamento actualizado exitosamente')
      } catch (error) {
        console.error('Error al actualizar departamento:', error)
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          alert('Error al actualizar el departamento')
        }
      } finally {
        processing.value = false
      }
    }

    const addLanguage = () => {
      form.languages.push('')
    }

    const removeLanguage = (index) => {
      form.languages.splice(index, 1)
    }

    return {
      processing,
      errors,
      form,
      addLanguage,
      removeLanguage,
      updateDepartment
    }
  }
}
</script>