import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configure CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Configure authentication for API calls - CRITICAL for session auth
window.axios.defaults.withCredentials = true;
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// Global function to refresh CSRF token with caching
let csrfRefreshInProgress = false;
let lastCsrfRefresh = 0;
const CSRF_REFRESH_COOLDOWN = 5000; // 5 seconds cooldown

window.refreshCsrfToken = async () => {
    const now = Date.now();
    
    // Prevent multiple simultaneous refresh attempts
    if (csrfRefreshInProgress || (now - lastCsrfRefresh) < CSRF_REFRESH_COOLDOWN) {
        return;
    }
    
    csrfRefreshInProgress = true;
    lastCsrfRefresh = now;
    
    try {
        await window.axios.get('/sanctum/csrf-cookie');
        // Update the meta tag with fresh token if available
        const metaToken = document.head.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            const response = await window.axios.get('/csrf-token');
            if (response.data.token) {
                metaToken.content = response.data.token;
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.token;
            }
        }
    } catch (error) {
        // Silent fail - no console logs for normal operation
    } finally {
        csrfRefreshInProgress = false;
    }
};

// Request interceptor to ensure fresh CSRF token
window.axios.interceptors.request.use(async function (config) {
    // For POST, PUT, PATCH, DELETE requests, ensure fresh token
    if (['post', 'put', 'patch', 'delete'].includes(config.method)) {
        const metaToken = document.head.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            config.headers['X-CSRF-TOKEN'] = metaToken.content;
        }
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

// Interceptor para manejo de errores de autenticación
window.axios.interceptors.response.use(function (response) {
    return response;
}, async function (error) {
    if (error.response?.status === 401) {
        console.error('❌ UNAUTHORIZED - User not authenticated or session expired');
    }
    if (error.response?.status === 419) {
        // Only log if it's a real CSRF error that needs attention
        const isImportantRequest = error.config?.url?.includes('/logout') || 
                                 error.config?.url?.includes('/login') ||
                                 error.config?.url?.includes('/register');
        
        if (isImportantRequest) {
            console.log('🔄 CSRF token refreshed for authentication request');
        }
        
        // Auto-refresh CSRF token and retry the request (silently)
        try {
            await window.refreshCsrfToken();
            // Update the failed request with new token
            const metaToken = document.head.querySelector('meta[name="csrf-token"]');
            if (metaToken && error.config) {
                error.config.headers['X-CSRF-TOKEN'] = metaToken.content;
                // Retry the original request
                return window.axios.request(error.config);
            }
        } catch (refreshError) {
            // Silent fail for normal navigation
        }
    }
    return Promise.reject(error);
});