<template>
  <div class="attraction-map">
    <div :id="mapId" class="w-full h-96 rounded-lg shadow-lg"></div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix para los iconos de Leaflet
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

// Configurar los iconos correctamente
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIconRetina,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
})

export default {
  name: 'AttractionMap',
  props: {
    attraction: {
      type: Object,
      required: true
    },
    nearbyAttractions: {
      type: Array,
      default: () => []
    },
    compact: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const mapId = ref(`attraction-map-${Math.random().toString(36).substr(2, 9)}`)
    const map = ref(null)

    const initializeMap = () => {
      if (map.value) return

      // Verificar que el atractivo tenga coordenadas
      if (!props.attraction.latitude || !props.attraction.longitude) {
        console.warn('Atractivo sin coordenadas:', props.attraction.name)
        return
      }

      const lat = parseFloat(props.attraction.latitude)
      const lng = parseFloat(props.attraction.longitude)
      const zoom = props.compact ? 10 : 12 // Zoom menos cercano para el modo compacto

      // Crear el mapa centrado en el atractivo
      map.value = L.map(mapId.value).setView([lat, lng], zoom)

      // Añadir capa de tiles (OpenStreetMap)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
      }).addTo(map.value)

      // Forzar el zoom después de un pequeño delay
      setTimeout(() => {
        map.value.setView([lat, lng], zoom)
      }, 500)

      // Crear icono personalizado para el atractivo principal
      const mainIconSize = props.compact ? [24, 24] : [32, 32]
      const mainAttractionIcon = L.divIcon({
        className: 'custom-attraction-marker',
        html: `
          <div class="bg-red-600 text-white rounded-full ${props.compact ? 'w-6 h-6' : 'w-8 h-8'} flex items-center justify-center text-xs font-bold shadow-lg border-2 border-white">
            <svg class="${props.compact ? 'w-3 h-3' : 'w-4 h-4'}" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
          </div>
        `,
        iconSize: mainIconSize,
        iconAnchor: [mainIconSize[0]/2, mainIconSize[1]],
        popupAnchor: [0, -mainIconSize[1]]
      })

      // Añadir marcador principal del atractivo
      const mainMarker = L.marker([lat, lng], { icon: mainAttractionIcon })
        .addTo(map.value)

      // Solo agregar popup si NO es modo compacto
      if (!props.compact) {
        mainMarker.bindPopup(`
          <div class="p-4 max-w-sm">
            <h3 class="font-bold text-lg text-gray-800 mb-2">${props.attraction.name}</h3>
            <p class="text-sm text-gray-600 mb-3">${props.attraction.short_description || props.attraction.description || 'Atractivo turístico'}</p>
            ${props.attraction.image_url ? `<img src="${props.attraction.image_url}" alt="${props.attraction.name}" class="w-full h-24 object-cover rounded mb-2"/>` : ''}
            <div class="text-xs text-gray-500">
              <p>📍 Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}</p>
              ${props.attraction.department ? `<p>🏛️ ${props.attraction.department.name}</p>` : ''}
            </div>
          </div>
        `).openPopup()
      } else {
        // En modo compacto, agregar tooltip simple en hover
        mainMarker.bindTooltip(props.attraction.name, {
          permanent: false,
          direction: 'top',
          offset: [0, -10]
        })
      }

      const allMarkers = [mainMarker]

      // Añadir marcadores para atractivos cercanos (solo si NO es modo compacto)
      if (!props.compact && props.nearbyAttractions && props.nearbyAttractions.length > 0) {
        props.nearbyAttractions.forEach(nearby => {
          if (nearby.latitude && nearby.longitude && nearby.id !== props.attraction.id) {
            const nearbyLat = parseFloat(nearby.latitude)
            const nearbyLng = parseFloat(nearby.longitude)

            // Icono diferente para atractivos cercanos
            const nearbyIcon = L.divIcon({
              className: 'custom-nearby-marker',
              html: `
                <div class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-lg border border-white">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              `,
              iconSize: [24, 24],
              iconAnchor: [12, 24],
              popupAnchor: [0, -24]
            })

            const nearbyMarker = L.marker([nearbyLat, nearbyLng], { icon: nearbyIcon })
              .addTo(map.value)
              .bindPopup(`
                <div class="p-3 max-w-xs">
                  <h4 class="font-bold text-base text-gray-800 mb-2">${nearby.name}</h4>
                  <p class="text-sm text-gray-600 mb-2">${nearby.short_description || 'Atractivo cercano'}</p>
                  ${nearby.image_url ? `<img src="${nearby.image_url}" alt="${nearby.name}" class="w-full h-20 object-cover rounded mt-2"/>` : ''}
                  <button class="mt-2 text-blue-600 text-xs hover:underline" onclick="window.location.href='/atractivos/${nearby.slug || nearby.id}'">
                    Ver detalles →
                  </button>
                </div>
              `)

            allMarkers.push(nearbyMarker)
          }
        })
      }

      // Si hay múltiples marcadores, ajustar la vista para mostrar todos (solo en modo NO compacto)
      if (!props.compact && allMarkers.length > 1) {
        const group = new L.featureGroup(allMarkers)
        const bounds = group.getBounds()

        // Verificar si los marcadores están muy dispersos
        const latDiff = Math.abs(bounds.getNorth() - bounds.getSouth())
        const lngDiff = Math.abs(bounds.getEast() - bounds.getWest())

        if (latDiff > 0.05 || lngDiff > 0.05) {
          // Solo ajustar si hay suficiente dispersión
          map.value.fitBounds(bounds, {
            padding: [30, 30],
            maxZoom: 14
          })
        }
      }
    }

    const destroyMap = () => {
      if (map.value) {
        map.value.remove()
        map.value = null
      }
    }

    // Reinitializar cuando cambia el atractivo
    watch(() => props.attraction, () => {
      destroyMap()
      setTimeout(initializeMap, 100)
    }, { deep: true })

    // Reinitializar cuando cambian los atractivos cercanos
    watch(() => props.nearbyAttractions, () => {
      if (map.value) {
        destroyMap()
        setTimeout(initializeMap, 100)
      }
    }, { deep: true })

    onMounted(() => {
      setTimeout(initializeMap, 100)
    })

    onBeforeUnmount(() => {
      destroyMap()
    })

    return {
      mapId
    }
  }
}
</script>

<style scoped>
.attraction-map {
  position: relative;
}

/* Estilos para los marcadores personalizados */
:deep(.custom-attraction-marker) {
  background: transparent;
  border: none;
}

:deep(.custom-nearby-marker) {
  background: transparent;
  border: none;
}

/* Estilos para los popups */
:deep(.leaflet-popup-content) {
  margin: 8px 12px;
  font-family: inherit;
  max-width: 280px;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 8px;
}

:deep(.leaflet-popup-tip) {
  background: white;
}

/* Animación para los marcadores */
:deep(.custom-attraction-marker div),
:deep(.custom-nearby-marker div) {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-5px);
  }
  60% {
    transform: translateY(-3px);
  }
}
</style>