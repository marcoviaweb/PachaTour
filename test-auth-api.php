<?php

/**
 * Script para probar la API de autenticación de Pacha Tour
 */

$baseUrl = 'http://127.0.0.1:8000/api';

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

echo "=== PRUEBAS DE AUTENTICACIÓN PACHA TOUR ===\n\n";

// 1. Probar Health Check
echo "1. Probando Health Check...\n";
$health = makeRequest("$baseUrl/health");
echo "Status: {$health['status']}\n";
echo "Response: " . json_encode($health['body'], JSON_PRETTY_PRINT) . "\n\n";

// 2. Registrar un nuevo usuario
echo "2. Registrando nuevo usuario...\n";
$userData = [
    'name' => 'Juan Carlos',
    'last_name' => 'Pérez López',
    'email' => 'juan.perez@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'phone' => '+591 70123456',
    'nationality' => 'Boliviana',
    'country' => 'Bolivia',
    'city' => 'La Paz',
    'preferred_language' => 'es'
];

$register = makeRequest("$baseUrl/auth/register", 'POST', $userData);
echo "Status: {$register['status']}\n";
echo "Response: " . json_encode($register['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($register['status'] === 201) {
    $token = $register['body']['access_token'];
    echo "✅ Usuario registrado exitosamente!\n";
    echo "Token: $token\n\n";
    
    // 3. Obtener perfil del usuario autenticado
    echo "3. Obteniendo perfil del usuario...\n";
    $profile = makeRequest("$baseUrl/auth/me", 'GET', null, ["Authorization: Bearer $token"]);
    echo "Status: {$profile['status']}\n";
    echo "Response: " . json_encode($profile['body'], JSON_PRETTY_PRINT) . "\n\n";
    
    // 4. Probar login con las mismas credenciales
    echo "4. Probando login...\n";
    $loginData = [
        'email' => 'juan.perez@test.com',
        'password' => 'password123'
    ];
    
    $login = makeRequest("$baseUrl/auth/login", 'POST', $loginData);
    echo "Status: {$login['status']}\n";
    echo "Response: " . json_encode($login['body'], JSON_PRETTY_PRINT) . "\n\n";
    
    // 5. Probar logout
    echo "5. Cerrando sesión...\n";
    $logout = makeRequest("$baseUrl/auth/logout", 'POST', null, ["Authorization: Bearer $token"]);
    echo "Status: {$logout['status']}\n";
    echo "Response: " . json_encode($logout['body'], JSON_PRETTY_PRINT) . "\n\n";
    
    // 6. Intentar acceder al perfil después del logout (debería fallar)
    echo "6. Intentando acceder al perfil después del logout...\n";
    $profileAfterLogout = makeRequest("$baseUrl/auth/me", 'GET', null, ["Authorization: Bearer $token"]);
    echo "Status: {$profileAfterLogout['status']}\n";
    echo "Response: " . json_encode($profileAfterLogout['body'], JSON_PRETTY_PRINT) . "\n\n";
    
} else {
    echo "❌ Error en el registro\n";
}

// 7. Probar registro con email duplicado
echo "7. Probando registro con email duplicado...\n";
$duplicateUser = [
    'name' => 'María',
    'last_name' => 'González',
    'email' => 'juan.perez@test.com', // Mismo email
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$duplicateRegister = makeRequest("$baseUrl/auth/register", 'POST', $duplicateUser);
echo "Status: {$duplicateRegister['status']}\n";
echo "Response: " . json_encode($duplicateRegister['body'], JSON_PRETTY_PRINT) . "\n\n";

// 8. Probar login con credenciales incorrectas
echo "8. Probando login con credenciales incorrectas...\n";
$wrongLogin = [
    'email' => 'juan.perez@test.com',
    'password' => 'wrongpassword'
];

$wrongLoginResponse = makeRequest("$baseUrl/auth/login", 'POST', $wrongLogin);
echo "Status: {$wrongLoginResponse['status']}\n";
echo "Response: " . json_encode($wrongLoginResponse['body'], JSON_PRETTY_PRINT) . "\n\n";

echo "=== FIN DE LAS PRUEBAS ===\n";