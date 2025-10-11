<?php

namespace Tests\Unit\Departments;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;
use App\Features\Departments\Controllers\DepartmentController;
use Illuminate\Http\Request;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected DepartmentController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new DepartmentController();
    }

    /** @test */
    public function it_can_instantiate_controller()
    {
        $this->assertInstanceOf(DepartmentController::class, $this->controller);
    }

    /** @test */
    public function index_method_returns_json_response()
    {
        // Create test departments
        Department::factory()->count(2)->create(['is_active' => true]);

        $response = $this->controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function show_method_returns_department_data()
    {
        $department = Department::factory()->create(['is_active' => true]);

        $response = $this->controller->show($department->slug);

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($department->id, $data['data']['id']);
        $this->assertEquals($department->name, $data['data']['name']);
        $this->assertEquals($department->slug, $data['data']['slug']);
    }

    /** @test */
    public function show_method_returns_404_for_non_existent_department()
    {
        $response = $this->controller->show('non-existent-slug');

        $this->assertEquals(404, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Departamento no encontrado', $data['message']);
    }

    /** @test */
    public function show_method_returns_404_for_inactive_department()
    {
        $department = Department::factory()->create(['is_active' => false]);

        $response = $this->controller->show($department->slug);

        $this->assertEquals(404, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Departamento no encontrado', $data['message']);
    }
}