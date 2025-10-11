<?php

namespace App\Features\Tours\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TourSchedule;

class UpdateTourScheduleRequest extends FormRequest
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
            'date' => 'sometimes|required|date|after_or_equal:today',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i|after:start_time',
            'available_spots' => 'sometimes|required|integer|min:1|max:' . $tour->max_participants,
            'price_override' => 'sometimes|nullable|numeric|min:0|max:99999.99',
            'status' => 'sometimes|string|in:' . implode(',', array_keys(TourSchedule::STATUSES)),
            'notes' => 'sometimes|nullable|string|max:1000',
            'special_conditions' => 'sometimes|nullable|array',
            'special_conditions.*' => 'string|max:255',
            'guide_name' => 'sometimes|nullable|string|max:255',
            'guide_contact' => 'sometimes|nullable|string|max:255',
            'is_private' => 'sometimes|boolean',
            'weather_forecast' => 'sometimes|nullable|numeric|min:0|max:10',
            'weather_conditions' => 'sometimes|nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'La fecha es obligatoria',
            'date.after_or_equal' => 'La fecha debe ser hoy o posterior',
            'start_time.required' => 'La hora de inicio es obligatoria',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:MM',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:MM',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'available_spots.required' => 'El número de cupos disponibles es obligatorio',
            'available_spots.min' => 'Debe haber al menos 1 cupo disponible',
            'available_spots.max' => 'Los cupos no pueden exceder la capacidad máxima del tour',
            'price_override.min' => 'El precio no puede ser negativo',
            'price_override.max' => 'El precio no puede exceder 99,999.99',
            'status.in' => 'El estado seleccionado no es válido',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres',
            'special_conditions.*.max' => 'Cada condición especial no puede exceder 255 caracteres',
            'guide_name.max' => 'El nombre del guía no puede exceder 255 caracteres',
            'guide_contact.max' => 'El contacto del guía no puede exceder 255 caracteres',
            'weather_forecast.min' => 'El pronóstico del clima debe ser entre 0 y 10',
            'weather_forecast.max' => 'El pronóstico del clima debe ser entre 0 y 10',
            'weather_conditions.max' => 'Las condiciones climáticas no pueden exceder 255 caracteres',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tour = $this->route('tour');
            $schedule = $this->route('schedule');
            
            // Check if schedule has bookings and prevent certain changes
            $hasBookings = $schedule->bookings()->whereIn('status', ['confirmed', 'paid'])->exists();
            
            if ($hasBookings) {
                $restrictedFields = ['date', 'start_time', 'end_time'];
                
                foreach ($restrictedFields as $field) {
                    if ($this->filled($field) && $this->get($field) != $schedule->$field) {
                        $validator->errors()->add($field, 
                            "No se puede modificar {$field} porque el horario tiene reservas confirmadas");
                    }
                }
            }

            // Validate available spots doesn't go below booked spots
            if ($this->filled('available_spots')) {
                if ($this->available_spots < $schedule->booked_spots) {
                    $validator->errors()->add('available_spots', 
                        "Los cupos disponibles no pueden ser menores a los ya reservados ({$schedule->booked_spots})");
                }
                
                if ($this->available_spots > $tour->max_participants) {
                    $validator->errors()->add('available_spots', 
                        "Los cupos disponibles no pueden exceder la capacidad máxima del tour ({$tour->max_participants})");
                }
            }

            // Validate date is within tour availability window
            if ($this->filled('date')) {
                if ($tour->available_from && $this->date < $tour->available_from) {
                    $validator->errors()->add('date', 
                        "La fecha debe ser posterior a la fecha de inicio del tour ({$tour->available_from})");
                }
                
                if ($tour->available_until && $this->date > $tour->available_until) {
                    $validator->errors()->add('date', 
                        "La fecha debe ser anterior a la fecha de fin del tour ({$tour->available_until})");
                }
            }

            // Validate status changes
            if ($this->filled('status')) {
                $newStatus = $this->status;
                $currentStatus = $schedule->status;
                
                // Prevent changing from completed or cancelled to other states
                if (in_array($currentStatus, [TourSchedule::STATUS_COMPLETED, TourSchedule::STATUS_CANCELLED])) {
                    if ($newStatus !== $currentStatus) {
                        $validator->errors()->add('status', 
                            'No se puede cambiar el estado de un horario completado o cancelado');
                    }
                }
                
                // Validate full status
                if ($newStatus === TourSchedule::STATUS_FULL) {
                    $availableSpots = $this->filled('available_spots') ? $this->available_spots : $schedule->available_spots;
                    if ($schedule->booked_spots < $availableSpots) {
                        $validator->errors()->add('status', 
                            'No se puede marcar como lleno si aún hay cupos disponibles');
                    }
                }
            }

            // Validate end_time is reasonable for tour duration
            if ($this->filled('start_time') && $this->filled('end_time') && $tour->duration_hours) {
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
                $duration = $endTime->diffInHours($startTime);
                
                if ($duration > $tour->duration_hours + 2) { // Allow 2 hours buffer
                    $validator->errors()->add('end_time', 
                        "La duración del horario excede significativamente la duración estimada del tour");
                }
            }

            // Validate guide contact format if provided
            if ($this->filled('guide_contact')) {
                $contact = $this->guide_contact;
                // Basic validation for phone or email
                if (!filter_var($contact, FILTER_VALIDATE_EMAIL) && 
                    !preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $contact)) {
                    $validator->errors()->add('guide_contact', 
                        'El contacto del guía debe ser un email válido o un número de teléfono');
                }
            }
        });
    }
}