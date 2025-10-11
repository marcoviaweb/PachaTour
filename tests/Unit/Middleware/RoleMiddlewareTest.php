<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private RoleMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RoleMiddleware();
    }

    public function test_unauthenticated_user_receives_401()
    {
        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'admin');

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('No autenticado', $data['message']);
    }

    public function test_user_with_correct_role_passes()
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'admin');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_user_with_incorrect_role_receives_403()
    {
        $user = User::factory()->create(['role' => User::ROLE_TOURIST]);
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'admin');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('No autorizado', $data['message']);
    }

    public function test_user_with_multiple_allowed_roles()
    {
        $user = User::factory()->create(['role' => User::ROLE_TOURIST]);
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'admin', 'tourist');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }
}