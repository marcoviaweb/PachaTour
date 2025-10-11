<?php

namespace App\Features\Search\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Búsqueda es pública
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'query' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'tourism_type' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:name,created_at,rating',
            'sort_order' => 'nullable|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'query.max' => 'La consulta no puede exceder 255 caracteres',
            'department_id.exists' => 'El departamento seleccionado no existe',
            'tourism_type.max' => 'El tipo de turismo no puede exceder 100 caracteres',
            'sort_by.in' => 'El campo de ordenamiento debe ser: name, created_at o rating',
            'sort_order.in' => 'El orden debe ser: asc o desc',
            'limit.min' => 'El límite debe ser al menos 1',
            'limit.max' => 'El límite no puede exceder 100'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'query' => 'consulta',
            'department_id' => 'departamento',
            'tourism_type' => 'tipo de turismo',
            'sort_by' => 'ordenar por',
            'sort_order' => 'orden',
            'limit' => 'límite'
        ];
    }
}