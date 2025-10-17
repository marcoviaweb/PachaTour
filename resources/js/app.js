import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm';
import { Ziggy } from './ziggy.js';

const appName = import.meta.env.VITE_APP_NAME || 'Pacha Tour';

// Import the route function from Ziggy and router for CSRF handling
import { route as ziggyRoute } from '../../vendor/tightenco/ziggy/dist/index.esm';
import { router } from '@inertiajs/vue3';

// Make route function globally available
window.route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy);
        
        // Make route function available as a global property
        app.config.globalProperties.route = (name, params, absolute, config = Ziggy) => 
            ziggyRoute(name, params, absolute, config);
        
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Global Inertia CSRF error handler

// Override Inertia's default error handling for CSRF issues
const originalPost = router.post;
const originalPut = router.put;
const originalPatch = router.patch;
const originalDelete = router.delete;

const wrapWithCsrfHandling = (originalMethod) => {
    return function(url, data = {}, options = {}) {
        const originalOnError = options.onError;
        
        options.onError = async (errors) => {
            // Check if it's a CSRF error
            if (errors.message && errors.message.includes('CSRF') || 
                (typeof errors === 'object' && errors.csrf)) {
                
                // Only log for important operations (login, logout, etc.)
                const isImportantOperation = url.includes('/login') || 
                                           url.includes('/logout') || 
                                           url.includes('/register');
                
                if (isImportantOperation) {
                    console.log('🔄 Refreshing CSRF token for authentication...');
                }
                
                try {
                    // Refresh CSRF token
                    await window.refreshCsrfToken();
                    
                    // Retry the request after a short delay (silently)
                    setTimeout(() => {
                        originalMethod.call(this, url, data, {
                            ...options,
                            onError: originalOnError // Use original error handler for retry
                        });
                    }, 300); // Reduced delay
                    
                    return; // Don't call original error handler for CSRF errors
                } catch (refreshError) {
                    // Silent fail - only log if it's an important operation
                    if (isImportantOperation) {
                        console.error('Failed to refresh CSRF token:', refreshError);
                    }
                }
            }
            
            // Call original error handler for non-CSRF errors
            if (originalOnError) {
                originalOnError(errors);
            }
        };
        
        return originalMethod.call(this, url, data, options);
    };
};

// Wrap Inertia methods with CSRF handling
router.post = wrapWithCsrfHandling(originalPost);
router.put = wrapWithCsrfHandling(originalPut);
router.patch = wrapWithCsrfHandling(originalPatch);
router.delete = wrapWithCsrfHandling(originalDelete);