<template>
  <div class="fixed top-4 right-4 z-50 max-w-sm w-full">
    <div 
      class="bg-white rounded-lg shadow-lg border-l-4 p-4 transition-all duration-300 transform"
      :class="[
        typeClasses,
        'animate-slide-in-right'
      ]"
    >
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <!-- Success Icon -->
          <svg 
            v-if="type === 'success'" 
            class="h-5 w-5 text-green-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          
          <!-- Error Icon -->
          <svg 
            v-else-if="type === 'error'" 
            class="h-5 w-5 text-red-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          
          <!-- Warning Icon -->
          <svg 
            v-else-if="type === 'warning'" 
            class="h-5 w-5 text-yellow-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          
          <!-- Info Icon -->
          <svg 
            v-else 
            class="h-5 w-5 text-blue-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        
        <div class="ml-3 flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ message }}
          </p>
        </div>
        
        <div class="ml-4 flex-shrink-0 flex">
          <button 
            @click="$emit('close')"
            class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'NotificationToast',
  props: {
    type: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    message: {
      type: String,
      required: true
    }
  },
  emits: ['close'],
  setup(props) {
    const typeClasses = computed(() => {
      const classes = {
        success: 'border-green-400 bg-green-50',
        error: 'border-red-400 bg-red-50',
        warning: 'border-yellow-400 bg-yellow-50',
        info: 'border-blue-400 bg-blue-50'
      }
      return classes[props.type] || classes.info
    })

    return {
      typeClasses
    }
  }
}
</script>

<style scoped>
@keyframes slide-in-right {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in-right {
  animation: slide-in-right 0.3s ease-out;
}
</style>