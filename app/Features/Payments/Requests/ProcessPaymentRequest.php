<?php

namespace App\Features\Payments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,qr_code',
        ];

        // Reglas específicas según el método de pago
        if ($this->payment_method === 'credit_card' || $this->payment_method === 'debit_card') {
            $rules = array_merge($rules, [
                'payment_data.card_number' => 'required|string|regex:/^\d{13,19}$/',
                'payment_data.cvv' => 'required|string|regex:/^\d{3,4}$/',
                'payment_data.expiry_month' => 'required|integer|between:1,12',
                'payment_data.expiry_year' => 'required|integer|min:' . date('Y'),
                'payment_data.cardholder_name' => 'required|string|max:255'
            ]);
        }

        if ($this->payment_method === 'bank_transfer') {
            $rules = array_merge($rules, [
                'payment_data.bank_code' => 'required|string',
                'payment_data.account_number' => 'required|string'
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'booking_id.required' => 'El ID de reserva es obligatorio',
            'booking_id.exists' => 'La reserva especificada no existe',
            'payment_method.required' => 'El método de pago es obligatorio',
            'payment_method.in' => 'El método de pago no es válido',
            'payment_data.card_number.required' => 'El número de tarjeta es obligatorio',
            'payment_data.card_number.regex' => 'El número de tarjeta debe tener entre 13 y 19 dígitos',
            'payment_data.cvv.required' => 'El CVV es obligatorio',
            'payment_data.cvv.regex' => 'El CVV debe tener 3 o 4 dígitos',
            'payment_data.expiry_month.required' => 'El mes de vencimiento es obligatorio',
            'payment_data.expiry_month.between' => 'El mes debe estar entre 1 y 12',
            'payment_data.expiry_year.required' => 'El año de vencimiento es obligatorio',
            'payment_data.expiry_year.min' => 'El año no puede ser anterior al actual',
            'payment_data.cardholder_name.required' => 'El nombre del titular es obligatorio'
        ];
    }
}