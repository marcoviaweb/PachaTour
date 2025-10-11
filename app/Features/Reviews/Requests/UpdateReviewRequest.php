<?php

namespace App\Features\Reviews\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Review;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $review = $this->route('review');
        return auth()->check() && $review->canBeEditedBy(auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5'
            ],
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255'
            ],
            'comment' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ],
            'detailed_ratings' => [
                'nullable',
                'array'
            ],
            'detailed_ratings.*' => [
                'numeric',
                'min:1',
                'max:5'
            ],
            'pros' => [
                'nullable',
                'array',
                'max:5'
            ],
            'pros.*' => [
                'string',
                'max:100'
            ],
            'cons' => [
                'nullable',
                'array',
                'max:5'
            ],
            'cons.*' => [
                'string',
                'max:100'
            ],
            'would_recommend' => [
                'nullable',
                'boolean'
            ],
            'travel_type' => [
                'nullable',
                'string',
                Rule::in(array_keys(Review::TRAVEL_TYPES))
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'La calificación es obligatoria.',
            'rating.min' => 'La calificación mínima es 1 estrella.',
            'rating.max' => 'La calificación máxima es 5 estrellas.',
            'title.required' => 'El título de la reseña es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'comment.required' => 'El comentario es obligatorio.',
            'comment.min' => 'El comentario debe tener al menos 10 caracteres.',
            'comment.max' => 'El comentario no puede exceder 2000 caracteres.',
            'detailed_ratings.*.min' => 'Cada calificación detallada debe ser mínimo 1.',
            'detailed_ratings.*.max' => 'Cada calificación detallada debe ser máximo 5.',
            'pros.max' => 'Máximo 5 aspectos positivos permitidos.',
            'cons.max' => 'Máximo 5 aspectos negativos permitidos.',
            'travel_type.in' => 'El tipo de viaje especificado no es válido.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert rating to float
        if ($this->has('rating')) {
            $this->merge(['rating' => (float) $this->input('rating')]);
        }
    }
}