<template>
    <div class="min-h-screen bg-gradient-to-br from-pacha-50 to-pacha-100">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-pacha-800">Pacha Tour</h1>
                    </div>
                    <nav class="hidden md:flex space-x-8">
                        <a href="#" class="text-gray-600 hover:text-pacha-600 transition-colors">Destinos</a>
                        <a href="#" class="text-gray-600 hover:text-pacha-600 transition-colors">Tours</a>
                        <a href="#" class="text-gray-600 hover:text-pacha-600 transition-colors">Sobre Bolivia</a>
                        <a href="#" class="btn-primary">Iniciar Sesión</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h2 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    Descubre la Magia de
                    <span class="text-pacha-600">Bolivia</span>
                </h2>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Explora los nueve departamentos de Bolivia y vive experiencias únicas. 
                    Desde el Salar de Uyuni hasta la Amazonía, tu aventura comienza aquí.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="btn-primary text-lg px-8 py-3">
                        Explorar Destinos
                    </button>
                    <button class="btn-secondary text-lg px-8 py-3">
                        Ver Tours
                    </button>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card text-center">
                    <div class="w-12 h-12 bg-pacha-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-pacha-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">9 Departamentos</h3>
                    <p class="text-gray-600">Explora cada rincón de Bolivia, desde La Paz hasta Santa Cruz</p>
                </div>

                <div class="card text-center">
                    <div class="w-12 h-12 bg-pacha-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-pacha-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Reservas Fáciles</h3>
                    <p class="text-gray-600">Programa tu visita con unos pocos clics</p>
                </div>

                <div class="card text-center">
                    <div class="w-12 h-12 bg-pacha-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-pacha-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Pagos Seguros</h3>
                    <p class="text-gray-600">Múltiples métodos de pago con total seguridad</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <p>&copy; 2024 Pacha Tour. Descubre Bolivia con nosotros.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>