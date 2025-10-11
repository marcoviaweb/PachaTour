<?php

namespace App\Features\Attractions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1|max:10',
            'files.*' => [
                'required',
                'file',
                'max:51200', // 50MB max file size
                function ($attribute, $value, $fail) {
                    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp'];
                    $allowedVideoTypes = ['mp4', 'mov', 'avi', 'webm'];
                    $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);
                    
                    $extension = strtolower($value->getClientOriginalExtension());
                    
                    if (!in_array($extension, $allowedTypes)) {
                        $fail('El archivo debe ser una imagen (jpg, jpeg, png, webp) o video (mp4, mov, avi, webm).');
                    }
                    
                    // Additional size limits based on type
                    if (in_array($extension, $allowedImageTypes) && $value->getSize() > 10485760) { // 10MB for images
                        $fail('Las imágenes no pueden exceder 10MB.');
                    }
                    
                    if (in_array($extension, $allowedVideoTypes) && $value->getSize() > 52428800) { // 50MB for videos
                        $fail('Los videos no pueden exceder 50MB.');
                    }
                }
            ],
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Debe seleccionar al menos un archivo.',
            'files.array' => 'Los archivos deben enviarse como un array.',
            'files.min' => 'Debe seleccionar al menos un archivo.',
            'files.max' => 'No puede subir más de 10 archivos a la vez.',
            'files.*.required' => 'Cada archivo es obligatorio.',
            'files.*.file' => 'Cada elemento debe ser un archivo válido.',
            'files.*.max' => 'Cada archivo no puede exceder 50MB.',
            'alt_text.max' => 'El texto alternativo no puede exceder 255 caracteres.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden no puede ser negativo.',
            'sort_order.max' => 'El orden no puede exceder 999.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured', false),
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check total file count for the attraction
            $attraction = $this->route('attraction');
            if ($attraction) {
                $currentMediaCount = $attraction->media()->count();
                $newFilesCount = count($this->files ?? []);
                
                if ($currentMediaCount + $newFilesCount > 50) {
                    $validator->errors()->add('files', 'Un atractivo no puede tener más de 50 archivos multimedia en total.');
                }
            }

            // Validate file dimensions for images (optional, can be added if needed)
            if ($this->hasFile('files')) {
                foreach ($this->file('files') as $index => $file) {
                    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp'];
                    $extension = strtolower($file->getClientOriginalExtension());
                    
                    if (in_array($extension, $allowedImageTypes)) {
                        $imageInfo = getimagesize($file->getPathname());
                        if ($imageInfo) {
                            $width = $imageInfo[0];
                            $height = $imageInfo[1];
                            
                            // Minimum dimensions
                            if ($width < 300 || $height < 200) {
                                $validator->errors()->add("files.{$index}", 'Las imágenes deben tener al menos 300x200 píxeles.');
                            }
                            
                            // Maximum dimensions
                            if ($width > 4000 || $height > 4000) {
                                $validator->errors()->add("files.{$index}", 'Las imágenes no pueden exceder 4000x4000 píxeles.');
                            }
                        }
                    }
                }
            }
        });
    }
}