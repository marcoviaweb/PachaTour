<template>
  <div class="department-map">
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
  name: 'DepartmentMap',
  props: {
    departmentName: {
      type: String,
      required: true
    },
    departmentSlug: {
      type: String,
      required: true
    },
    attractions: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    const mapId = ref(`map-${Math.random().toString(36).substr(2, 9)}`)
    const map = ref(null)

    // Coordenadas más precisas de cada departamento de Bolivia
    const departmentCoordinates = {
      'santa-cruz': { center: [-17.8146, -63.1561], zoom: 10 },
      'la-paz': { center: [-16.5000, -68.1193], zoom: 10 },
      'cochabamba': { center: [-17.3895, -66.1568], zoom: 11 },
      'potosi': { center: [-19.5836, -65.7531], zoom: 10 },
      'oruro': { center: [-17.9714, -67.1069], zoom: 11 },
      'chuquisaca': { center: [-19.0448, -65.2592], zoom: 11 },
      'tarija': { center: [-21.5355, -64.7296], zoom: 11 },
      'beni': { center: [-14.8333, -64.9000], zoom: 10 },
      'pando': { center: [-11.0267, -68.7692], zoom: 10 }
    }

    const initializeMap = () => {
      if (map.value) return

      // Obtener coordenadas específicas del departamento
      const coords = departmentCoordinates[props.departmentSlug]
      
      if (!coords) {
        // Usar coordenadas por defecto de Bolivia
        const defaultCoords = { center: [-16.2902, -63.5887], zoom: 6 }
        map.value = L.map(mapId.value).setView(defaultCoords.center, defaultCoords.zoom)
      } else {
        // Crear el mapa con las coordenadas específicas
        map.value = L.map(mapId.value).setView(coords.center, coords.zoom)
      }

      // Añadir capa de tiles (OpenStreetMap)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
      }).addTo(map.value)

      // Forzar el zoom después de un pequeño delay
      setTimeout(() => {
        if (coords) {
          map.value.setView(coords.center, coords.zoom)
        }
      }, 500)

      // Añadir marcador para el centro del departamento
      const centerMarker = L.marker(coords.center)
        .addTo(map.value)
        .bindPopup(`
          <div class="p-3 text-center">
            <h3 class="font-bold text-lg text-gray-800">${props.departmentName}</h3>
            <p class="text-sm text-gray-600">Departamento de Bolivia</p>
          </div>
        `)

      // Array para almacenar todos los marcadores
      const allMarkers = [centerMarker]

      // Añadir marcadores para las atracciones si tienen coordenadas
      if (props.attractions && props.attractions.length > 0) {
        props.attractions.forEach(attraction => {
          if (attraction.latitude && attraction.longitude) {
            const attractionMarker = L.marker([attraction.latitude, attraction.longitude])
              .addTo(map.value)
              .bindPopup(`
                <div class="p-3 max-w-xs">
                  <h4 class="font-bold text-base text-gray-800 mb-2">${attraction.name}</h4>
                  <p class="text-sm text-gray-600 mb-2">${attraction.short_description || 'Atractivo turístico'}</p>
                  ${attraction.image_url ? `<img src="${attraction.image_url}" alt="${attraction.name}" class="w-full h-20 object-cover rounded mt-2"/>` : ''}
                </div>
              `)
            
            allMarkers.push(attractionMarker)
          }
        })
      }

      // Solo si hay más de 3 marcadores Y las atracciones están muy dispersas, ajustar bounds
      const hasValidAttractions = allMarkers.length > 1 && (allMarkers.length - 1) > 0
      
      if (hasValidAttractions && allMarkers.length > 3) {
        const group = new L.featureGroup(allMarkers)
        const bounds = group.getBounds()
        
        // Solo ajustar si las coordenadas están realmente dispersas (más de 0.1 grados de diferencia)
        const latDiff = Math.abs(bounds.getNorth() - bounds.getSouth())
        const lngDiff = Math.abs(bounds.getEast() - bounds.getWest())
        
        if (latDiff > 0.1 || lngDiff > 0.1) {
          map.value.fitBounds(bounds, {
            padding: [20, 20],
            maxZoom: Math.max(coords.zoom - 1, 8) // Un zoom menos que el del departamento, mínimo 8
          })
        }
      } else {
        // Mantener el zoom específico del departamento si hay pocas atracciones
        // Forzar el zoom nuevamente después de añadir marcadores
        setTimeout(() => {
          if (coords && map.value) {
            map.value.setView(coords.center, coords.zoom)
          }
        }, 200)
      }
    }

    const destroyMap = () => {
      if (map.value) {
        map.value.remove()
        map.value = null
      }
    }

    // Reinitializar mapa cuando cambian las atracciones
    watch(() => props.attractions, () => {
      if (map.value) {
        destroyMap()
        setTimeout(initializeMap, 100)
      }
    }, { deep: true })

    // Reinitializar cuando cambia el departamento
    watch(() => props.departmentSlug, () => {
      destroyMap()
      setTimeout(initializeMap, 100)
    })

    onMounted(() => {
      setTimeout(initializeMap, 100) // Pequeño delay para asegurar que el DOM esté listo
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
.department-map {
  position: relative;
}

/* Estilos para los popups */
:deep(.leaflet-popup-content) {
  margin: 8px 12px;
  font-family: inherit;
  max-width: 250px;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 8px;
}

:deep(.leaflet-popup-tip) {
  background: white;
}
</style>