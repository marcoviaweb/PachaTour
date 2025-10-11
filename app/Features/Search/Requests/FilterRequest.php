<?php

namespace App\Features\Search\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Los filtros son públicos
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'nullable|string|in:natural,cultural,historical,adventure,religious,archaeological,urban,gastronomic',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'difficulty_level' => 'nullable|string|in:Fácil,Moderado,Difícil',
            'min_duration' => 'nullable|integer|min:0',
            'max_duration' => 'nullable|integer|min:0|gte:min_duration',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'is_featured' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:500',
            'sort_by' => 'nullable|in:name,rating,price,created_at,popularity,reviews_count,distance',
            'sort_order' => 'nullable|in:asc,desc'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.max' => 'El texto de búsqueda no puede exceder 255 caracteres',
            'department_id.exists' => 'El departamento seleccionado no existe',
            'type.in' => 'El tipo de turismo debe ser uno de los valores permitidos',
            'min_price.numeric' => 'El precio mínimo debe ser un número',
            'min_price.min' => 'El precio mínimo no puede ser negativo',
            'max_price.numeric' => 'El precio máximo debe ser un número',
            'max_price.gte' => 'El precio máximo debe ser mayor o igual al precio mínimo',
            'min_rating.between' => 'La valoración mínima debe estar entre 0 y 5',
            'difficulty_level.in' => 'El nivel de dificultad debe ser: Fácil, Moderado o Difícil',
            'min_duration.min' => 'La duración mínima no puede ser negativa',
            'max_duration.gte' => 'La duración máxima debe ser mayor o igual a la duración mínima',
            'amenities.array' => 'Las amenidades deben ser un array',
            'amenities.*.max' => 'Cada amenidad no puede exceder 100 caracteres',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
            'radius.min' => 'El radio debe ser al menos 1 km',
            'radius.max' => 'El radio no puede exceder 500 km',
            'sort_by.in' => 'El campo de ordenamiento no es válido',
            'sort_order.in' => 'El orden debe ser asc o desc'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'búsqueda',
            'department_id' => 'departamento',
            'type' => 'tipo de turismo',
            'min_price' => 'precio mínimo',
            'max_price' => 'precio máximo',
            'min_rating' => 'valoración mínima',
            'difficulty_level' => 'nivel de dificultad',
            'min_duration' => 'duración mínima',
            'max_duration' => 'duración máxima',
            'amenities' => 'amenidades',
            'is_featured' => 'destacado',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'radius' => 'radio',
            'sort_by' => 'ordenar por',
            'sort_order' => 'orden'
        ];
    }
}