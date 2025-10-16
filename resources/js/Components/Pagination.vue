<template>
  <nav class="flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
      <!-- Mobile pagination -->
      <Link
        v-if="links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
      >
        Anterior
      </Link>
      <Link
        v-if="links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
      >
        Siguiente
      </Link>
    </div>
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">{{ from }}</span>
          a
          <span class="font-medium">{{ to }}</span>
          de
          <span class="font-medium">{{ total }}</span>
          resultados
        </p>
      </div>
      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <template v-for="(link, index) in links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                link.active
                  ? 'z-10 bg-green-50 border-green-500 text-green-600'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                index === 0 ? 'rounded-l-md' : '',
                index === links.length - 1 ? 'rounded-r-md' : ''
              ]"
              v-html="link.label"
            />
            <span
              v-else
              :class="[
                'relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-default',
                index === 0 ? 'rounded-l-md' : '',
                index === links.length - 1 ? 'rounded-r-md' : ''
              ]"
              v-html="link.label"
            />
          </template>
        </nav>
      </div>
    </div>
  </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

export default {
  components: {
    Link,
  },

  props: {
    links: Array,
    from: Number,
    to: Number,
    total: Number,
  },

  setup(props) {
    return {}
  },
}
</script>