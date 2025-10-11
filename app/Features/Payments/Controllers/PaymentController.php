<?php

namespace App\Features\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Payments\Services\PaymentService;
use App\Features\Payments\Requests\ProcessPaymentRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function processPayment(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $booking = Booking::findOrFail($request->booking_id);
            
            $result = $this->paymentService->processPayment(
                $booking,
                $request->payment_method,
                $request->payment_data
            );

            return response()->json([
                'success' => true,
                'payment_id' => $result['payment_id'],
                'status' => $result['status'],
                'message' => 'Pago procesado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getPaymentMethods(): JsonResponse
    {
        $methods = $this->paymentService->getAvailablePaymentMethods();
        
        return response()->json([
            'payment_methods' => $methods
        ]);
    }

    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $status = $this->paymentService->getPaymentStatus($paymentId);
            
            return response()->json([
                'payment_id' => $paymentId,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment not found'
            ], 404);
        }
    }

    public function refundPayment(string $paymentId): JsonResponse
    {
        try {
            $result = $this->paymentService->refundPayment($paymentId);
            
            return response()->json([
                'success' => true,
                'refund_id' => $result['refund_id'],
                'message' => 'Reembolso procesado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el reembolso: ' . $e->getMessage()
            ], 400);
        }
    }
}