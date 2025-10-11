<template>
  <Head title="Departamentos - Pacha Tour" />
  
  <AppLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-green-600 to-blue-600 text-white">
      <div class="absolute inset-0 bg-black opacity-40"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Departamentos de Bolivia
          </h1>
          <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            Explora los nueve departamentos bolivianos y descubre la diversidad 
            cultural, natural e histórica de cada región
          </p>
        </div>
      </div>
    </section>

    <!-- Departments Grid -->
    <section class="py-16">
      <DepartmentGrid />
    </section>

    <!-- Additional Info -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Diversidad Boliviana
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Cada departamento de Bolivia ofrece experiencias únicas, desde los paisajes 
            altiplánicos hasta la exuberante Amazonía
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">9 Departamentos</h3>
            <p class="text-gray-600">Cada uno con su propia identidad cultural y atractivos únicos</p>
          </div>

          <div class="text-center">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Múltiples Destinos</h3>
            <p class="text-gray-600">Desde el altiplano hasta los llanos, cada región ofrece paisajes únicos</p>
          </div>

          <div class="text-center">
            <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Experiencias Auténticas</h3>
            <p class="text-gray-600">Vive la cultura, tradiciones y hospitalidad de cada departamento</p>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DepartmentGrid from '@/Components/DepartmentGrid.vue'

export default {
  name: 'DepartmentsIndex',
  components: {
    Head,
    AppLayout,
    DepartmentGrid
  }
}
</script>