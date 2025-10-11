<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Pacha Tour') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            margin: 2rem;
        }
        
        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .status {
            background: #e8f5e8;
            color: #2d5a2d;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #4caf50;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .feature {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .feature h3 {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .feature p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .links {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .version-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏔️ Pacha Tour</div>
        <div class="subtitle">Plataforma de Turismo de Bolivia</div>
        
        <div class="status">
            ✅ Laravel está funcionando correctamente
        </div>
        
        <div class="features">
            <div class="feature">
                <h3>🗂️ Estructura por Features</h3>
                <p>Organización modular del código por funcionalidades</p>
            </div>
            
            <div class="feature">
                <h3>🏛️ 9 Departamentos</h3>
                <p>Gestión completa de todos los departamentos bolivianos</p>
            </div>
            
            <div class="feature">
                <h3>🎯 API REST</h3>
                <p>Endpoints listos para el frontend Vue.js</p>
            </div>
            
            <div class="feature">
                <h3>🔒 Seguridad</h3>
                <p>Implementación de mejores prácticas de seguridad</p>
            </div>
        </div>
        
        <div class="links">
            <a href="/test" class="btn btn-primary">🧪 Test API</a>
            <a href="/test-features" class="btn btn-secondary">🏗️ Test Features</a>
            <a href="/api/health" class="btn btn-secondary">❤️ Health Check</a>
            <a href="/api/departments" class="btn btn-secondary">🗺️ Departamentos</a>
        </div>
        
        <div class="version-info">
            <strong>Laravel:</strong> {{ app()->version() }} | 
            <strong>PHP:</strong> {{ PHP_VERSION }} | 
            <strong>Entorno:</strong> {{ config('app.env') }}
        </div>
    </div>
</body>
</html>