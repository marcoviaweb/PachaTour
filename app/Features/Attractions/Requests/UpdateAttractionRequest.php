<?php

namespace App\Features\Attractions\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attraction;

class UpdateAttractionRequest extends FormRequest
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
            'department_id' => 'sometimes|integer|exists:departments,id',
            'name' => 'sometimes|string|min:3|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\.]+$/',
            'description' => 'sometimes|string|min:50|max:2000',
            'short_description' => 'nullable|string|max:300',
            'type' => 'sometimes|string|in:' . implode(',', array_keys(Attraction::TYPES)),
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'address' => 'nullable|string|max:200',
            'city' => 'sometimes|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/',
            'entry_price' => 'nullable|numeric|min:0|max:9999.99',
            'currency' => 'nullable|string|size:3|in:BOB,USD,EUR',
            'opening_hours' => 'nullable|array',
            'opening_hours.monday' => 'nullable|array',
            'opening_hours.monday.open' => 'nullable|date_format:H:i',
            'opening_hours.monday.close' => 'nullable|date_format:H:i|after:opening_hours.monday.open',
            'opening_hours.tuesday' => 'nullable|array',
            'opening_hours.tuesday.open' => 'nullable|date_format:H:i',
            'opening_hours.tuesday.close' => 'nullable|date_format:H:i|after:opening_hours.tuesday.open',
            'opening_hours.wednesday' => 'nullable|array',
            'opening_hours.wednesday.open' => 'nullable|date_format:H:i',
            'opening_hours.wednesday.close' => 'nullable|date_format:H:i|after:opening_hours.wednesday.open',
            'opening_hours.thursday' => 'nullable|array',
            'opening_hours.thursday.open' => 'nullable|date_format:H:i',
            'opening_hours.thursday.close' => 'nullable|date_format:H:i|after:opening_hours.thursday.open',
            'opening_hours.friday' => 'nullable|array',
            'opening_hours.friday.open' => 'nullable|date_format:H:i',
            'opening_hours.friday.close' => 'nullable|date_format:H:i|after:opening_hours.friday.open',
            'opening_hours.saturday' => 'nullable|array',
            'opening_hours.saturday.open' => 'nullable|date_format:H:i',
            'opening_hours.saturday.close' => 'nullable|date_format:H:i|after:opening_hours.saturday.open',
            'opening_hours.sunday' => 'nullable|array',
            'opening_hours.sunday.open' => 'nullable|date_format:H:i',
            'opening_hours.sunday.close' => 'nullable|date_format:H:i|after:opening_hours.sunday.open',
            'contact_info' => 'nullable|array',
            'contact_info.phone' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'contact_info.email' => 'nullable|email|max:255',
            'contact_info.website' => 'nullable|url|max:255',
            'difficulty_level' => 'nullable|string|in:easy,moderate,difficult,extreme',
            'estimated_duration' => 'nullable|integer|min:15|max:1440', // 15 minutes to 24 hours
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'restrictions' => 'nullable|array',
            'restrictions.*' => 'string|max:200',
            'best_season' => 'nullable|string|max:100',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'department_id.exists' => 'El departamento seleccionado no existe.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, espacios, guiones y puntos.',
            'description.min' => 'La descripción debe tener al menos 50 caracteres.',
            'description.max' => 'La descripción no puede exceder 2000 caracteres.',
            'short_description.max' => 'La descripción corta no puede exceder 300 caracteres.',
            'type.in' => 'El tipo de atractivo seleccionado no es válido.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90 grados.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180 grados.',
            'city.min' => 'La ciudad debe tener al menos 2 caracteres.',
            'city.regex' => 'La ciudad solo puede contener letras, espacios y guiones.',
            'entry_price.numeric' => 'El precio debe ser un número válido.',
            'entry_price.min' => 'El precio no puede ser negativo.',
            'entry_price.max' => 'El precio no puede exceder 9999.99.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres.',
            'currency.in' => 'La moneda debe ser BOB, USD o EUR.',
            'opening_hours.*.open.date_format' => 'El horario de apertura debe tener formato HH:MM.',
            'opening_hours.*.close.date_format' => 'El horario de cierre debe tener formato HH:MM.',
            'opening_hours.*.close.after' => 'El horario de cierre debe ser posterior al de apertura.',
            'contact_info.phone.regex' => 'El teléfono tiene un formato inválido.',
            'contact_info.email.email' => 'El email debe tener un formato válido.',
            'contact_info.website.url' => 'El sitio web debe ser una URL válida.',
            'difficulty_level.in' => 'El nivel de dificultad debe ser: easy, moderate, difficult o extreme.',
            'estimated_duration.min' => 'La duración estimada debe ser al menos 15 minutos.',
            'estimated_duration.max' => 'La duración estimada no puede exceder 24 horas (1440 minutos).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format coordinates if provided
        if ($this->has('latitude')) {
            $this->merge(['latitude' => (float) $this->latitude]);
        }
        if ($this->has('longitude')) {
            $this->merge(['longitude' => (float) $this->longitude]);
        }

        // Clean entry price if provided
        if ($this->has('entry_price') && $this->entry_price !== null) {
            $this->merge(['entry_price' => (float) $this->entry_price]);
        }

        // Handle boolean fields
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that coordinates are within Bolivia's approximate bounds if provided
            $lat = $this->latitude;
            $lng = $this->longitude;
            
            if ($lat && $lng) {
                // Bolivia approximate bounds: lat -22.9 to -9.7, lng -69.6 to -57.5
                if ($lat < -22.9 || $lat > -9.7 || $lng < -69.6 || $lng > -57.5) {
                    $validator->errors()->add('coordinates', 'Las coordenadas deben estar dentro del territorio boliviano.');
                }
            }

            // Validate opening hours consistency if provided
            if ($this->opening_hours) {
                foreach ($this->opening_hours as $day => $hours) {
                    if (is_array($hours) && isset($hours['open']) && isset($hours['close'])) {
                        if ($hours['open'] >= $hours['close']) {
                            $validator->errors()->add("opening_hours.{$day}", "El horario de cierre debe ser posterior al de apertura para {$day}.");
                        }
                    }
                }
            }
        });
    }
}