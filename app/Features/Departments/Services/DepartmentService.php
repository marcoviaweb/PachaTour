<?php

namespace App\Features\Departments\Services;

class DepartmentService
{
    /**
     * Get all departments of Bolivia
     */
    public function getAllDepartments(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Beni',
                'slug' => 'beni',
                'description' => 'Departamento amazónico con rica biodiversidad',
                'capital' => 'Trinidad'
            ],
            [
                'id' => 2,
                'name' => 'Chuquisaca',
                'slug' => 'chuquisaca',
                'description' => 'Cuna de la libertad americana',
                'capital' => 'Sucre'
            ],
            [
                'id' => 3,
                'name' => 'Cochabamba',
                'slug' => 'cochabamba',
                'description' => 'Valle de eterna primavera',
                'capital' => 'Cochabamba'
            ],
            [
                'id' => 4,
                'name' => 'La Paz',
                'slug' => 'la-paz',
                'description' => 'Sede de gobierno y maravillas andinas',
                'capital' => 'La Paz'
            ],
            [
                'id' => 5,
                'name' => 'Oruro',
                'slug' => 'oruro',
                'description' => 'Capital del folklore boliviano',
                'capital' => 'Oruro'
            ],
            [
                'id' => 6,
                'name' => 'Pando',
                'slug' => 'pando',
                'description' => 'Puerta de entrada a la Amazonía',
                'capital' => 'Cobija'
            ],
            [
                'id' => 7,
                'name' => 'Potosí',
                'slug' => 'potosi',
                'description' => 'Villa Imperial y patrimonio de la humanidad',
                'capital' => 'Potosí'
            ],
            [
                'id' => 8,
                'name' => 'Santa Cruz',
                'slug' => 'santa-cruz',
                'description' => 'Tierra de oportunidades y desarrollo',
                'capital' => 'Santa Cruz de la Sierra'
            ],
            [
                'id' => 9,
                'name' => 'Tarija',
                'slug' => 'tarija',
                'description' => 'Valle de la eterna primavera y vinos',
                'capital' => 'Tarija'
            ],
        ];
    }

    /**
     * Get department by slug
     */
    public function getDepartmentBySlug(string $slug): ?array
    {
        $departments = $this->getAllDepartments();
        
        foreach ($departments as $department) {
            if ($department['slug'] === $slug) {
                return $department;
            }
        }
        
        return null;
    }
}