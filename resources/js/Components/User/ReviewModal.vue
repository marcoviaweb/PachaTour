<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ isEditing ? 'Editar Reseña' : 'Escribir Reseña' }}
            </h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <form @submit.prevent="handleSubmit" class="bg-white">
          <div class="px-6 py-4 space-y-6">
            <!-- Tour/Attraction Info -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-2">
                {{ attractionName }}
              </h4>
              <div v-if="booking" class="text-sm text-gray-600 space-y-1">
                <p><span class="font-medium">Tour:</span> {{ booking.tour_name }}</p>
                <p><span class="font-medium">Fecha:</span> {{ formatDate(booking.tour_date) }}</p>
              </div>
            </div>

            <!-- Rating -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Calificación *
              </label>
              <div class="flex items-center space-x-2">
                <div class="flex space-x-1">
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    @click="setRating(star)"
                    @mouseover="hoverRating = star"
                    @mouseleave="hoverRating = 0"
                    :class="[
                      'w-8 h-8 transition-colors',
                      (hoverRating >= star || form.rating >= star) 
                        ? 'text-yellow-400' 
                        : 'text-gray-300'
                    ]"
                  >
                    <svg fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </button>
                </div>
                <span class="text-sm text-gray-600 ml-2">
                  {{ getRatingText(hoverRating || form.rating) }}
                </span>
              </div>
              <p v-if="errors.rating" class="mt-1 text-sm text-red-600">{{ errors.rating }}</p>
            </div>

            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Título de la reseña
              </label>
              <input
                v-model="form.title"
                type="text"
                maxlength="100"
                placeholder="Resumen de tu experiencia..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.title }"
              />
              <p class="mt-1 text-xs text-gray-500">{{ form.title.length }}/100 caracteres</p>
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
            </div>

            <!-- Comment -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tu reseña *
              </label>
              <textarea
                v-model="form.comment"
                rows="5"
                maxlength="1000"
                required
                placeholder="Comparte tu experiencia: ¿Qué te gustó más? ¿Qué recomendarías a otros viajeros? ¿Cómo fue el servicio?"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.comment }"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">{{ form.comment.length }}/1000 caracteres</p>
              <p v-if="errors.comment" class="mt-1 text-sm text-red-600">{{ errors.comment }}</p>
            </div>

            <!-- Review Guidelines -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h5 class="font-medium text-blue-900 mb-2">Consejos para una buena reseña</h5>
              <ul class="text-sm text-blue-700 space-y-1">
                <li>• Sé específico sobre tu experiencia</li>
                <li>• Menciona aspectos como el guía, la organización, y los atractivos</li>
                <li>• Incluye consejos útiles para otros viajeros</li>
                <li>• Mantén un tono respetuoso y constructivo</li>
                <li>• Evita información personal o datos de contacto</li>
              </ul>
            </div>

            <!-- Moderation Notice -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-sm text-yellow-800 font-medium">Moderación de contenido</p>
                  <p class="text-sm text-yellow-700 mt-1">
                    Tu reseña será revisada por nuestro equipo antes de ser publicada. 
                    Esto puede tomar hasta 24 horas.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors text-sm font-medium"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!canSubmit || submitting"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">{{ isEditing ? 'Actualizando...' : 'Publicando...' }}</span>
              <span v-else>{{ isEditing ? 'Actualizar Reseña' : 'Publicar Reseña' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReviewModal',
  props: {
    booking: {
      type: Object,
      default: null
    },
    review: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      form: {
        rating: this.review?.rating || 0,
        title: this.review?.title || '',
        comment: this.review?.comment || ''
      },
      hoverRating: 0,
      submitting: false,
      errors: {}
    }
  },
  computed: {
    isEditing() {
      return !!this.review
    },

    attractionName() {
      if (this.review) {
        return this.review.attraction_name
      }
      if (this.booking) {
        return this.booking.attraction_name || this.booking.tour_name
      }
      return 'Atractivo'
    },

    canSubmit() {
      return this.form.rating > 0 && 
             this.form.comment.trim().length >= 10 &&
             !this.submitting
    }
  },
  methods: {
    setRating(rating) {
      this.form.rating = rating
      this.errors.rating = ''
    },

    getRatingText(rating) {
      const texts = {
        1: 'Muy malo',
        2: 'Malo',
        3: 'Regular',
        4: 'Bueno',
        5: 'Excelente'
      }
      return texts[rating] || 'Selecciona una calificación'
    },

    async handleSubmit() {
      this.errors = {}
      this.submitting = true

      try {
        // Validate form
        if (!this.validateForm()) {
          this.submitting = false
          return
        }

        // Prepare data
        const reviewData = {
          rating: this.form.rating,
          title: this.form.title.trim(),
          comment: this.form.comment.trim()
        }

        // Add attraction_id if creating new review from booking
        if (this.booking && !this.isEditing) {
          reviewData.attraction_id = this.booking.attraction_id
        }

        this.$emit('save', reviewData)
      } catch (error) {
        console.error('Error submitting review:', error)
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
        } else {
          this.errors = { general: 'Error al procesar la reseña' }
        }
      } finally {
        this.submitting = false
      }
    },

    validateForm() {
      const errors = {}

      if (!this.form.rating || this.form.rating < 1 || this.form.rating > 5) {
        errors.rating = 'Debes seleccionar una calificación del 1 al 5'
      }

      if (!this.form.comment.trim()) {
        errors.comment = 'El comentario es requerido'
      } else if (this.form.comment.trim().length < 10) {
        errors.comment = 'El comentario debe tener al menos 10 caracteres'
      } else if (this.form.comment.length > 1000) {
        errors.comment = 'El comentario no puede exceder 1000 caracteres'
      }

      if (this.form.title.length > 100) {
        errors.title = 'El título no puede exceder 100 caracteres'
      }

      // Check for inappropriate content (basic validation)
      const inappropriateWords = ['spam', 'fake', 'falso']
      const content = (this.form.title + ' ' + this.form.comment).toLowerCase()
      
      if (inappropriateWords.some(word => content.includes(word))) {
        errors.comment = 'El contenido contiene palabras no permitidas'
      }

      this.errors = errors
      return Object.keys(errors).length === 0
    },

    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  }
}
</script>