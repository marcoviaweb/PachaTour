<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Filtros Attractions</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .error { background-color: #ffe6e6; border-color: #ff0000; }
        .success { background-color: #e6ffe6; border-color: #00ff00; }
        button { margin: 5px; padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Filtros Admin Attractions</h1>
    
    <div>
        <h2>1. Test Autenticación</h2>
        <button onclick="testAuth()">Verificar Autenticación</button>
        <div id="auth-result" class="result"></div>
    </div>

    <div>
        <h2>2. Test Login Temporal</h2>
        <button onclick="doLogin()">Login como Admin</button>
        <div id="login-result" class="result"></div>
    </div>

    <div>
        <h2>3. Test Ruta Admin Attractions (GET)</h2>
        <button onclick="testAdminAttractionsGet()">GET /admin/attractions</button>
        <div id="get-result" class="result"></div>
    </div>

    <div>
        <h2>4. Test Filtros (AJAX)</h2>
        <input type="text" id="search-input" placeholder="Buscar..." value="plaza">
        <button onclick="testFilter()">Test Filtro</button>
        <div id="filter-result" class="result"></div>
    </div>

    <script>
        // Configurar axios con CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        console.log('CSRF Token:', csrfToken);

        function showResult(elementId, data, isError = false) {
            const element = document.getElementById(elementId);
            element.className = 'result ' + (isError ? 'error' : 'success');
            element.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }

        async function testAuth() {
            try {
                const response = await axios.get('/debug-auth-status');
                showResult('auth-result', response.data);
            } catch (error) {
                console.error('Error en testAuth:', error);
                showResult('auth-result', {
                    error: error.message,
                    response: error.response?.data,
                    status: error.response?.status
                }, true);
            }
        }

        async function doLogin() {
            try {
                const response = await axios.get('/temp-login');
                showResult('login-result', response.data);
                
                // Si el login fue exitoso, verificar auth automáticamente
                if (response.data.success) {
                    setTimeout(testAuth, 1000);
                }
            } catch (error) {
                console.error('Error en doLogin:', error);
                showResult('login-result', {
                    error: error.message,
                    response: error.response?.data,
                    status: error.response?.status
                }, true);
            }
        }

        async function testAdminAttractionsGet() {
            try {
                const response = await axios.get('/admin/attractions');
                showResult('get-result', {
                    status: response.status,
                    headers: response.headers,
                    dataType: typeof response.data,
                    dataLength: response.data?.length || 'N/A'
                });
            } catch (error) {
                console.error('Error en testAdminAttractionsGet:', error);
                showResult('get-result', {
                    error: error.message,
                    response: error.response?.data,
                    status: error.response?.status,
                    statusText: error.response?.statusText
                }, true);
            }
        }

        async function testFilter() {
            const search = document.getElementById('search-input').value;
            try {
                const response = await axios.get('/admin/attractions', {
                    params: {
                        search: search,
                        department: '',
                        type: ''
                    }
                });
                showResult('filter-result', {
                    status: response.status,
                    search: search,
                    dataType: typeof response.data,
                    dataKeys: Object.keys(response.data || {}),
                    attractionsCount: response.data?.attractions?.data?.length || 'N/A'
                });
            } catch (error) {
                console.error('Error en testFilter:', error);
                showResult('filter-result', {
                    error: error.message,
                    response: error.response?.data,
                    status: error.response?.status,
                    statusText: error.response?.statusText,
                    search: search
                }, true);
            }
        }

        // Test inicial al cargar la página
        window.onload = function() {
            console.log('Página cargada, iniciando tests...');
            testAuth();
        };
    </script>
</body>
</html>