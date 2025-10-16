<template>
  <Link
    :href="href"
    :method="method"
    :as="as"
    :class="[
      'block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition duration-150 ease-in-out focus:outline-none',
      active
        ? 'border-green-400 text-green-700 bg-green-50 focus:text-green-800 focus:bg-green-100 focus:border-green-700'
        : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300'
    ]"
  >
    <slot />
  </Link>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  components: {
    Link,
  },

  props: {
    href: {
      type: String,
      required: true,
    },
    method: String,
    as: String,
    active: {
      type: Boolean,
      default: false,
    },
  },
}
</script>