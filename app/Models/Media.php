<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'name',
        'file_name',
        'mime_type',
        'type',
        'disk',
        'path',
        'url',
        'size',
        'metadata',
        'alt_text',
        'description',
        'caption',
        'sort_order',
        'is_featured',
        'is_public',
        'uploaded_by_type',
        'uploaded_by_id',
        'transformations',
        'original_name',
        'hash',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'transformations' => 'array',
    ];

    // Constantes para tipos de media
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';

    const TYPES = [
        self::TYPE_IMAGE => 'Imagen',
        self::TYPE_VIDEO => 'Video',
        self::TYPE_AUDIO => 'Audio',
        self::TYPE_DOCUMENT => 'Documento',
    ];

    // Tipos MIME permitidos
    const ALLOWED_IMAGE_TYPES = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'
    ];

    const ALLOWED_VIDEO_TYPES = [
        'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'
    ];

    const ALLOWED_AUDIO_TYPES = [
        'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'
    ];

    const ALLOWED_DOCUMENT_TYPES = [
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    // Relaciones

    /**
     * Entidad a la que pertenece el archivo (polimórfica)
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    /**
     * Solo archivos públicos
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Solo archivos destacados
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Filtrar por tipo
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Solo imágenes
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IMAGE);
    }

    /**
     * Solo videos
     */
    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * Ordenar por orden de visualización
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Buscar por nombre o descripción
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%")
              ->orWhere('alt_text', 'ILIKE', "%{$search}%");
        });
    }

    // Accessors & Mutators

    /**
     * Nombre del tipo en español
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * URL completa del archivo
     */
    public function getFullUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }
        
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Tamaño formateado
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Verificar si es una imagen
     */
    public function getIsImageAttribute(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    /**
     * Verificar si es un video
     */
    public function getIsVideoAttribute(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * Verificar si es un audio
     */
    public function getIsAudioAttribute(): bool
    {
        return $this->type === self::TYPE_AUDIO;
    }

    /**
     * Verificar si es un documento
     */
    public function getIsDocumentAttribute(): bool
    {
        return $this->type === self::TYPE_DOCUMENT;
    }

    /**
     * Obtener dimensiones de imagen
     */
    public function getDimensionsAttribute(): ?array
    {
        if (!$this->is_image || !$this->metadata) {
            return null;
        }
        
        return [
            'width' => $this->metadata['width'] ?? null,
            'height' => $this->metadata['height'] ?? null,
        ];
    }

    /**
     * Obtener duración de video/audio
     */
    public function getDurationAttribute(): ?string
    {
        if (!in_array($this->type, [self::TYPE_VIDEO, self::TYPE_AUDIO]) || !$this->metadata) {
            return null;
        }
        
        $seconds = $this->metadata['duration'] ?? null;
        if (!$seconds) {
            return null;
        }
        
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    // Métodos auxiliares

    /**
     * Determinar tipo de archivo basado en MIME type
     */
    public static function getTypeFromMimeType(string $mimeType): string
    {
        if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return self::TYPE_IMAGE;
        }
        
        if (in_array($mimeType, self::ALLOWED_VIDEO_TYPES)) {
            return self::TYPE_VIDEO;
        }
        
        if (in_array($mimeType, self::ALLOWED_AUDIO_TYPES)) {
            return self::TYPE_AUDIO;
        }
        
        if (in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES)) {
            return self::TYPE_DOCUMENT;
        }
        
        return self::TYPE_DOCUMENT; // Por defecto
    }

    /**
     * Verificar si el tipo MIME está permitido
     */
    public static function isMimeTypeAllowed(string $mimeType): bool
    {
        $allowedTypes = array_merge(
            self::ALLOWED_IMAGE_TYPES,
            self::ALLOWED_VIDEO_TYPES,
            self::ALLOWED_AUDIO_TYPES,
            self::ALLOWED_DOCUMENT_TYPES
        );
        
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Generar hash del archivo
     */
    public function generateHash(): string
    {
        if ($this->hash) {
            return $this->hash;
        }
        
        $content = Storage::disk($this->disk)->get($this->path);
        $hash = hash('sha256', $content);
        
        $this->update(['hash' => $hash]);
        
        return $hash;
    }

    /**
     * Verificar si existe un duplicado
     */
    public static function findDuplicate(string $hash): ?self
    {
        return self::where('hash', $hash)->first();
    }

    /**
     * Marcar como destacado
     */
    public function markAsFeatured(): void
    {
        // Quitar featured de otros archivos del mismo mediable
        self::where('mediable_type', $this->mediable_type)
            ->where('mediable_id', $this->mediable_id)
            ->where('id', '!=', $this->id)
            ->update(['is_featured' => false]);
        
        $this->update(['is_featured' => true]);
    }

    /**
     * Cambiar orden
     */
    public function updateSortOrder(int $order): void
    {
        $this->update(['sort_order' => $order]);
    }

    /**
     * Eliminar archivo físico y registro
     */
    public function deleteFile(): bool
    {
        try {
            // Eliminar archivo físico
            if (Storage::disk($this->disk)->exists($this->path)) {
                Storage::disk($this->disk)->delete($this->path);
            }
            
            // Eliminar transformaciones si existen
            if ($this->transformations) {
                foreach ($this->transformations as $transformation) {
                    if (isset($transformation['path']) && Storage::disk($this->disk)->exists($transformation['path'])) {
                        Storage::disk($this->disk)->delete($transformation['path']);
                    }
                }
            }
            
            // Eliminar registro de la base de datos
            $this->delete();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crear thumbnail para imagen
     */
    public function createThumbnail(int $width = 300, int $height = 300): ?string
    {
        if (!$this->is_image) {
            return null;
        }
        
        // TODO: Implementar generación de thumbnails
        // Esto requeriría una librería como Intervention Image
        
        return null;
    }

    /**
     * Obtener URL del thumbnail
     */
    public function getThumbnailUrl(string $size = 'medium'): ?string
    {
        if (!$this->is_image || !$this->transformations) {
            return $this->full_url;
        }
        
        $thumbnail = collect($this->transformations)->firstWhere('type', 'thumbnail_' . $size);
        
        if ($thumbnail && isset($thumbnail['path'])) {
            return Storage::disk($this->disk)->url($thumbnail['path']);
        }
        
        return $this->full_url;
    }

    /**
     * Obtener información completa del archivo
     */
    public function getFileInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_name' => $this->original_name,
            'type' => $this->type,
            'type_name' => $this->type_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'url' => $this->full_url,
            'thumbnail_url' => $this->getThumbnailUrl(),
            'dimensions' => $this->dimensions,
            'duration' => $this->duration,
            'alt_text' => $this->alt_text,
            'description' => $this->description,
            'caption' => $this->caption,
            'is_featured' => $this->is_featured,
            'is_public' => $this->is_public,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Actualizar metadatos
     */
    public function updateMetadata(array $metadata): void
    {
        $currentMetadata = $this->metadata ?? [];
        $newMetadata = array_merge($currentMetadata, $metadata);
        
        $this->update(['metadata' => $newMetadata]);
    }
}
