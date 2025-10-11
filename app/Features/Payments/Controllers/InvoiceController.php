<?php

namespace App\Features\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Payments\Services\InvoiceService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
        $this->middleware('auth');
    }

    public function generateInvoice(Booking $booking): JsonResponse
    {
        try {
            // Verificar que el usuario puede acceder a esta factura
            if (!$this->canAccessBooking($booking)) {
                return response()->json([
                    'error' => 'No tienes permisos para acceder a esta factura'
                ], 403);
            }

            $invoice = $this->invoiceService->generateInvoice($booking);

            return response()->json([
                'success' => true,
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadInvoice(Booking $booking): Response
    {
        try {
            if (!$this->canAccessBooking($booking)) {
                abort(403, 'No tienes permisos para descargar esta factura');
            }

            $pdfContent = $this->invoiceService->generateInvoicePDF($booking);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="factura-' . $booking->id . '.pdf"');

        } catch (\Exception $e) {
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    public function getInvoiceHistory(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            // Los admins pueden ver todas las facturas
            $bookings = Booking::with(['tour.attraction', 'user', 'payments'])
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Los usuarios solo ven sus propias facturas
            $bookings = $user->bookings()
                ->with(['tour.attraction', 'payments'])
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return response()->json([
            'invoices' => $bookings->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'tour_name' => $booking->tour->name ?? 'N/A',
                    'attraction_name' => $booking->tour->attraction->name ?? 'N/A',
                    'user_name' => $booking->user->name ?? 'N/A',
                    'total_amount' => $booking->total_amount,
                    'booking_date' => $booking->booking_date,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'download_url' => route('invoices.download', $booking->id)
                ];
            }),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total()
            ]
        ]);
    }

    public function sendInvoiceByEmail(Booking $booking): JsonResponse
    {
        try {
            if (!$this->canAccessBooking($booking)) {
                return response()->json([
                    'error' => 'No tienes permisos para enviar esta factura'
                ], 403);
            }

            $this->invoiceService->sendInvoiceByEmail($booking);

            return response()->json([
                'success' => true,
                'message' => 'Factura enviada por correo electrónico'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    private function canAccessBooking(Booking $booking): bool
    {
        $user = auth()->user();
        
        // Los admins pueden acceder a cualquier reserva
        if ($user->role === 'admin') {
            return true;
        }

        // Los usuarios solo pueden acceder a sus propias reservas
        return $booking->user_id === $user->id;
    }
}