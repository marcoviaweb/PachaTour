<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AuthenticateApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthenticateApiTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticateApi $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AuthenticateApi();
    }

    public function test_unauthenticated_user_receives_401()
    {
        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Token de autenticación requerido', $data['message']);
        $this->assertEquals('Unauthenticated', $data['error']);
    }

    public function test_authenticated_active_user_passes()
    {
        $user = User::factory()->create(['is_active' => true]);
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_authenticated_inactive_user_receives_403()
    {
        $user = User::factory()->create(['is_active' => false]);
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Cuenta desactivada', $data['message']);
        $this->assertEquals('Account disabled', $data['error']);
    }
}