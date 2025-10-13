<template>
  <Head title="Mi Perfil - Pacha Tour" />
  
  <AppLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mi Perfil</h1>
        <p class="text-gray-600 mt-1">Gestiona tu información personal y preferencias</p>
      </div>

      <!-- Profile Form -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6">
          <!-- Información del usuario autenticado (solo lectura) -->
          <div class="mb-6 bg-green-50 rounded-lg p-4 border border-green-200">
            <h4 class="text-sm font-medium text-green-800 mb-3">Información de contacto</h4>
            <div class="space-y-2">
              <div class="flex items-center">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm text-gray-700">Nombre:</span>
                <span class="text-sm font-medium text-gray-900 ml-2">{{ form.name }} {{ form.last_name }}</span>
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm text-gray-700">Email:</span>
                <span class="text-sm font-medium text-gray-900 ml-2">{{ form.email }}</span>
              </div>
            </div>
          </div>

          <form @submit.prevent="updateProfile">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

              <!-- Phone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                  Teléfono
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.phone }"
                />
                <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
              </div>

              <!-- Birth Date -->
              <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                  Fecha de Nacimiento
                </label>
                <input
                  id="birth_date"
                  v-model="form.birth_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.birth_date }"
                />
                <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600">{{ errors.birth_date }}</p>
              </div>

              <!-- Gender -->
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                  Género
                </label>
                <select
                  id="gender"
                  v-model="form.gender"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.gender }"
                >
                  <option value="">Seleccionar...</option>
                  <option value="male">Masculino</option>
                  <option value="female">Femenino</option>
                  <option value="other">Otro</option>
                  <option value="prefer_not_to_say">Prefiero no decir</option>
                </select>
                <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
              </div>

              <!-- Nationality -->
              <div>
                <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">
                  Nacionalidad
                </label>
                <input
                  id="nationality"
                  v-model="form.nationality"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.nationality }"
                />
                <p v-if="errors.nationality" class="mt-1 text-sm text-red-600">{{ errors.nationality }}</p>
              </div>

              <!-- Country -->
              <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                  País
                </label>
                <input
                  id="country"
                  v-model="form.country"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.country }"
                />
                <p v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country }}</p>
              </div>

              <!-- City -->
              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                  Ciudad
                </label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.city }"
                />
                <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
              </div>

              <!-- Preferred Language -->
              <div>
                <label for="preferred_language" class="block text-sm font-medium text-gray-700 mb-2">
                  Idioma Preferido
                </label>
                <select
                  id="preferred_language"
                  v-model="form.preferred_language"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.preferred_language }"
                >
                  <option value="">Seleccionar...</option>
                  <option value="es">Español</option>
                  <option value="en">English</option>
                </select>
                <p v-if="errors.preferred_language" class="mt-1 text-sm text-red-600">{{ errors.preferred_language }}</p>
              </div>
            </div>

            <!-- Bio -->
            <div class="mt-6">
              <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                Biografía
              </label>
              <textarea
                id="bio"
                v-model="form.bio"
                rows="4"
                maxlength="500"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.bio }"
                placeholder="Cuéntanos un poco sobre ti..."
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">{{ form.bio?.length || 0 }}/500 caracteres</p>
              <p v-if="errors.bio" class="mt-1 text-sm text-red-600">{{ errors.bio }}</p>
            </div>

            <!-- Preferences -->
            <div class="mt-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Preferencias</h3>
              <div class="space-y-4">
                <div class="flex items-center">
                  <input
                    id="newsletter_subscription"
                    v-model="form.newsletter_subscription"
                    type="checkbox"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  />
                  <label for="newsletter_subscription" class="ml-2 block text-sm text-gray-900">
                    Recibir boletín informativo con ofertas y novedades
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="marketing_emails"
                    v-model="form.marketing_emails"
                    type="checkbox"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  />
                  <label for="marketing_emails" class="ml-2 block text-sm text-gray-900">
                    Recibir emails promocionales y de marketing
                  </label>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
              <button
                type="submit"
                :disabled="processing"
                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Guardando...</span>
                <span v-else>Guardar Cambios</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Change Password Section -->
      <div class="bg-white rounded-lg shadow mt-8">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Contraseña</h3>
          
          <form @submit.prevent="changePassword">
            <div class="space-y-4">
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Contraseña Actual *
                </label>
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': passwordErrors.current_password }"
                />
                <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password }}</p>
              </div>

              <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Nueva Contraseña *
                </label>
                <input
                  id="new_password"
                  v-model="passwordForm.new_password"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': passwordErrors.new_password }"
                />
                <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password }}</p>
              </div>

              <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirmar Nueva Contraseña *
                </label>
                <input
                  id="new_password_confirmation"
                  v-model="passwordForm.new_password_confirmation"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': passwordErrors.new_password_confirmation }"
                />
                <p v-if="passwordErrors.new_password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password_confirmation }}</p>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button
                type="submit"
                :disabled="processingPassword"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processingPassword">Cambiando...</span>
                <span v-else>Cambiar Contraseña</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

export default {
  name: 'UserProfile',
  components: {
    Head,
    AppLayout
  },
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const processing = ref(false)
    const processingPassword = ref(false)
    const errors = ref({})
    const passwordErrors = ref({})

    const form = reactive({
      name: '',
      last_name: '',
      email: '',
      phone: '',
      birth_date: '',
      gender: '',
      nationality: '',
      country: '',
      city: '',
      preferred_language: '',
      bio: '',
      newsletter_subscription: false,
      marketing_emails: false
    })

    const passwordForm = reactive({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })

    const loadProfile = async () => {
      try {
        // First load from props
        Object.keys(form).forEach(key => {
          if (props.user[key] !== undefined && props.user[key] !== null) {
            // Handle boolean fields explicitly
            if (key === 'newsletter_subscription' || key === 'marketing_emails') {
              form[key] = Boolean(props.user[key])
            } else if (key === 'birth_date') {
              // Format date for HTML date input (YYYY-MM-DD)
              const dateValue = props.user[key]
              if (dateValue) {
                // Convert timestamp/datetime to YYYY-MM-DD format
                const date = new Date(dateValue)
                if (!isNaN(date.getTime())) {
                  form[key] = date.toISOString().split('T')[0]
                } else {
                  form[key] = ''
                }
              } else {
                form[key] = ''
              }
            } else {
              form[key] = props.user[key]
            }
          }
        })

        // Then fetch full profile data from API
        const response = await axios.get('/api/user/profile')
        const user = response.data.user
        
        Object.keys(form).forEach(key => {
          if (user[key] !== undefined && user[key] !== null) {
            // Handle boolean fields explicitly
            if (key === 'newsletter_subscription' || key === 'marketing_emails') {
              form[key] = Boolean(user[key])
            } else if (key === 'birth_date') {
              // Format date for HTML date input (YYYY-MM-DD)
              const dateValue = user[key]
              if (dateValue) {
                // Convert timestamp/datetime to YYYY-MM-DD format
                const date = new Date(dateValue)
                if (!isNaN(date.getTime())) {
                  form[key] = date.toISOString().split('T')[0]
                } else {
                  form[key] = ''
                }
              } else {
                form[key] = ''
              }
            } else {
              form[key] = user[key]
            }
          }
        })
      } catch (error) {
        console.error('Error loading profile:', error)
        // If API fails, still use props data
        Object.keys(form).forEach(key => {
          if (props.user[key] !== undefined && props.user[key] !== null) {
            // Handle boolean fields explicitly
            if (key === 'newsletter_subscription' || key === 'marketing_emails') {
              form[key] = Boolean(props.user[key])
            } else if (key === 'birth_date') {
              // Format date for HTML date input (YYYY-MM-DD)
              const dateValue = props.user[key]
              if (dateValue) {
                // Convert timestamp/datetime to YYYY-MM-DD format
                const date = new Date(dateValue)
                if (!isNaN(date.getTime())) {
                  form[key] = date.toISOString().split('T')[0]
                } else {
                  form[key] = ''
                }
              } else {
                form[key] = ''
              }
            } else {
              form[key] = props.user[key]
            }
          }
        })
      }
    }

    const updateProfile = async () => {
      processing.value = true
      errors.value = {}

      try {
        const response = await axios.put('/api/user/profile', form)
        alert('Perfil actualizado exitosamente')
      } catch (error) {
        console.error('Error al actualizar perfil:', error)
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          alert('Error al actualizar el perfil')
        }
      } finally {
        processing.value = false
      }
    }

    const changePassword = async () => {
      processingPassword.value = true
      passwordErrors.value = {}

      try {
        await axios.post('/api/user/change-password', passwordForm)
        alert('Contraseña cambiada exitosamente')
        
        // Clear form
        passwordForm.current_password = ''
        passwordForm.new_password = ''
        passwordForm.new_password_confirmation = ''
      } catch (error) {
        if (error.response?.data?.errors) {
          passwordErrors.value = error.response.data.errors
        } else {
          alert('Error al cambiar la contraseña')
        }
      } finally {
        processingPassword.value = false
      }
    }

    onMounted(() => {
      loadProfile()
    })

    return {
      processing,
      processingPassword,
      errors,
      passwordErrors,
      form,
      passwordForm,
      updateProfile,
      changePassword
    }
  }
}
</script>