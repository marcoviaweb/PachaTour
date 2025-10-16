<template>
  <div>
    <Link
      v-if="as === 'link'"
      :href="href"
      :class="classes"
    >
      <slot />
    </Link>

    <Link
      v-else-if="as === 'button' && method"
      :href="href"
      :method="method"
      :as="'button'"
      :class="classes"
    >
      <slot />
    </Link>

    <button
      v-else
      :type="as === 'button' ? 'button' : 'submit'"
      :class="classes"
      @click="$emit('click')"
    >
      <slot />
    </button>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

export default {
  components: {
    Link,
  },

  props: {
    href: String,
    method: String,
    as: {
      type: String,
      default: 'link',
    },
  },

  emits: ['click'],

  setup() {
    const classes = computed(() => {
      return 'block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out'
    })

    return {
      classes,
    }
  },
}
</script>