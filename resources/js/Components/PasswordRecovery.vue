<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-green-100">
          <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 015.743-7.743z" />
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Restablecer Contraseña
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Ingresa tu nueva contraseña para completar el proceso de recuperación
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleResetPassword">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Correo Electrónico
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              readonly
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Nueva Contraseña *
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                @blur="validatePassword"
                @input="clearError('password')"
                class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': validationErrors.password || errors.password }"
              >
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <svg v-if="showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L8.464 8.464m5.656 5.656l1.415 1.415m-1.415-1.415l1.415 1.415M14.828 14.828L16.243 16.243" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
            <div class="mt-1">
              <div class="text-xs text-gray-500">
                La contraseña debe tener:
              </div>
              <ul class="text-xs text-gray-500 mt-1 space-y-1">
                <li :class="passwordStrength.minLength ? 'text-green-600' : 'text-red-500'">
                  ✓ Al menos 8 caracteres
                </li>
                <li :class="passwordStrength.hasUppercase ? 'text-green-600' : 'text-red-500'">
                  ✓ Una letra mayúscula
                </li>
                <li :class="passwordStrength.hasLowercase ? 'text-green-600' : 'text-red-500'">
                  ✓ Una letra minúscula
                </li>
                <li :class="passwordStrength.hasNumber ? 'text-green-600' : 'text-red-500'">
                  ✓ Un número
                </li>
              </ul>
            </div>
            <p v-if="validationErrors.password" class="mt-1 text-sm text-red-600">{{ validationErrors.password }}</p>
            <p v-else-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmar Nueva Contraseña *
            </label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                @blur="validatePasswordConfirmation"
                @input="clearError('password_confirmation')"
                class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': validationErrors.password_confirmation }"
              >
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <svg v-if="showConfirmPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L8.464 8.464m5.656 5.656l1.415 1.415m-1.415-1.415l1.415 1.415M14.828 14.828L16.243 16.243" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
            <p v-if="validationErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ validationErrors.password_confirmation }}</p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="!isFormValid || processing"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ processing ? 'Restableciendo...' : 'Restablecer Contraseña' }}
          </button>
        </div>

        <div class="text-center">
          <a href="/" class="text-sm text-green-600 hover:text-green-500">
            ← Volver al inicio
          </a>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export default {
  name: 'PasswordRecovery',
  props: {
    token: {
      type: String,
      required: true
    },
    email: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const processing = ref(false)
    const errors = ref({})
    const validationErrors = ref({})
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)

    const form = reactive({
      token: props.token,
      email: props.email,
      password: '',
      password_confirmation: ''
    })

    const validatePassword = () => {
      const password = form.password
      if (!password) {
        validationErrors.value.password = 'La contraseña es obligatoria'
        return
      }
      
      const errors = []
      if (password.length < 8) errors.push('al menos 8 caracteres')
      if (!/[A-Z]/.test(password)) errors.push('una letra mayúscula')
      if (!/[a-z]/.test(password)) errors.push('una letra minúscula')
      if (!/\d/.test(password)) errors.push('un número')
      
      if (errors.length > 0) {
        validationErrors.value.password = `La contraseña debe contener ${errors.join(', ')}`
      } else {
        delete validationErrors.value.password
      }
    }

    const validatePasswordConfirmation = () => {
      if (!form.password_confirmation) {
        validationErrors.value.password_confirmation = 'Confirma tu contraseña'
      } else if (form.password !== form.password_confirmation) {
        validationErrors.value.password_confirmation = 'Las contraseñas no coinciden'
      } else {
        delete validationErrors.value.password_confirmation
      }
    }

    const clearError = (field) => {
      delete validationErrors.value[field]
      delete errors.value[field]
    }

    const passwordStrength = computed(() => {
      const password = form.password
      return {
        minLength: password.length >= 8,
        hasUppercase: /[A-Z]/.test(password),
        hasLowercase: /[a-z]/.test(password),
        hasNumber: /\d/.test(password)
      }
    })

    const isFormValid = computed(() => {
      return form.password && 
             form.password_confirmation && 
             !validationErrors.value.password &&
             !validationErrors.value.password_confirmation &&
             passwordStrength.value.minLength &&
             passwordStrength.value.hasUppercase &&
             passwordStrength.value.hasLowercase &&
             passwordStrength.value.hasNumber
    })

    // Watch for password changes to validate confirmation
    watch(() => form.password, () => {
      if (form.password_confirmation) {
        validatePasswordConfirmation()
      }
    })

    const handleResetPassword = () => {
      validatePassword()
      validatePasswordConfirmation()
      
      if (!isFormValid.value) return

      processing.value = true
      errors.value = {}

      router.post('/reset-password', form, {
        onSuccess: () => {
          alert('Tu contraseña ha sido restablecida exitosamente')
          router.visit('/')
        },
        onError: (err) => {
          errors.value = err
        },
        onFinish: () => {
          processing.value = false
        }
      })
    }

    return {
      processing,
      errors,
      validationErrors,
      showPassword,
      showConfirmPassword,
      form,
      passwordStrength,
      isFormValid,
      validatePassword,
      validatePasswordConfirmation,
      clearError,
      handleResetPassword
    }
  }
}
</script>