<?php

namespace App\Features\Payments\Services;

use App\Models\Booking;
use App\Features\Payments\Models\Payment;
use App\Features\Payments\Services\CommissionService;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private CommissionService $commissionService
    ) {}

    public function processPayment(Booking $booking, string $paymentMethod, array $paymentData): array
    {
        // Validar que la reserva esté en estado válido para pago
        if ($booking->payment_status === 'paid') {
            throw new \Exception('Esta reserva ya ha sido pagada');
        }

        // Calcular comisiones
        $commissionAmount = $this->commissionService->calculateCommission($booking->total_amount);
        $operatorAmount = $booking->total_amount - $commissionAmount;

        // Crear registro de pago
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'commission_amount' => $commissionAmount,
            'operator_amount' => $operatorAmount,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'payment_reference' => $this->generatePaymentReference(),
            'gateway_data' => json_encode($paymentData)
        ]);

        // Procesar según el método de pago
        $result = match($paymentMethod) {
            'credit_card' => $this->processCreditCardPayment($payment, $paymentData),
            'debit_card' => $this->processDebitCardPayment($payment, $paymentData),
            'bank_transfer' => $this->processBankTransferPayment($payment, $paymentData),
            'qr_code' => $this->processQRPayment($payment, $paymentData),
            default => throw new \Exception('Método de pago no soportado')
        };

        // Actualizar estado del pago y reserva
        if ($result['success']) {
            $payment->update([
                'status' => 'completed',
                'gateway_transaction_id' => $result['transaction_id']
            ]);

            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_reference' => $payment->payment_reference
            ]);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return [
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'transaction_id' => $result['transaction_id'] ?? null
        ];
    }

    public function getAvailablePaymentMethods(): array
    {
        return [
            [
                'id' => 'credit_card',
                'name' => 'Tarjeta de Crédito',
                'description' => 'Visa, MasterCard, American Express',
                'enabled' => true
            ],
            [
                'id' => 'debit_card',
                'name' => 'Tarjeta de Débito',
                'description' => 'Débito bancario',
                'enabled' => true
            ],
            [
                'id' => 'bank_transfer',
                'name' => 'Transferencia Bancaria',
                'description' => 'Transferencia directa',
                'enabled' => true
            ],
            [
                'id' => 'qr_code',
                'name' => 'Código QR',
                'description' => 'Pago mediante QR',
                'enabled' => true
            ]
        ];
    }

    public function getPaymentStatus(string $paymentId): string
    {
        $payment = Payment::findOrFail($paymentId);
        return $payment->status;
    }

    public function refundPayment(string $paymentId): array
    {
        $payment = Payment::findOrFail($paymentId);
        
        if ($payment->status !== 'completed') {
            throw new \Exception('Solo se pueden reembolsar pagos completados');
        }

        // Procesar reembolso según el gateway
        $refundResult = $this->processRefund($payment);

        if ($refundResult['success']) {
            $payment->update(['status' => 'refunded']);
            
            // Actualizar estado de la reserva
            $payment->booking->update(['payment_status' => 'refunded']);
        }

        return $refundResult;
    }

    private function processCreditCardPayment(Payment $payment, array $paymentData): array
    {
        // Simulación de procesamiento con Stripe
        // En producción aquí iría la integración real con Stripe
        
        // Validar datos de tarjeta
        if (!isset($paymentData['card_number']) || !isset($paymentData['cvv'])) {
            return ['success' => false, 'error' => 'Datos de tarjeta incompletos'];
        }

        // Simular procesamiento exitoso
        return [
            'success' => true,
            'transaction_id' => 'stripe_' . Str::random(20)
        ];
    }

    private function processDebitCardPayment(Payment $payment, array $paymentData): array
    {
        // Similar al procesamiento de tarjeta de crédito
        return $this->processCreditCardPayment($payment, $paymentData);
    }

    private function processBankTransferPayment(Payment $payment, array $paymentData): array
    {
        // Para transferencias bancarias, generar instrucciones
        return [
            'success' => true,
            'transaction_id' => 'transfer_' . Str::random(15),
            'instructions' => 'Transferir a cuenta: 1234567890'
        ];
    }

    private function processQRPayment(Payment $payment, array $paymentData): array
    {
        // Generar código QR para pago
        return [
            'success' => true,
            'transaction_id' => 'qr_' . Str::random(15),
            'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        ];
    }

    private function processRefund(Payment $payment): array
    {
        // Simular procesamiento de reembolso
        return [
            'success' => true,
            'refund_id' => 'refund_' . Str::random(15)
        ];
    }

    private function generatePaymentReference(): string
    {
        return 'PAY-' . date('Ymd') . '-' . Str::random(8);
    }
}