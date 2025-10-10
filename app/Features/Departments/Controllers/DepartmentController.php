<?php

namespace App\Features\Departments\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(): JsonResponse
    {
        // Test data for the 9 departments of Bolivia
        $departments = [
            ['id' => 1, 'name' => 'Beni', 'slug' => 'beni'],
            ['id' => 2, 'name' => 'Chuquisaca', 'slug' => 'chuquisaca'],
            ['id' => 3, 'name' => 'Cochabamba', 'slug' => 'cochabamba'],
            ['id' => 4, 'name' => 'La Paz', 'slug' => 'la-paz'],
            ['id' => 5, 'name' => 'Oruro', 'slug' => 'oruro'],
            ['id' => 6, 'name' => 'Pando', 'slug' => 'pando'],
            ['id' => 7, 'name' => 'Potosí', 'slug' => 'potosi'],
            ['id' => 8, 'name' => 'Santa Cruz', 'slug' => 'santa-cruz'],
            ['id' => 9, 'name' => 'Tarija', 'slug' => 'tarija'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Departamentos obtenidos correctamente',
            'data' => $departments
        ]);
    }

    /**
     * Display the specified department.
     */
    public function show(string $slug): JsonResponse
    {
        // Test implementation
        return response()->json([
            'success' => true,
            'message' => "Departamento {$slug} encontrado",
            'data' => [
                'slug' => $slug,
                'name' => ucfirst(str_replace('-', ' ', $slug)),
                'description' => "Descripción del departamento de {$slug}",
                'attractions_count' => rand(5, 20)
            ]
        ]);
    }
}