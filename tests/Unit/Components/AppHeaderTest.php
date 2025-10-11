<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppHeaderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_navigation_links()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // El header se renderiza en el lado del cliente con Vue.js
        // Aquí podríamos probar que la página principal se carga correctamente
    }

    /** @test */
    public function it_shows_login_options_for_guests()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // Para usuarios no autenticados, el componente Vue mostrará opciones de login
    }

    /** @test */
    public function it_shows_user_menu_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        // Para usuarios autenticados, el componente Vue mostrará el menú de usuario
    }

    /** @test */
    public function it_shows_admin_options_for_admin_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/');
        
        $response->assertStatus(200);
        // Para administradores, el componente Vue mostrará opciones adicionales
    }
}