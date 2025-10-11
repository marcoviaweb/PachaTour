<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true"
        @click="$emit('close')"
      ></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                {{ getModalTitle }}
              </h3>
              <p class="text-sm text-gray-600 mt-1">
                Inicia sesión para planificar tu visita
              </p>
            </div>
            <button 
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Login Form -->
          <form v-if="mode === 'login'" @submit.prevent="handleLogin">
            <div class="space-y-4">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                  Correo Electrónico *
                </label>
                <input
                  id="email"
                  v-model="loginForm.email"
                  type="email"
                  required
                  @blur="validateLoginEmail"
                  @input="clearError('email')"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': validationErrors.email || errors.email }"
                >
                <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">{{ validationErrors.email }}</p>
                <p v-else-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                  Contraseña *
                </label>
                <div class="relative">
                  <input
                    id="password"
                    v-model="loginForm.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    @blur="validateLoginPassword"
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
                <p v-if="validationErrors.password" class="mt-1 text-sm text-red-600">{{ validationErrors.password }}</p>
                <p v-else-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <input
                    id="remember"
                    v-model="loginForm.remember"
                    type="checkbox"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  >
                  <label for="remember" class="ml-2 block text-sm text-gray-900">
                    Recordarme
                  </label>
                </div>
                <button
                  type="button"
                  @click="switchToForgotPassword"
                  class="text-sm text-green-600 hover:text-green-500"
                >
                  ¿Olvidaste tu contraseña?
                </button>
              </div>
            </div>

            <!-- Social Login -->
            <div class="mt-6">
              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center text-sm">
                  <span class="px-2 bg-white text-gray-500">O continúa con</span>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-2 gap-3">
                <button
                  type="button"
                  @click="handleSocialLogin('google')"
                  :disabled="processing"
                  class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                  </svg>
                  <span class="ml-2">Google</span>
                </button>

                <button
                  type="button"
                  @click="handleSocialLogin('facebook')"
                  :disabled="processing"
                  class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                  <span class="ml-2">Facebook</span>
                </button>
              </div>
            </div>

            <div class="mt-6">
              <button
                type="submit"
                :disabled="!isLoginFormValid || processing"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ processing ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
              </button>
            </div>
          </form>

          <!-- Register Form -->
          <form v-else-if="mode === 'register'" @submit.prevent="handleRegister">
            <div class="space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Nombre Completo *
                </label>
                <input
                  id="name"
                  v-model="registerForm.name"
                  type="text"
                  required
                  @blur="validateName"
                  @input="clearError('name')"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': validationErrors.name || errors.name }"
                >
                <p v-if="validationErrors.name" class="mt-1 text-sm text-red-600">{{ validationErrors.name }}</p>
                <p v-else-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>

              <div>
                <label for="register-email" class="block text-sm font-medium text-gray-700">
                  Correo Electrónico *
                </label>
                <input
                  id="register-email"
                  v-model="registerForm.email"
                  type="email"
                  required
                  @blur="validateRegisterEmail"
                  @input="clearError('email')"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': validationErrors.email || errors.email }"
                >
                <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">{{ validationErrors.email }}</p>
                <p v-else-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>

              <div>
                <label for="register-password" class="block text-sm font-medium text-gray-700">
                  Contraseña *
                </label>
                <div class="relative">
                  <input
                    id="register-password"
                    v-model="registerForm.password"
                    :type="showRegisterPassword ? 'text' : 'password'"
                    required
                    @blur="validateRegisterPassword"
                    @input="clearError('password')"
                    class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    :class="{ 'border-red-500': validationErrors.password || errors.password }"
                  >
                  <button
                    type="button"
                    @click="showRegisterPassword = !showRegisterPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    <svg v-if="showRegisterPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <label for="password-confirmation" class="block text-sm font-medium text-gray-700">
                  Confirmar Contraseña *
                </label>
                <div class="relative">
                  <input
                    id="password-confirmation"
                    v-model="registerForm.password_confirmation"
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

              <div>
                <label class="flex items-center">
                  <input
                    v-model="registerForm.terms_accepted"
                    type="checkbox"
                    required
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  >
                  <span class="ml-2 text-sm text-gray-900">
                    Acepto los 
                    <a href="#" class="text-green-600 hover:text-green-500">términos y condiciones</a>
                    y la 
                    <a href="#" class="text-green-600 hover:text-green-500">política de privacidad</a>
                  </span>
                </label>
              </div>
            </div>

            <!-- Social Registration -->
            <div class="mt-6">
              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center text-sm">
                  <span class="px-2 bg-white text-gray-500">O regístrate con</span>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-2 gap-3">
                <button
                  type="button"
                  @click="handleSocialLogin('google')"
                  :disabled="processing"
                  class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                  </svg>
                  <span class="ml-2">Google</span>
                </button>

                <button
                  type="button"
                  @click="handleSocialLogin('facebook')"
                  :disabled="processing"
                  class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                  <span class="ml-2">Facebook</span>
                </button>
              </div>
            </div>

            <div class="mt-6">
              <button
                type="submit"
                :disabled="!isRegisterFormValid || processing"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ processing ? 'Creando cuenta...' : 'Crear Cuenta' }}
              </button>
            </div>
          </form>

          <!-- Forgot Password Form -->
          <form v-else-if="mode === 'forgot-password'" @submit.prevent="handleForgotPassword">
            <div class="text-center mb-6">
              <p class="text-sm text-gray-600">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
              </p>
            </div>

            <div class="space-y-4">
              <div>
                <label for="forgot-email" class="block text-sm font-medium text-gray-700">
                  Correo Electrónico *
                </label>
                <input
                  id="forgot-email"
                  v-model="forgotPasswordForm.email"
                  type="email"
                  required
                  @blur="validateForgotEmail"
                  @input="clearError('email')"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': validationErrors.email || errors.email }"
                >
                <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">{{ validationErrors.email }}</p>
                <p v-else-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>
            </div>

            <div class="mt-6">
              <button
                type="submit"
                :disabled="!isForgotPasswordFormValid || processing"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ processing ? 'Enviando...' : 'Enviar Enlace de Recuperación' }}
              </button>
            </div>

            <div class="mt-4 text-center">
              <button
                type="button"
                @click="switchMode('login')"
                class="text-sm text-green-600 hover:text-green-500"
              >
                ← Volver al inicio de sesión
              </button>
            </div>
          </form>

          <!-- Switch mode -->
          <div v-if="mode !== 'forgot-password'" class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              {{ mode === 'login' ? '¿No tienes cuenta?' : '¿Ya tienes cuenta?' }}
              <button
                @click="switchMode(mode === 'login' ? 'register' : 'login')"
                class="font-medium text-green-600 hover:text-green-500"
              >
                {{ mode === 'login' ? 'Regístrate aquí' : 'Inicia sesión aquí' }}
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export default {
  name: 'AuthModal',
  props: {
    mode: {
      type: String,
      default: 'login',
      validator: (value) => ['login', 'register', 'forgot-password'].includes(value)
    }
  },
  emits: ['close', 'switch-mode', 'auth-success'],
  setup(props, { emit }) {
    const processing = ref(false)
    const errors = ref({})
    const validationErrors = ref({})
    const showPassword = ref(false)
    const showRegisterPassword = ref(false)
    const showConfirmPassword = ref(false)

    const loginForm = reactive({
      email: '',
      password: '',
      remember: false
    })

    const registerForm = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      terms_accepted: false
    })

    const forgotPasswordForm = reactive({
      email: ''
    })

    // Validation functions
    const validateEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!email) return 'El correo electrónico es obligatorio'
      if (!emailRegex.test(email)) return 'Ingresa un correo electrónico válido'
      return null
    }

    const validatePassword = (password) => {
      if (!password) return 'La contraseña es obligatoria'
      if (password.length < 8) return 'La contraseña debe tener al menos 8 caracteres'
      return null
    }

    const validateRegisterPassword = () => {
      const password = registerForm.password
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

    const validateName = () => {
      const name = registerForm.name.trim()
      if (!name) {
        validationErrors.value.name = 'El nombre es obligatorio'
      } else if (name.length < 2) {
        validationErrors.value.name = 'El nombre debe tener al menos 2 caracteres'
      } else if (name.length > 100) {
        validationErrors.value.name = 'El nombre no puede exceder 100 caracteres'
      } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name)) {
        validationErrors.value.name = 'El nombre solo puede contener letras y espacios'
      } else {
        delete validationErrors.value.name
      }
    }

    const validateLoginEmail = () => {
      const error = validateEmail(loginForm.email)
      if (error) {
        validationErrors.value.email = error
      } else {
        delete validationErrors.value.email
      }
    }

    const validateRegisterEmail = () => {
      const error = validateEmail(registerForm.email)
      if (error) {
        validationErrors.value.email = error
      } else {
        delete validationErrors.value.email
      }
    }

    const validateForgotEmail = () => {
      const error = validateEmail(forgotPasswordForm.email)
      if (error) {
        validationErrors.value.email = error
      } else {
        delete validationErrors.value.email
      }
    }

    const validateLoginPassword = () => {
      const error = validatePassword(loginForm.password)
      if (error) {
        validationErrors.value.password = error
      } else {
        delete validationErrors.value.password
      }
    }

    const validatePasswordConfirmation = () => {
      if (!registerForm.password_confirmation) {
        validationErrors.value.password_confirmation = 'Confirma tu contraseña'
      } else if (registerForm.password !== registerForm.password_confirmation) {
        validationErrors.value.password_confirmation = 'Las contraseñas no coinciden'
      } else {
        delete validationErrors.value.password_confirmation
      }
    }

    const clearError = (field) => {
      delete validationErrors.value[field]
      delete errors.value[field]
    }

    // Computed properties
    const getModalTitle = computed(() => {
      switch (props.mode) {
        case 'login': return 'Iniciar Sesión'
        case 'register': return 'Crear Cuenta'
        case 'forgot-password': return 'Recuperar Contraseña'
        default: return 'Autenticación'
      }
    })

    const passwordStrength = computed(() => {
      const password = registerForm.password
      return {
        minLength: password.length >= 8,
        hasUppercase: /[A-Z]/.test(password),
        hasLowercase: /[a-z]/.test(password),
        hasNumber: /\d/.test(password)
      }
    })

    const isLoginFormValid = computed(() => {
      return loginForm.email && 
             loginForm.password && 
             !validationErrors.value.email && 
             !validationErrors.value.password
    })

    const isRegisterFormValid = computed(() => {
      return registerForm.name && 
             registerForm.email && 
             registerForm.password && 
             registerForm.password_confirmation && 
             registerForm.terms_accepted &&
             !validationErrors.value.name &&
             !validationErrors.value.email && 
             !validationErrors.value.password &&
             !validationErrors.value.password_confirmation &&
             passwordStrength.value.minLength &&
             passwordStrength.value.hasUppercase &&
             passwordStrength.value.hasLowercase &&
             passwordStrength.value.hasNumber
    })

    const isForgotPasswordFormValid = computed(() => {
      return forgotPasswordForm.email && !validationErrors.value.email
    })

    // Watch for password changes to validate confirmation
    watch(() => registerForm.password, () => {
      if (registerForm.password_confirmation) {
        validatePasswordConfirmation()
      }
    })

    // Form handlers
    const handleLogin = () => {
      // Validate all fields before submitting
      validateLoginEmail()
      validateLoginPassword()
      
      if (!isLoginFormValid.value) return

      processing.value = true
      errors.value = {}

      router.post('/login', loginForm, {
        onSuccess: () => {
          emit('auth-success')
          emit('close')
        },
        onError: (err) => {
          errors.value = err
        },
        onFinish: () => {
          processing.value = false
        }
      })
    }

    const handleRegister = () => {
      // Validate all fields before submitting
      validateName()
      validateRegisterEmail()
      validateRegisterPassword()
      validatePasswordConfirmation()
      
      if (!isRegisterFormValid.value) return

      processing.value = true
      errors.value = {}

      router.post('/register', registerForm, {
        onSuccess: () => {
          emit('auth-success')
          emit('close')
        },
        onError: (err) => {
          errors.value = err
        },
        onFinish: () => {
          processing.value = false
        }
      })
    }

    const handleForgotPassword = () => {
      validateForgotEmail()
      
      if (!isForgotPasswordFormValid.value) return

      processing.value = true
      errors.value = {}

      router.post('/forgot-password', forgotPasswordForm, {
        onSuccess: () => {
          // Show success message and switch back to login
          alert('Se ha enviado un enlace de recuperación a tu correo electrónico')
          switchMode('login')
        },
        onError: (err) => {
          errors.value = err
        },
        onFinish: () => {
          processing.value = false
        }
      })
    }

    const handleSocialLogin = (provider) => {
      processing.value = true
      window.location.href = `/auth/${provider}/redirect`
    }

    const switchMode = (newMode) => {
      errors.value = {}
      validationErrors.value = {}
      emit('switch-mode', newMode)
    }

    const switchToForgotPassword = () => {
      switchMode('forgot-password')
    }

    return {
      processing,
      errors,
      validationErrors,
      showPassword,
      showRegisterPassword,
      showConfirmPassword,
      loginForm,
      registerForm,
      forgotPasswordForm,
      getModalTitle,
      passwordStrength,
      isLoginFormValid,
      isRegisterFormValid,
      isForgotPasswordFormValid,
      validateName,
      validateLoginEmail,
      validateRegisterEmail,
      validateForgotEmail,
      validateLoginPassword,
      validateRegisterPassword,
      validatePasswordConfirmation,
      clearError,
      handleLogin,
      handleRegister,
      handleForgotPassword,
      handleSocialLogin,
      switchMode,
      switchToForgotPassword
    }
  }
}
</script>