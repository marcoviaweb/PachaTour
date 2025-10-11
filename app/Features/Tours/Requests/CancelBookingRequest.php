<?php

namespace App\Features\Tours\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelBookingRequest extends FormRequest
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
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:500',
                Rule::in([
                    'Cambio de planes del cliente',
                    'Condiciones climáticas adversas',
                    'Problemas de salud',
                    'Emergencia familiar',
                    'Problemas de transporte',
                    'Cancelación por parte del operador',
                    'Fuerza mayor',
                    'Otro'
                ])
            ],
            'custom_reason' => [
                'required_if:cancellation_reason,Otro',
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.in' => 'Debe seleccionar una razón válida para la cancelación',
            'cancellation_reason.max' => 'La razón de cancelación no puede exceder 500 caracteres',
            'custom_reason.required_if' => 'Debe especificar la razón personalizada cuando selecciona "Otro"',
            'custom_reason.max' => 'La razón personalizada no puede exceder 500 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cancellation_reason' => 'razón de cancelación',
            'custom_reason' => 'razón personalizada',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If custom reason is provided and cancellation_reason is "Otro", use custom reason
        if ($this->cancellation_reason === 'Otro' && $this->custom_reason) {
            $this->merge([
                'cancellation_reason' => $this->custom_reason
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $booking = $this->route('booking');
            
            // Check if booking can be cancelled
            if ($booking && !$booking->can_be_cancelled) {
                $validator->errors()->add(
                    'booking',
                    'Esta reserva no puede ser cancelada. El tour es muy pronto o la reserva ya está finalizada.'
                );
            }
        });
    }
}