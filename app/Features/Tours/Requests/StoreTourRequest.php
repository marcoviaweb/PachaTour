<?php

namespace App\Features\Tours\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tour;

class StoreTourRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:tours,name',
            'slug' => 'nullable|string|max:255|unique:tours,slug|regex:/^[a-z0-9-]+$/',
            'description' => 'required|string|min:50|max:5000',
            'short_description' => 'nullable|string|max:500',
            'type' => 'required|string|in:' . implode(',', array_keys(Tour::TYPES)),
            'duration_days' => 'required|integer|min:1|max:30',
            'duration_hours' => 'nullable|integer|min:1|max:24',
            'price_per_person' => 'required|numeric|min:0|max:99999.99',
            'currency' => 'required|string|in:BOB,USD',
            'min_participants' => 'required|integer|min:1|max:100',
            'max_participants' => 'required|integer|min:1|max:100|gte:min_participants',
            'difficulty_level' => 'required|string|in:' . implode(',', array_keys(Tour::DIFFICULTIES)),
            'included_services' => 'nullable|array',
            'included_services.*' => 'string|max:255',
            'excluded_services' => 'nullable|array',
            'excluded_services.*' => 'string|max:255',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'what_to_bring' => 'nullable|array',
            'what_to_bring.*' => 'string|max:255',
            'meeting_point' => 'required|string|max:500',
            'departure_time' => 'nullable|date_format:H:i',
            'return_time' => 'nullable|date_format:H:i|after:departure_time',
            'itinerary' => 'nullable|array',
            'itinerary.*.day' => 'required_with:itinerary|integer|min:1',
            'itinerary.*.title' => 'required_with:itinerary|string|max:255',
            'itinerary.*.description' => 'required_with:itinerary|string|max:1000',
            'itinerary.*.activities' => 'nullable|array',
            'itinerary.*.activities.*' => 'string|max:255',
            'guide_language' => 'nullable|string|in:es,en,qu,ay',
            'available_languages' => 'nullable|array',
            'available_languages.*' => 'string|in:es,en,qu,ay',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'available_from' => 'nullable|date|after_or_equal:today',
            'available_until' => 'nullable|date|after:available_from',
            
            // Attractions relationship
            'attractions' => 'nullable|array|max:20',
            'attractions.*.id' => 'required_with:attractions|integer|exists:attractions,id',
            'attractions.*.visit_order' => 'required_with:attractions|integer|min:1',
            'attractions.*.duration_minutes' => 'nullable|integer|min:15|max:1440',
            'attractions.*.notes' => 'nullable|string|max:500',
            'attractions.*.is_optional' => 'boolean',
            'attractions.*.arrival_time' => 'nullable|date_format:H:i',
            'attractions.*.departure_time' => 'nullable|date_format:H:i|after:attractions.*.arrival_time',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del tour es obligatorio',
            'name.unique' => 'Ya existe un tour con este nombre',
            'slug.unique' => 'Ya existe un tour con este slug',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones',
            'description.required' => 'La descripción es obligatoria',
            'description.min' => 'La descripción debe tener al menos 50 caracteres',
            'description.max' => 'La descripción no puede exceder 5000 caracteres',
            'type.required' => 'El tipo de tour es obligatorio',
            'type.in' => 'El tipo de tour seleccionado no es válido',
            'duration_days.required' => 'La duración en días es obligatoria',
            'duration_days.min' => 'La duración debe ser de al menos 1 día',
            'duration_days.max' => 'La duración no puede exceder 30 días',
            'price_per_person.required' => 'El precio por persona es obligatorio',
            'price_per_person.min' => 'El precio no puede ser negativo',
            'price_per_person.max' => 'El precio no puede exceder 99,999.99',
            'currency.required' => 'La moneda es obligatoria',
            'currency.in' => 'La moneda debe ser BOB o USD',
            'min_participants.required' => 'El número mínimo de participantes es obligatorio',
            'max_participants.required' => 'El número máximo de participantes es obligatorio',
            'max_participants.gte' => 'El máximo de participantes debe ser mayor o igual al mínimo',
            'difficulty_level.required' => 'El nivel de dificultad es obligatorio',
            'difficulty_level.in' => 'El nivel de dificultad seleccionado no es válido',
            'meeting_point.required' => 'El punto de encuentro es obligatorio',
            'return_time.after' => 'La hora de retorno debe ser posterior a la hora de salida',
            'available_from.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior',
            'available_until.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'attractions.*.id.exists' => 'Uno de los atractivos seleccionados no existe',
            'attractions.*.visit_order.required_with' => 'El orden de visita es obligatorio',
            'attractions.*.departure_time.after' => 'La hora de salida debe ser posterior a la hora de llegada',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Generate slug if not provided
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }

        // Set default values
        $this->merge([
            'is_featured' => $this->boolean('is_featured', false),
            'is_active' => $this->boolean('is_active', true),
            'currency' => $this->get('currency', 'BOB'),
            'guide_language' => $this->get('guide_language', 'es'),
        ]);

        // Ensure available_languages includes guide_language
        if ($this->filled('guide_language')) {
            $availableLanguages = $this->get('available_languages', []);
            if (!in_array($this->guide_language, $availableLanguages)) {
                $availableLanguages[] = $this->guide_language;
                $this->merge(['available_languages' => $availableLanguages]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that duration_hours is provided for single-day tours
            if ($this->duration_days == 1 && !$this->filled('duration_hours')) {
                $validator->errors()->add('duration_hours', 'La duración en horas es obligatoria para tours de un día');
            }

            // Validate itinerary days match duration
            if ($this->filled('itinerary') && $this->filled('duration_days')) {
                $maxDay = collect($this->itinerary)->max('day');
                if ($maxDay > $this->duration_days) {
                    $validator->errors()->add('itinerary', 'El itinerario no puede tener más días que la duración del tour');
                }
            }

            // Validate attractions visit order is sequential
            if ($this->filled('attractions')) {
                $orders = collect($this->attractions)->pluck('visit_order')->sort()->values();
                $expected = range(1, count($orders));
                if ($orders->toArray() !== $expected) {
                    $validator->errors()->add('attractions', 'El orden de visita de los atractivos debe ser secuencial (1, 2, 3, ...)');
                }
            }
        });
    }
}