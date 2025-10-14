<?php

namespace App\Features\Attractions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Features\Attractions\Models\Attraction;
use App\Features\Attractions\Requests\StoreMediaRequest;
use App\Features\Attractions\Requests\UpdateMediaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display media for a specific attraction
     */
    public function index(Attraction $attraction): JsonResponse
    {
        $media = $attraction->media()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Media retrieved successfully'
        ]);
    }

    /**
     * Store new media for an attraction
     */
    public function store(StoreMediaRequest $request, Attraction $attraction): JsonResponse
    {
        $data = $request->validated();
        $uploadedFiles = [];

        try {
            // Handle multiple file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $media = $this->processFileUpload($file, $attraction, $data);
                    $uploadedFiles[] = $media;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $uploadedFiles,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            // Clean up any uploaded files if there was an error
            foreach ($uploadedFiles as $media) {
                Storage::disk('public')->delete($media->path);
                $media->delete();
            }

            return response()->json([
                'success' => false,
                'message' => 'Error uploading files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified media
     */
    public function show(Attraction $attraction, Media $media): JsonResponse
    {
        // Ensure media belongs to the attraction
        if ($media->mediable_id !== $attraction->id || $media->mediable_type !== Attraction::class) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found for this attraction'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Media retrieved successfully'
        ]);
    }

    /**
     * Update the specified media
     */
    public function update(UpdateMediaRequest $request, Attraction $attraction, Media $media): JsonResponse
    {
        // Ensure media belongs to the attraction
        if ($media->mediable_id !== $attraction->id || $media->mediable_type !== Attraction::class) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found for this attraction'
            ], 404);
        }

        $data = $request->validated();
        $media->update($data);

        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Media updated successfully'
        ]);
    }

    /**
     * Remove the specified media
     */
    public function destroy(Attraction $attraction, Media $media): JsonResponse
    {
        // Ensure media belongs to the attraction
        if ($media->mediable_id !== $attraction->id || $media->mediable_type !== Attraction::class) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found for this attraction'
            ], 404);
        }

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }

            // Delete database record
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update media sort order
     */
    public function updateOrder(Request $request, Attraction $attraction): JsonResponse
    {
        $request->validate([
            'media_order' => 'required|array',
            'media_order.*' => 'required|integer|exists:media,id'
        ]);

        try {
            foreach ($request->media_order as $index => $mediaId) {
                Media::where('id', $mediaId)
                    ->where('mediable_id', $attraction->id)
                    ->where('mediable_type', Attraction::class)
                    ->update(['sort_order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Media order updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating media order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set featured media
     */
    public function setFeatured(Attraction $attraction, Media $media): JsonResponse
    {
        // Ensure media belongs to the attraction
        if ($media->mediable_id !== $attraction->id || $media->mediable_type !== Attraction::class) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found for this attraction'
            ], 404);
        }

        try {
            // Remove featured status from all other media of this attraction
            Media::where('mediable_id', $attraction->id)
                ->where('mediable_type', Attraction::class)
                ->update(['is_featured' => false]);

            // Set this media as featured
            $media->update(['is_featured' => true]);

            return response()->json([
                'success' => true,
                'data' => $media,
                'message' => 'Featured media updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting featured media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process individual file upload
     */
    private function processFileUpload($file, Attraction $attraction, array $data): Media
    {
        // Validate file type
        $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedVideoTypes = ['mp4', 'mov', 'avi', 'webm'];
        
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($extension, $allowedImageTypes);
        $isVideo = in_array($extension, $allowedVideoTypes);

        if (!$isImage && !$isVideo) {
            throw new \Exception('Invalid file type. Only images (jpg, jpeg, png, webp) and videos (mp4, mov, avi, webm) are allowed.');
        }

        // Determine media type
        $mediaType = $isImage ? 'image' : 'video';

        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $extension;
        $directory = "attractions/{$attraction->id}/{$mediaType}s";
        $path = $directory . '/' . $filename;

        // Store file
        $file->storeAs($directory, $filename, 'public');

        // Get file size
        $fileSize = $file->getSize();

        // Create media record
        $media = new Media([
            'mediable_type' => Attraction::class,
            'mediable_id' => $attraction->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'type' => $mediaType,
            'path' => $path,
            'file_name' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $fileSize,
            'mime_type' => $file->getMimeType(),
            'alt_text' => $data['alt_text'] ?? '',
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $data['is_featured'] ?? false
        ]);

        $media->save();

        // If this is set as featured, remove featured status from others
        if ($media->is_featured) {
            Media::where('mediable_id', $attraction->id)
                ->where('mediable_type', Attraction::class)
                ->where('id', '!=', $media->id)
                ->update(['is_featured' => false]);
        }

        return $media;
    }

    /**
     * Get media statistics for an attraction
     */
    public function statistics(Attraction $attraction): JsonResponse
    {
        $stats = [
            'total' => $attraction->media()->count(),
            'images' => $attraction->images()->count(),
            'videos' => $attraction->videos()->count(),
            'featured' => $attraction->media()->where('is_featured', true)->count(),
            'total_size' => $attraction->media()->sum('size'),
            'by_type' => $attraction->media()
                ->selectRaw('type, COUNT(*) as count, SUM(size) as total_size')
                ->groupBy('type')
                ->get()
                ->keyBy('type')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Media statistics retrieved successfully'
        ]);
    }
}