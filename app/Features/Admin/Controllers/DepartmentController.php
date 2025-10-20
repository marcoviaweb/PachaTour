<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Departments\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of departments for admin
     */
    public function index(Request $request)
    {
        $query = Department::query()
            ->withCount(['attractions', 'activeAttractions'])
            ->with(['media' => function ($query) {
                $query->where('type', 'image')->orderBy('sort_order');
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('capital', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $departments = $query->paginate(15)->appends($request->all());

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
            'statistics' => $this->getDepartmentStatistics()
        ]);
    }

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return Inertia::render('Admin/Departments/Create');
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:departments,name',
            'capital' => 'required|string|max:100',
            'description' => 'required|string|min:50',
            'short_description' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'population' => 'nullable|integer|min:1',
            'area_km2' => 'nullable|numeric|min:0.01',
            'climate' => 'nullable|string|max:100',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        // Generate slug
        $data['slug'] = Str::slug($data['name']);
        
        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Department::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set default sort order
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = Department::max('sort_order') + 1;
        }

        $department = Department::create($data);

        // Handle main image upload
        if ($request->hasFile('image')) {
            $this->uploadMainImage($department, $request->file('image'));
        }

        // Handle gallery upload
        if ($request->hasFile('gallery')) {
            $this->uploadGalleryImages($department, $request->file('gallery'));
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento creado exitosamente.');
    }

    /**
     * Display the specified department
     */
    public function show(Department $department)
    {
        $department->load([
            'attractions' => function ($query) {
                $query->withCount('reviews')->with('media');
            },
            'media' => function ($query) {
                $query->orderBy('sort_order');
            },
            'reviews' => function ($query) {
                $query->with('user:id,name')->latest()->limit(10);
            }
        ]);

        $statistics = [
            'total_attractions' => $department->attractions()->count(),
            'active_attractions' => $department->activeAttractions()->count(),
            'total_reviews' => $department->reviews()->count(),
            'average_rating' => $department->reviews()->avg('rating') ?? 0,
            'total_visits' => $department->attractions()->sum('visits_count'),
        ];

        return Inertia::render('Admin/Departments/Show', [
            'department' => $department,
            'statistics' => $statistics
        ]);
    }

    /**
     * Show the form for editing the specified department
     */
    public function edit(Department $department)
    {
        $department->load('media');
        
        Log::info('EDIT: Department data being sent:', [
            'department' => $department->toArray(),
            'timestamp' => now()
        ]);
        
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department
        ]);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'capital' => 'required|string|max:100',
            'description' => 'required|string|min:50',
            'short_description' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'population' => 'nullable|integer|min:1',
            'area_km2' => 'nullable|numeric|min:0.01',
            'climate' => 'nullable|string|max:100',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        // Update slug if name changed
        if ($data['name'] !== $department->name) {
            $data['slug'] = Str::slug($data['name']);
            
            // Ensure unique slug
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Department::where('slug', $data['slug'])->where('id', '!=', $department->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $department->update($data);

        // Handle main image upload
        if ($request->hasFile('image')) {
            $this->uploadMainImage($department, $request->file('image'));
        }

        // Handle gallery upload
        if ($request->hasFile('gallery')) {
            $this->uploadGalleryImages($department, $request->file('gallery'));
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento actualizado exitosamente.');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        // Check if department has attractions
        if ($department->attractions()->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar el departamento porque tiene atractivos asociados.'
            ]);
        }

        // Delete associated media files
        foreach ($department->media as $media) {
            Storage::disk('public')->delete($media->file_path);
            $media->delete();
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento eliminado exitosamente.');
    }

    /**
     * Toggle department status
     */
    public function toggleStatus(Department $department)
    {
        $department->update([
            'is_active' => !$department->is_active
        ]);

        $status = $department->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Departamento {$status} exitosamente.");
    }

    /**
     * Update department coordinates
     */
    public function updateCoordinates(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department->update($validator->validated());

        return response()->json([
            'message' => 'Coordenadas actualizadas exitosamente.',
            'coordinates' => $department->getCoordinates()
        ]);
    }

    /**
     * Bulk actions on departments
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $departments = Department::whereIn('id', $request->departments);
        $count = $departments->count();

        switch ($request->action) {
            case 'activate':
                $departments->update(['is_active' => true]);
                $message = "{$count} departamentos activados exitosamente.";
                break;
            
            case 'deactivate':
                $departments->update(['is_active' => false]);
                $message = "{$count} departamentos desactivados exitosamente.";
                break;
            
            case 'delete':
                // Check if any department has attractions
                $departmentsWithAttractions = $departments->has('attractions')->count();
                if ($departmentsWithAttractions > 0) {
                    return response()->json([
                        'error' => 'No se pueden eliminar departamentos que tienen atractivos asociados.'
                    ], 422);
                }
                
                $departments->delete();
                $message = "{$count} departamentos eliminados exitosamente.";
                break;
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Get department statistics
     */
    private function getDepartmentStatistics()
    {
        return [
            'total' => Department::count(),
            'active' => Department::where('is_active', true)->count(),
            'inactive' => Department::where('is_active', false)->count(),
            'with_attractions' => Department::has('attractions')->count(),
            'without_attractions' => Department::doesntHave('attractions')->count(),
        ];
    }

    /**
     * Remove a media file from department
     */
    public function removeMedia(Department $department, $mediaId)
    {
        try {
            $media = $department->media()->findOrFail($mediaId);
            
            // Delete file from storage
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            
            // Delete media record
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen'
            ], 500);
        }
    }

    /**
     * Upload main image for department
     */
    private function uploadMainImage(Department $department, $image)
    {
        $path = $image->store('departments', 'public');
        
        // Delete old main image if exists
        if ($department->image_path && Storage::disk('public')->exists($department->image_path)) {
            Storage::disk('public')->delete($department->image_path);
        }
        
        $department->update(['image_path' => $path]);
    }

    /**
     * Upload gallery images for department
     */
    private function uploadGalleryImages(Department $department, array $images)
    {
        $maxSortOrder = $department->media()->max('sort_order') ?? 0;
        
        foreach ($images as $image) {
            $path = $image->store('departments/gallery', 'public');
            $maxSortOrder++;
            
            $department->media()->create([
                'type' => 'image',
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'sort_order' => $maxSortOrder
            ]);
        }
    }
}