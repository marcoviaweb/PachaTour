<?php

namespace App\Features\Tours\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tour;
use Illuminate\Validation\Rule;

class UpdateTourRequest extends FormRequest
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
        $tour = $this->route('tour');
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tours', 'name')->ignore($tour->id)
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('tours', 'slug')->ignore($tour->id)
            ],
            'description' => 'sometimes|required|string|min:50|max:5000',
            'short_description' => 'sometimes|nullable|string|max:500',
            'type' => 'sometimes|required|string|in:' . implode(',', array_keys(Tour::TYPES)),
            'duration_days' => 'sometimes|required|integer|min:1|max:30',
            'duration_hours' => 'sometimes|nullable|integer|min:1|max:24',
            'price_per_person' => 'sometimes|required|numeric|min:0|max:99999.99',
            'currency' => 'sometimes|required|string|in:BOB,USD',
            'min_participants' => 'sometimes|required|integer|min:1|max:100',
            'max_participants' => 'sometimes|required|integer|min:1|max:100|gte:min_participants',
            'difficulty_level' => 'sometimes|required|string|in:' . implode(',', array_keys(Tour::DIFFICULTIES)),
            'included_services' => 'sometimes|nullable|array',
            'included_services.*' => 'string|max:255',
            'excluded_services' => 'sometimes|nullable|array',
            'excluded_services.*' => 'string|max:255',
            'requirements' => 'sometimes|nullable|array',
            'requirements.*' => 'string|max:255',
            'what_to_bring' => 'sometimes|nullable|array',
            'what_to_bring.*' => 'string|max:255',
            'meeting_point' => 'sometimes|required|string|max:500',
            'departure_time' => 'sometimes|nullable|date_format:H:i',
            'return_time' => 'sometimes|nullable|date_format:H:i|after:departure_time',
            'itinerary' => 'sometimes|nullable|array',
            'itinerary.*.day' => 'required_with:itinerary|integer|min:1',
            'itinerary.*.title' => 'required_with:itinerary|string|max:255',
            'itinerary.*.description' => 'required_with:itinerary|string|max:1000',
            'itinerary.*.activities' => 'nullable|array',
            'itinerary.*.activities.*' => 'string|max:255',
            'guide_language' => 'sometimes|nullable|string|in:es,en,qu,ay',
            'available_languages' => 'sometimes|nullable|array',
            'available_languages.*' => 'string|in:es,en,qu,ay',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'available_from' => 'sometimes|nullable|date|after_or_equal:today',
            'available_until' => 'sometimes|nullable|date|after:available_from',
            
            // Attractions relationship
            'attractions' => 'sometimes|nullable|array|max:20',
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
        // Generate slug if name is being updated but slug is not provided
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }

        // Ensure available_languages includes guide_language if both are provided
        if ($this->filled('guide_language') && $this->filled('available_languages')) {
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
            $tour = $this->route('tour');
            
            // Check if tour has active bookings before allowing certain changes
            // Note: Temporarily disabled until booking system is implemented
            // $hasActiveBookings = $tour->bookings()
            //                          ->whereIn('status', ['pending', 'confirmed', 'paid'])
            //                          ->exists();

            // if ($hasActiveBookings) {
            //     $restrictedFields = ['min_participants', 'max_participants', 'duration_days'];
            //     
            //     foreach ($restrictedFields as $field) {
            //         if ($this->filled($field) && $this->get($field) != $tour->$field) {
            //             $validator->errors()->add($field, "No se puede modificar {$field} porque el tour tiene reservas activas");
            //         }
            //     }
            // }
            
            $hasActiveBookings = false; // Temporary until booking system is implemented

            // Validate that duration_hours is provided for single-day tours
            $durationDays = $this->filled('duration_days') ? $this->duration_days : $tour->duration_days;
            $durationHours = $this->filled('duration_hours') ? $this->duration_hours : $tour->duration_hours;
            
            if ($durationDays == 1 && !$durationHours) {
                $validator->errors()->add('duration_hours', 'La duración en horas es obligatoria para tours de un día');
            }

            // Validate itinerary days match duration
            if ($this->filled('itinerary')) {
                $maxDay = collect($this->itinerary)->max('day');
                if ($maxDay > $durationDays) {
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

            // Validate price changes don't affect existing bookings unfairly
            if ($this->filled('price_per_person') && $hasActiveBookings) {
                $newPrice = $this->price_per_person;
                $oldPrice = $tour->price_per_person;
                
                // Allow price decreases, but warn about increases
                if ($newPrice > $oldPrice * 1.1) { // More than 10% increase
                    $validator->errors()->add('price_per_person', 'No se puede aumentar el precio más del 10% cuando hay reservas activas');
                }
            }
        });
    }
}