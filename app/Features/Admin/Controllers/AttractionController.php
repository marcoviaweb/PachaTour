<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Attractions\Models\Attraction;
use App\Features\Departments\Models\Department;
use App\Features\Attractions\Requests\StoreAttractionRequest;
use App\Features\Attractions\Requests\UpdateAttractionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AttractionController extends Controller
{
    public function __construct()
    {
        // Temporalmente deshabilitado para debug
        // $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of attractions for admin
     */
    public function index(Request $request)
    {
        $query = Attraction::query()
            ->with(['department:id,name,slug', 'media' => function ($query) {
                $query->where('type', 'image')->orderBy('sort_order')->limit(1);
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('city', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('type', 'ILIKE', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Featured filter
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'true');
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortField === 'rating') {
            $query->orderBy('rating', $sortDirection);
        } elseif ($sortField === 'visits') {
            $query->orderBy('visits_count', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $attractions = $query->paginate(15)->appends($request->all());

        // Get filter options
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $types = collect(Attraction::TYPES)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values();

        // Get statistics
        $statistics = [
            'total' => Attraction::count(),
            'active' => Attraction::where('is_active', true)->count(),
            'inactive' => Attraction::where('is_active', false)->count(),
            'featured' => Attraction::where('is_featured', true)->count(),
            'by_type' => Attraction::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return Inertia::render('Admin/Attractions/Index', [
            'attractions' => $attractions,
            'filters' => $request->only(['search', 'department', 'type', 'status', 'featured', 'sort', 'direction']),
            'departments' => $departments,
            'types' => $types,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new attraction
     */
    public function create()
    {
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $types = collect(Attraction::TYPES)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values();

        return Inertia::render('Admin/Attractions/Create', [
            'departments' => $departments,
            'types' => $types,
        ]);
    }

    /**
     * Store a newly created attraction
     */
    public function store(StoreAttractionRequest $request)
    {
        $data = $request->validated();
        
        // Generate unique slug
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $attraction = Attraction::create($data);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->uploadGalleryImages($attraction, $request->file('images'));
        }

        return redirect()->route('admin.attractions.index')
            ->with('success', 'Atractivo creado exitosamente.');
    }

    /**
     * Display the specified attraction
     */
    public function show(Attraction $attraction)
    {
        $attraction->load([
            'department',
            'media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at');
            },
            'tours' => function ($query) {
                $query->where('is_active', true);
            },
            'approvedReviews' => function ($query) {
                $query->with('user:id,name')
                    ->latest()
                    ->limit(10);
            }
        ]);

        // Get statistics
        $statistics = [
            'total_visits' => $attraction->visits_count,
            'total_reviews' => $attraction->reviews_count,
            'average_rating' => $attraction->rating,
            'tours_count' => $attraction->tours->count(),
            'media_count' => $attraction->media->count(),
        ];

        return Inertia::render('Admin/Attractions/Show', [
            'attraction' => $attraction,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified attraction
     */
    public function edit(Attraction $attraction)
    {
        $attraction->load('media');
        
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $types = collect(Attraction::TYPES)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values();

        return Inertia::render('Admin/Attractions/Edit', [
            'attraction' => $attraction,
            'departments' => $departments,
            'types' => $types,
        ]);
    }

    /**
     * Update the specified attraction
     */
    public function update(UpdateAttractionRequest $request, Attraction $attraction)
    {
        $data = $request->validated();

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $attraction->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $attraction->id);
        }

        $attraction->update($data);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $this->uploadGalleryImages($attraction, $request->file('images'));
        }

        return redirect()->route('admin.attractions.index')
            ->with('success', 'Atractivo actualizado exitosamente.');
    }

    /**
     * Remove the specified attraction
     */
    public function destroy(Attraction $attraction)
    {
        // Delete associated media files
        foreach ($attraction->media as $media) {
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
        }

        $attraction->delete();

        return redirect()->route('admin.attractions.index')
            ->with('success', 'Atractivo eliminado exitosamente.');
    }

    /**
     * Toggle attraction status
     */
    public function toggleStatus(Attraction $attraction)
    {
        $attraction->update([
            'is_active' => !$attraction->is_active
        ]);

        $status = $attraction->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Atractivo {$status} exitosamente.");
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Attraction $attraction)
    {
        $attraction->update([
            'is_featured' => !$attraction->is_featured
        ]);

        $status = $attraction->is_featured ? 'destacado' : 'no destacado';
        
        return back()->with('success', "Atractivo marcado como {$status} exitosamente.");
    }

    /**
     * Update coordinates
     */
    public function updateCoordinates(Request $request, Attraction $attraction)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $attraction->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return back()->with('success', 'Coordenadas actualizadas exitosamente.');
    }

    /**
     * Remove a media file from attraction
     */
    public function removeMedia(Attraction $attraction, $mediaId)
    {
        try {
            $media = $attraction->media()->findOrFail($mediaId);
            
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
     * Bulk actions for attractions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'attractions' => 'required|array',
            'attractions.*' => 'exists:attractions,id',
        ]);

        $attractions = Attraction::whereIn('id', $request->attractions);

        switch ($request->action) {
            case 'activate':
                $attractions->update(['is_active' => true]);
                $message = 'Atractivos activados exitosamente.';
                break;
            case 'deactivate':
                $attractions->update(['is_active' => false]);
                $message = 'Atractivos desactivados exitosamente.';
                break;
            case 'feature':
                $attractions->update(['is_featured' => true]);
                $message = 'Atractivos marcados como destacados exitosamente.';
                break;
            case 'unfeature':
                $attractions->update(['is_featured' => false]);
                $message = 'Atractivos desmarcados como destacados exitosamente.';
                break;
            case 'delete':
                // Delete media files first
                foreach ($attractions->get() as $attraction) {
                    foreach ($attraction->media as $media) {
                        if (Storage::disk('public')->exists($media->file_path)) {
                            Storage::disk('public')->delete($media->file_path);
                        }
                    }
                }
                $attractions->delete();
                $message = 'Atractivos eliminados exitosamente.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Generate a unique slug
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = Attraction::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $query = Attraction::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Upload gallery images for attraction
     */
    private function uploadGalleryImages(Attraction $attraction, array $images)
    {
        $maxSortOrder = $attraction->media()->max('sort_order') ?? 0;
        
        foreach ($images as $image) {
            $path = $image->store('attractions/gallery', 'public');
            $maxSortOrder++;
            
            $attraction->media()->create([
                'type' => 'image',
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'sort_order' => $maxSortOrder,
                'url' => Storage::disk('public')->url($path),
            ]);
        }
    }
}