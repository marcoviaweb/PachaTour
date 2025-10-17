<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simular el controlador
use App\Features\Departments\Models\Department;

$slug = 'cochabamba';
$department = Department::where('slug', $slug)->first();

if (!$department) {
    echo "Department not found with slug: $slug\n";
    exit(1);
}

$department->load('media');

echo "Department data being sent to Vue:\n";
echo json_encode($department->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);