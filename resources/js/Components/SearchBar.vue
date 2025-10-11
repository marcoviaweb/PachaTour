<template>
  <div class="relative w-full max-w-2xl mx-auto">
    <div class="relative">
      <input
        v-model="searchQuery"
        @input="handleInput"
        @focus="showSuggestions = true"
        @blur="handleBlur"
        @keydown.down="navigateDown"
        @keydown.up="navigateUp"
        @keydown.enter="selectSuggestion"
        @keydown.escape="hideSuggestions"
        type="text"
        :placeholder="placeholder"
        class="w-full px-4 py-2 pl-10 pr-4 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
      <div class="absolute inset-y-0 left-0 flex items-center pl-3">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute inset-y-0 right-0 flex items-center pr-3"
      >
        <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Suggestions dropdown -->
    <div
      v-if="showSuggestions && filteredSuggestions.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <div
        v-for="(suggestion, index) in filteredSuggestions"
        :key="suggestion.id"
        @mousedown="selectSuggestionByIndex(index)"
        :class="[
          'px-4 py-2 cursor-pointer hover:bg-gray-100',
          { 'bg-blue-50': index === selectedIndex }
        ]"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0 mr-3">
            <span
              :class="[
                'inline-block w-2 h-2 rounded-full',
                suggestion.type === 'attraction' ? 'bg-green-500' : 
                suggestion.type === 'department' ? 'bg-blue-500' : 'bg-purple-500'
              ]"
            ></span>
          </div>
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-900">
              {{ suggestion.name }}
            </div>
            <div class="text-xs text-gray-500">
              {{ suggestion.type === 'attraction' ? 'Atractivo' : 
                  suggestion.type === 'department' ? 'Departamento' : 'Tipo de turismo' }}
              <span v-if="suggestion.department"> - {{ suggestion.department }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading indicator -->
    <div
      v-if="loading"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg"
    >
      <div class="px-4 py-3 text-center text-gray-500">
        <svg class="inline w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Buscando...
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'SearchBar',
  props: {
    placeholder: {
      type: String,
      default: 'Buscar atractivos, departamentos...'
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue', 'search', 'select'],
  setup(props, { emit }) {
    const searchQuery = ref(props.modelValue)
    const suggestions = ref([])
    const showSuggestions = ref(false)
    const loading = ref(false)
    const selectedIndex = ref(-1)
    const searchTimeout = ref(null)

    const filteredSuggestions = computed(() => {
      if (!searchQuery.value || searchQuery.value.length < 2) {
        return []
      }
      return suggestions.value.slice(0, 8) // Limit to 8 suggestions
    })

    const handleInput = () => {
      emit('update:modelValue', searchQuery.value)
      
      if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
      }

      if (searchQuery.value.length < 2) {
        suggestions.value = []
        showSuggestions.value = false
        return
      }

      searchTimeout.value = setTimeout(() => {
        fetchSuggestions()
      }, 300) // Debounce for 300ms
    }

    const fetchSuggestions = async () => {
      if (!searchQuery.value || searchQuery.value.length < 2) return

      loading.value = true
      try {
        const response = await axios.get('/api/search/suggestions', {
          params: { q: searchQuery.value }
        })
        suggestions.value = response.data.data || []
        showSuggestions.value = true
        selectedIndex.value = -1
      } catch (error) {
        console.error('Error fetching suggestions:', error)
        suggestions.value = []
      } finally {
        loading.value = false
      }
    }

    const navigateDown = () => {
      if (selectedIndex.value < filteredSuggestions.value.length - 1) {
        selectedIndex.value++
      }
    }

    const navigateUp = () => {
      if (selectedIndex.value > 0) {
        selectedIndex.value--
      }
    }

    const selectSuggestion = () => {
      if (selectedIndex.value >= 0 && filteredSuggestions.value[selectedIndex.value]) {
        selectSuggestionByIndex(selectedIndex.value)
      } else {
        performSearch()
      }
    }

    const selectSuggestionByIndex = (index) => {
      const suggestion = filteredSuggestions.value[index]
      if (suggestion) {
        searchQuery.value = suggestion.name
        emit('update:modelValue', suggestion.name)
        emit('select', suggestion)
        hideSuggestions()
      }
    }

    const performSearch = () => {
      emit('search', searchQuery.value)
      hideSuggestions()
    }

    const hideSuggestions = () => {
      showSuggestions.value = false
      selectedIndex.value = -1
    }

    const handleBlur = () => {
      // Delay hiding suggestions to allow click events
      setTimeout(() => {
        hideSuggestions()
      }, 200)
    }

    const clearSearch = () => {
      searchQuery.value = ''
      emit('update:modelValue', '')
      suggestions.value = []
      hideSuggestions()
    }

    // Watch for external changes to modelValue
    watch(() => props.modelValue, (newValue) => {
      searchQuery.value = newValue
    })

    return {
      searchQuery,
      suggestions,
      filteredSuggestions,
      showSuggestions,
      loading,
      selectedIndex,
      handleInput,
      navigateDown,
      navigateUp,
      selectSuggestion,
      selectSuggestionByIndex,
      hideSuggestions,
      handleBlur,
      clearSearch
    }
  }
}
</script>