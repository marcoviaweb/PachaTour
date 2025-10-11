<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\Department;
use App\Models\Attraction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentGridTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_departments_via_api()
    {
        // Crear departamentos de prueba
        $departments = Department::factory()->count(3)->create();
        
        $response = $this->getJson('/api/departments');
        
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'capital',
                            'short_description',
                            'attractions_count'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_includes_attractions_count_in_department_data()
    {
        $department = Department::factory()->create();
        Attraction::factory()->count(5)->create(['department_id' => $department->id]);
        
        $response = $this->getJson('/api/departments');
        
        $response->assertStatus(200);
        $departmentData = $response->json('data.0');
        $this->assertEquals(5, $departmentData['attractions_count']);
    }

    /** @test */
    public function it_returns_empty_array_when_no_departments_exist()
    {
        $response = $this->getJson('/api/departments');
        
        $response->assertStatus(200)
                ->assertJson(['data' => []]);
    }

    /** @test */
    public function it_handles_department_without_image()
    {
        Department::factory()->create();
        
        $response = $this->getJson('/api/departments');
        
        $response->assertStatus(200);
        $departmentData = $response->json('data.0');
        $this->assertArrayHasKey('image_url', $departmentData);
    }
}