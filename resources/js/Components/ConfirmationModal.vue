<template>
  <Modal :show="show" @close="$emit('close')" max-width="md">
    <div class="p-6">
      <div class="flex items-center">
        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
          <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
        </div>
      </div>

      <div class="mt-3 text-center sm:mt-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          <slot name="title" />
        </h3>
        <div class="mt-2">
          <p class="text-sm text-gray-500">
            <slot name="content" />
          </p>
        </div>
      </div>

      <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
        <button
          type="button"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm"
          @click="$emit('confirm')"
        >
          Confirmar
        </button>
        <button
          type="button"
          class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
          @click="$emit('close')"
        >
          Cancelar
        </button>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from '@/Components/Modal.vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

export default {
  components: {
    Modal,
    ExclamationTriangleIcon,
  },

  props: {
    show: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['close', 'confirm'],
}
</script>