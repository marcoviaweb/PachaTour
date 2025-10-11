<template>
  <div class="relative">
    <div
      ref="mapContainer"
      :style="{ height: height }"
      class="w-full rounded-lg overflow-hidden shadow-md"
    >
      <!-- Fallback content while map loads -->
      <div
        v-if="!mapLoaded"
        class="flex items-center justify-center h-full bg-gray-100"
      >
        <div class="text-center">
          <svg class="inline w-8 h-8 mr-2 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-gray-600">Cargando mapa...</p>
        </div>
      </div>
    </div>

    <!-- Map Controls -->
    <div class="absolute top-4 right-4 flex flex-col space-y-2">
      <button
        @click="zoomIn"
        class="bg-white hover:bg-gray-50 border border-gray-300 rounded-md p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Acercar"
      >
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
      </button>
      <button
        @click="zoomOut"
        class="bg-white hover:bg-gray-50 border border-gray-300 rounded-md p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Alejar"
      >
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
        </svg>
      </button>
      <button
        @click="resetView"
        class="bg-white hover:bg-gray-50 border border-gray-300 rounded-md p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Vista inicial"
      >
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
        </svg>
      </button>
    </div>

    <!-- Legend -->
    <div class="absolute bottom-4 left-4 bg-white rounded-lg shadow-md p-3">
      <h4 class="text-sm font-semibold text-gray-800 mb-2">Leyenda</h4>
      <div class="space-y-1">
        <div class="flex items-center text-xs">
          <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
          <span class="text-gray-600">Atractivos turísticos</span>
        </div>
        <div class="flex items-center text-xs">
          <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
          <span class="text-gray-600">Departamentos</span>
        </div>
        <div class="flex items-center text-xs">
          <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
          <span class="text-gray-600">Tu ubicación</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'

export default {
  name: 'InteractiveMap',
  props: {
    attractions: {
      type: Array,
      default: () => []
    },
    center: {
      type: Object,
      default: () => ({ lat: -16.5, lng: -64.0 }) // Center of Bolivia
    },
    zoom: {
      type: Number,
      default: 6
    },
    height: {
      type: String,
      default: '400px'
    },
    showUserLocation: {
      type: Boolean,
      default: false
    }
  },
  emits: ['marker-click', 'map-click', 'bounds-change'],
  setup(props, { emit }) {
    const mapContainer = ref(null)
    const map = ref(null)
    const mapLoaded = ref(false)
    const markers = ref([])
    const userLocationMarker = ref(null)

    // Simulated map implementation (replace with actual map library like Leaflet or Google Maps)
    const initializeMap = async () => {
      try {
        // Simulate map loading delay
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // Create a simple map representation
        const mapElement = mapContainer.value
        if (!mapElement) return

        // Clear existing content
        mapElement.innerHTML = ''

        // Create SVG map
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
        svg.setAttribute('width', '100%')
        svg.setAttribute('height', '100%')
        svg.setAttribute('viewBox', '0 0 800 600')
        svg.style.background = '#e5f3ff'

        // Add Bolivia outline (simplified)
        const boliviaPath = document.createElementNS('http://www.w3.org/2000/svg', 'path')
        boliviaPath.setAttribute('d', 'M200,150 L600,150 L600,450 L200,450 Z')
        boliviaPath.setAttribute('fill', '#f0f9ff')
        boliviaPath.setAttribute('stroke', '#0369a1')
        boliviaPath.setAttribute('stroke-width', '2')
        svg.appendChild(boliviaPath)

        // Add department boundaries (simplified grid)
        for (let i = 1; i < 3; i++) {
          const line1 = document.createElementNS('http://www.w3.org/2000/svg', 'line')
          line1.setAttribute('x1', 200 + (i * 133))
          line1.setAttribute('y1', 150)
          line1.setAttribute('x2', 200 + (i * 133))
          line1.setAttribute('y2', 450)
          line1.setAttribute('stroke', '#94a3b8')
          line1.setAttribute('stroke-width', '1')
          svg.appendChild(line1)

          const line2 = document.createElementNS('http://www.w3.org/2000/svg', 'line')
          line2.setAttribute('x1', 200)
          line2.setAttribute('y1', 150 + (i * 100))
          line2.setAttribute('x2', 600)
          line2.setAttribute('y2', 150 + (i * 100))
          line2.setAttribute('stroke', '#94a3b8')
          line2.setAttribute('stroke-width', '1')
          svg.appendChild(line2)
        }

        mapElement.appendChild(svg)
        map.value = { svg, element: mapElement }
        mapLoaded.value = true

        // Add markers after map is loaded
        await nextTick()
        updateMarkers()

        // Add user location if enabled
        if (props.showUserLocation) {
          getUserLocation()
        }

      } catch (error) {
        console.error('Error initializing map:', error)
      }
    }

    const updateMarkers = () => {
      if (!map.value || !props.attractions.length) return

      // Clear existing markers
      clearMarkers()

      const svg = map.value.svg
      
      props.attractions.forEach((attraction, index) => {
        // Convert lat/lng to SVG coordinates (simplified)
        const x = 200 + ((attraction.longitude + 70) / 20) * 400 // Rough conversion
        const y = 150 + ((attraction.latitude + 25) / 20) * 300  // Rough conversion

        // Create marker group
        const markerGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g')
        markerGroup.style.cursor = 'pointer'

        // Create marker circle
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle')
        circle.setAttribute('cx', x)
        circle.setAttribute('cy', y)
        circle.setAttribute('r', '8')
        circle.setAttribute('fill', '#10b981')
        circle.setAttribute('stroke', '#ffffff')
        circle.setAttribute('stroke-width', '2')

        // Create marker label
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text')
        text.setAttribute('x', x)
        text.setAttribute('y', y - 15)
        text.setAttribute('text-anchor', 'middle')
        text.setAttribute('font-size', '12')
        text.setAttribute('font-weight', 'bold')
        text.setAttribute('fill', '#374151')
        text.textContent = attraction.name.substring(0, 15) + (attraction.name.length > 15 ? '...' : '')

        // Create tooltip background
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect')
        const textBBox = text.getBBox ? text.getBBox() : { width: 100, height: 16 }
        rect.setAttribute('x', x - (textBBox.width / 2) - 4)
        rect.setAttribute('y', y - 30)
        rect.setAttribute('width', textBBox.width + 8)
        rect.setAttribute('height', 20)
        rect.setAttribute('fill', 'rgba(255, 255, 255, 0.9)')
        rect.setAttribute('stroke', '#d1d5db')
        rect.setAttribute('rx', '4')

        markerGroup.appendChild(rect)
        markerGroup.appendChild(text)
        markerGroup.appendChild(circle)

        // Add click event
        markerGroup.addEventListener('click', () => {
          emit('marker-click', attraction)
        })

        // Add hover effects
        markerGroup.addEventListener('mouseenter', () => {
          circle.setAttribute('r', '10')
          circle.setAttribute('fill', '#059669')
        })

        markerGroup.addEventListener('mouseleave', () => {
          circle.setAttribute('r', '8')
          circle.setAttribute('fill', '#10b981')
        })

        svg.appendChild(markerGroup)
        markers.value.push(markerGroup)
      })
    }

    const clearMarkers = () => {
      markers.value.forEach(marker => {
        if (marker.parentNode) {
          marker.parentNode.removeChild(marker)
        }
      })
      markers.value = []
    }

    const getUserLocation = () => {
      if (!navigator.geolocation) return

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords
          addUserLocationMarker(latitude, longitude)
        },
        (error) => {
          console.warn('Could not get user location:', error)
        }
      )
    }

    const addUserLocationMarker = (lat, lng) => {
      if (!map.value) return

      const svg = map.value.svg
      const x = 200 + ((lng + 70) / 20) * 400
      const y = 150 + ((lat + 25) / 20) * 300

      const userMarker = document.createElementNS('http://www.w3.org/2000/svg', 'circle')
      userMarker.setAttribute('cx', x)
      userMarker.setAttribute('cy', y)
      userMarker.setAttribute('r', '6')
      userMarker.setAttribute('fill', '#ef4444')
      userMarker.setAttribute('stroke', '#ffffff')
      userMarker.setAttribute('stroke-width', '2')

      svg.appendChild(userMarker)
      userLocationMarker.value = userMarker
    }

    const zoomIn = () => {
      // Simulate zoom in
      console.log('Zoom in')
    }

    const zoomOut = () => {
      // Simulate zoom out
      console.log('Zoom out')
    }

    const resetView = () => {
      // Reset to initial view
      console.log('Reset view')
    }

    // Watch for changes in attractions
    watch(() => props.attractions, () => {
      if (mapLoaded.value) {
        updateMarkers()
      }
    }, { deep: true })

    onMounted(() => {
      initializeMap()
    })

    onUnmounted(() => {
      clearMarkers()
    })

    return {
      mapContainer,
      mapLoaded,
      zoomIn,
      zoomOut,
      resetView
    }
  }
}
</script>

<style scoped>
/* Additional styles for map interactions */
</style>