<?php

namespace App\Features\Reviews\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Features\Reviews\Models\Review;
use App\Features\Tours\Models\Booking;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reviewable_type' => [
                'required',
                'string',
                Rule::in(['App\Features\Attractions\Models\Attraction', 'App\Features\Tours\Models\Tour'])
            ],
            'reviewable_id' => [
                'required',
                'integer',
                'exists:' . $this->getTableFromType() . ',id'
            ],
            'booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $booking = Booking::find($value);
                        if (!$booking || $booking->user_id !== auth()->id()) {
                            $fail('La reserva especificada no es válida.');
                        }
                    }
                }
            ],
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
            'visit_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'travel_type' => [
                'nullable',
                'string',
                Rule::in(array_keys(Review::TRAVEL_TYPES))
            ],
            'language' => [
                'nullable',
                'string',
                'size:2',
                Rule::in(['es', 'en'])
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reviewable_type.required' => 'El tipo de entidad a reseñar es obligatorio.',
            'reviewable_type.in' => 'El tipo de entidad no es válido.',
            'reviewable_id.required' => 'El ID de la entidad a reseñar es obligatorio.',
            'reviewable_id.exists' => 'La entidad especificada no existe.',
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
            'visit_date.before_or_equal' => 'La fecha de visita no puede ser futura.',
            'travel_type.in' => 'El tipo de viaje especificado no es válido.',
            'language.size' => 'El idioma debe ser un código de 2 caracteres.',
            'language.in' => 'Solo se permiten los idiomas: español (es) e inglés (en).'
        ];
    }

    /**
     * Get the table name from reviewable_type
     */
    private function getTableFromType(): string
    {
        $type = $this->input('reviewable_type');
        
        switch ($type) {
            case 'App\Features\Attractions\Models\Attraction':
                return 'attractions';
            case 'App\Features\Tours\Models\Tour':
                return 'tours';
            default:
                return 'attractions'; // fallback
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default language if not provided
        if (!$this->has('language')) {
            $this->merge(['language' => 'es']);
        }

        // Convert rating to float
        if ($this->has('rating')) {
            $this->merge(['rating' => (float) $this->input('rating')]);
        }
    }
} 