import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'pacha': {
                    50: '#f0f7ff',
                    100: '#e0effe',
                    200: '#bfe4fd',
                    300: '#93d5f9',
                    400: '#5bc0f3',
                    500: '#2da5e8',
                    600: '#1e86d4',
                    700: '#1668b8',
                    800: '#134a96',
                    900: '#0f3b7a',
                },
                'pacha-light': '#e8f4ff',
                'pacha-dark': '#0f3b7a',
            },
        },
    },

    plugins: [forms],
};