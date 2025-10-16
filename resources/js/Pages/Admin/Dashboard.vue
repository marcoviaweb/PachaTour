<template>
  <Head title="Dashboard - Admin" />

  <AdminLayout>
    <template #header>
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Dashboard Administrativo
        </h2>
        <p class="mt-1 text-sm text-gray-600">
          Resumen general del sistema PachaTour
        </p>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <UsersIcon class="h-6 w-6 text-blue-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Usuarios
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ metrics.total_users }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <span class="text-green-600 font-medium">+{{ metrics.new_users_this_month }}</span>
              <span class="text-gray-600"> este mes</span>
            </div>
          </div>
        </div>

        <!-- Total Attractions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <EyeIcon class="h-6 w-6 text-green-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Atractivos Activos
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ metrics.total_attractions }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <Link href="/admin/attractions" class="text-green-600 font-medium hover:text-green-900">
                Ver todos los atractivos
              </Link>
            </div>
          </div>
        </div>

        <!-- Total Bookings -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CalendarIcon class="h-6 w-6 text-purple-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Reservas
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ metrics.total_bookings }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <span class="text-purple-600 font-medium">{{ metrics.bookings_this_month }}</span>
              <span class="text-gray-600"> este mes</span>
            </div>
          </div>
        </div>

        <!-- Confirmed Bookings -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Reservas Confirmadas
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ metrics.confirmed_bookings }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <span class="text-yellow-600 font-medium">{{ metrics.pending_bookings }}</span>
              <span class="text-gray-600"> pendientes</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Chart -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Ingresos Mensuales</h3>
          </div>
          <div class="px-6 py-4">
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
              <div class="text-center">
                <ChartBarIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">Gráfico de ingresos</p>
                <p class="text-sm text-gray-400 mt-2">
                  Total: ${{ chartData.total_revenue || '0' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bookings Chart -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Reservas por Mes</h3>
          </div>
          <div class="px-6 py-4">
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
              <div class="text-center">
                <CalendarIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">Gráfico de reservas</p>
                <p class="text-sm text-gray-400 mt-2">
                  Este mes: {{ metrics.bookings_this_month }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Bookings -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Reservas Recientes</h3>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="recentActivity.recent_bookings && recentActivity.recent_bookings.length > 0">
              <div
                v-for="booking in recentActivity.recent_bookings"
                :key="booking.id"
                class="px-6 py-4"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-900">
                      {{ booking.user?.name || 'Usuario desconocido' }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ booking.attraction?.name || 'Atractivo no especificado' }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        booking.status === 'confirmed'
                          ? 'bg-green-100 text-green-800'
                          : booking.status === 'pending'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ booking.status === 'confirmed' ? 'Confirmado' : booking.status === 'pending' ? 'Pendiente' : 'Cancelado' }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ formatDate(booking.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="px-6 py-8 text-center">
              <CalendarIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500">No hay reservas recientes</p>
            </div>
          </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Reseñas Recientes</h3>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="recentActivity.recent_reviews && recentActivity.recent_reviews.length > 0">
              <div
                v-for="review in recentActivity.recent_reviews"
                :key="review.id"
                class="px-6 py-4"
              >
                <div class="flex items-start space-x-3">
                  <div class="flex-1">
                    <div class="flex items-center space-x-2">
                      <p class="text-sm font-medium text-gray-900">
                        {{ review.user?.name || 'Usuario anónimo' }}
                      </p>
                      <div class="flex items-center">
                        <span v-for="n in 5" :key="n" class="h-3 w-3">
                          <StarIcon
                            :class="[
                              n <= (review.rating || 0) ? 'text-yellow-400' : 'text-gray-300',
                              'h-3 w-3'
                            ]"
                            fill="currentColor"
                          />
                        </span>
                      </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ review.attraction?.name || 'Atractivo no especificado' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ formatDate(review.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="px-6 py-8 text-center">
              <StarIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500">No hay reseñas recientes</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
        </div>
        <div class="px-6 py-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Link
              href="/admin/departments/create"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg text-center transition duration-150 flex items-center justify-center space-x-2"
            >
              <PlusIcon class="h-5 w-5" />
              <span>Nuevo Departamento</span>
            </Link>
            <Link
              href="/admin/attractions/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-center transition duration-150 flex items-center justify-center space-x-2"
            >
              <PlusIcon class="h-5 w-5" />
              <span>Nuevo Atractivo</span>
            </Link>
            <Link
              href="/admin/reports"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg text-center transition duration-150 flex items-center justify-center space-x-2"
            >
              <ChartBarIcon class="h-5 w-5" />
              <span>Ver Reportes</span>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import {
  UsersIcon,
  EyeIcon,
  CalendarIcon,
  CheckCircleIcon,
  ChartBarIcon,
  StarIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Head,
    Link,
    AdminLayout,
    UsersIcon,
    EyeIcon,
    CalendarIcon,
    CheckCircleIcon,
    ChartBarIcon,
    StarIcon,
    PlusIcon,
  },

  props: {
    metrics: Object,
    recentActivity: Object,
    chartData: Object,
  },

  setup() {
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    return {
      formatDate,
    }
  },
}
</script>