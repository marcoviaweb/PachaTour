<?php

namespace App\Features\Tours\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Booking;

class UpdateBookingRequest extends FormRequest
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
            'tour_schedule_id' => [
                'sometimes',
                'integer',
                'exists:tour_schedules,id'
            ],
            'participants_count' => [
                'sometimes',
                'integer',
                'min:1',
                'max:20'
            ],
            'participant_details' => [
                'sometimes',
                'array'
            ],
            'participant_details.*.name' => [
                'required_with:participant_details',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'participant_details.*.last_name' => [
                'required_with:participant_details',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'participant_details.*.document_type' => [
                'required_with:participant_details',
                'string',
                Rule::in(['CI', 'Pasaporte', 'DNI', 'Cédula'])
            ],
            'participant_details.*.document_number' => [
                'required_with:participant_details',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-]+$/'
            ],
            'participant_details.*.birth_date' => [
                'required_with:participant_details',
                'date',
                'before:today',
                'after:1920-01-01'
            ],
            'participant_details.*.nationality' => [
                'nullable',
                'string',
                'max:50'
            ],
            'participant_details.*.dietary_restrictions' => [
                'nullable',
                'string',
                'max:200'
            ],
            'participant_details.*.medical_conditions' => [
                'nullable',
                'string',
                'max:500'
            ],
            'special_requests' => [
                'sometimes',
                'array'
            ],
            'special_requests.*' => [
                'string',
                'max:200'
            ],
            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000'
            ],
            'contact_name' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'contact_email' => [
                'sometimes',
                'email',
                'max:255'
            ],
            'contact_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'emergency_contact_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'emergency_contact_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tour_schedule_id.exists' => 'El horario seleccionado no existe',
            'participants_count.min' => 'Debe haber al menos 1 participante',
            'participants_count.max' => 'No se permiten más de 20 participantes por reserva',
            'participant_details.*.name.required_with' => 'El nombre del participante es obligatorio',
            'participant_details.*.name.regex' => 'El nombre solo puede contener letras y espacios',
            'participant_details.*.last_name.required_with' => 'El apellido del participante es obligatorio',
            'participant_details.*.last_name.regex' => 'El apellido solo puede contener letras y espacios',
            'participant_details.*.document_type.required_with' => 'El tipo de documento es obligatorio',
            'participant_details.*.document_type.in' => 'El tipo de documento debe ser CI, Pasaporte, DNI o Cédula',
            'participant_details.*.document_number.required_with' => 'El número de documento es obligatorio',
            'participant_details.*.document_number.regex' => 'El número de documento solo puede contener letras, números y guiones',
            'participant_details.*.birth_date.required_with' => 'La fecha de nacimiento es obligatoria',
            'participant_details.*.birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'participant_details.*.birth_date.after' => 'La fecha de nacimiento debe ser posterior a 1920',
            'contact_name.regex' => 'El nombre de contacto solo puede contener letras y espacios',
            'contact_email.email' => 'El email de contacto debe tener un formato válido',
            'contact_phone.regex' => 'El teléfono de contacto debe tener un formato válido',
            'emergency_contact_name.regex' => 'El nombre del contacto de emergencia solo puede contener letras y espacios',
            'emergency_contact_phone.regex' => 'El teléfono de emergencia debe tener un formato válido',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tour_schedule_id' => 'horario del tour',
            'participants_count' => 'número de participantes',
            'participant_details' => 'detalles de participantes',
            'special_requests' => 'solicitudes especiales',
            'notes' => 'notas',
            'contact_name' => 'nombre de contacto',
            'contact_email' => 'email de contacto',
            'contact_phone' => 'teléfono de contacto',
            'emergency_contact_name' => 'nombre del contacto de emergencia',
            'emergency_contact_phone' => 'teléfono de emergencia',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format phone numbers
        if ($this->has('contact_phone')) {
            $this->merge([
                'contact_phone' => $this->cleanPhoneNumber($this->contact_phone)
            ]);
        }

        if ($this->has('emergency_contact_phone')) {
            $this->merge([
                'emergency_contact_phone' => $this->cleanPhoneNumber($this->emergency_contact_phone)
            ]);
        }

        // Ensure participant_details count matches participants_count if both are provided
        if ($this->has('participant_details') && $this->has('participants_count')) {
            $participantDetails = $this->participant_details;
            $participantsCount = $this->participants_count;
            
            if (count($participantDetails) !== $participantsCount) {
                $this->merge([
                    'participant_details' => array_slice($participantDetails, 0, $participantsCount)
                ]);
            }
        }
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove extra spaces and normalize format
        return preg_replace('/\s+/', ' ', trim($phone));
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $booking = $this->route('booking');
            
            // Check if booking can be modified
            if ($booking && !$this->canBookingBeModified($booking)) {
                $validator->errors()->add(
                    'booking',
                    'Esta reserva no puede ser modificada. El tour es muy pronto o la reserva ya está finalizada.'
                );
            }

            // Custom validation: participant details count should match participants count
            if ($this->has('participant_details') && $this->has('participants_count')) {
                $detailsCount = count($this->participant_details ?? []);
                $participantsCount = $this->participants_count;
                
                if ($detailsCount > 0 && $detailsCount !== $participantsCount) {
                    $validator->errors()->add(
                        'participant_details',
                        "El número de detalles de participantes ({$detailsCount}) debe coincidir con el número de participantes ({$participantsCount})"
                    );
                }
            }

            // Custom validation: emergency contact is required for groups > 5
            $participantsCount = $this->participants_count ?? $booking->participants_count ?? 0;
            if ($participantsCount > 5) {
                $emergencyName = $this->emergency_contact_name ?? $booking->emergency_contact_name ?? null;
                $emergencyPhone = $this->emergency_contact_phone ?? $booking->emergency_contact_phone ?? null;
                
                if (empty($emergencyName) || empty($emergencyPhone)) {
                    $validator->errors()->add(
                        'emergency_contact_name',
                        'El contacto de emergencia es obligatorio para grupos de más de 5 personas'
                    );
                }
            }
        });
    }

    /**
     * Check if booking can be modified
     */
    private function canBookingBeModified(Booking $booking): bool
    {
        // Cannot modify cancelled, completed, or refunded bookings
        if (in_array($booking->status, [
            Booking::STATUS_CANCELLED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_REFUNDED,
            Booking::STATUS_NO_SHOW
        ])) {
            return false;
        }
        
        // Cannot modify if tour is in less than 24 hours
        $tourDateTime = $booking->tourSchedule->start_date_time;
        return $tourDateTime->gt(now()->addHours(24));
    }
}