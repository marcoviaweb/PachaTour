<template>
  <div
    v-if="flash && Object.keys(flash).length > 0"
    class="fixed top-4 right-4 z-50 space-y-2"
  >
    <div
      v-for="(message, type) in flash"
      :key="type"
      class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <CheckCircleIcon
              v-if="type === 'success'"
              class="h-6 w-6 text-green-400"
              aria-hidden="true"
            />
            <ExclamationTriangleIcon
              v-else-if="type === 'warning'"
              class="h-6 w-6 text-yellow-400"
              aria-hidden="true"
            />
            <XCircleIcon
              v-else-if="type === 'error'"
              class="h-6 w-6 text-red-400"
              aria-hidden="true"
            />
            <InformationCircleIcon
              v-else
              class="h-6 w-6 text-blue-400"
              aria-hidden="true"
            />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900">
              {{ message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="removeFlash(type)"
            >
              <span class="sr-only">Cerrar</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
  },

  props: {
    flash: {
      type: Object,
      default: () => ({}),
    },
  },

  setup(props) {
    const flashMessages = ref({ ...props.flash })

    const removeFlash = (type) => {
      delete flashMessages.value[type]
    }

    // Auto-remove flash messages after 5 seconds
    onMounted(() => {
      Object.keys(flashMessages.value).forEach((type) => {
        setTimeout(() => {
          removeFlash(type)
        }, 5000)
      })
    })

    return {
      flashMessages,
      removeFlash,
    }
  },
}
</script>