<?php

namespace App\Features\Payments\Services;

use App\Models\Booking;
use App\Features\Payments\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generateInvoice(Booking $booking): array
    {
        // Verificar que la reserva esté pagada
        if ($booking->payment_status !== 'paid') {
            throw new \Exception('Solo se pueden generar facturas para reservas pagadas');
        }

        // Buscar factura existente o crear nueva
        $invoice = Invoice::where('booking_id', $booking->id)->first();
        
        if (!$invoice) {
            $invoice = $this->createInvoice($booking);
        }

        return $this->formatInvoiceData($invoice);
    }

    public function generateInvoicePDF(Booking $booking): string
    {
        $invoice = $this->generateInvoice($booking);
        
        // En una implementación real, aquí usarías una librería como DomPDF o wkhtmltopdf
        // Por ahora, simularemos la generación del PDF
        
        $html = $this->generateInvoiceHTML($invoice);
        
        // Simulación de conversión a PDF
        return $this->convertHTMLToPDF($html);
    }

    public function sendInvoiceByEmail(Booking $booking): void
    {
        $invoice = $this->generateInvoice($booking);
        $pdfContent = $this->generateInvoicePDF($booking);
        
        // En una implementación real, aquí enviarías el email con el PDF adjunto
        // Por ahora, simularemos el envío
        
        // Mail::to($booking->user->email)->send(new InvoiceMail($invoice, $pdfContent));
        
        // Marcar como enviada
        Invoice::where('booking_id', $booking->id)->update([
            'email_sent_at' => now()
        ]);
    }

    private function createInvoice(Booking $booking): Invoice
    {
        return Invoice::create([
            'booking_id' => $booking->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $booking->total_amount,
            'tax_amount' => 0, // Bolivia no tiene IVA en turismo
            'total_amount' => $booking->total_amount,
            'status' => 'issued'
        ]);
    }

    private function formatInvoiceData(Invoice $invoice): array
    {
        $booking = $invoice->booking;
        
        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'status' => $invoice->status,
            
            // Información del cliente
            'customer' => [
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? 'N/A'
            ],
            
            // Información del tour
            'tour_details' => [
                'name' => $booking->tour->name,
                'attraction' => $booking->tour->attraction->name,
                'department' => $booking->tour->attraction->department->name,
                'date' => $booking->booking_date,
                'time' => $booking->booking_time,
                'people' => $booking->number_of_people
            ],
            
            // Información financiera
            'financial' => [
                'subtotal' => $invoice->subtotal,
                'tax_amount' => $invoice->tax_amount,
                'total_amount' => $invoice->total_amount,
                'payment_method' => $booking->payment_method,
                'payment_reference' => $booking->payment_reference
            ],
            
            // Información de la empresa
            'company' => [
                'name' => 'Pacha Tour',
                'address' => 'La Paz, Bolivia',
                'phone' => '+591 2 123-4567',
                'email' => 'info@pachatour.bo',
                'website' => 'www.pachatour.bo'
            ]
        ];
    }

    private function generateInvoiceHTML(array $invoice): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Factura {$invoice['invoice_number']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-info { margin-bottom: 20px; }
                .customer-info { margin-bottom: 20px; }
                .tour-details { margin-bottom: 20px; }
                .financial { margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                .total { font-weight: bold; font-size: 1.2em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>FACTURA</h1>
                <h2>Número: {$invoice['invoice_number']}</h2>
                <p>Fecha: {$invoice['issue_date']}</p>
            </div>
            
            <div class='company-info'>
                <h3>{$invoice['company']['name']}</h3>
                <p>{$invoice['company']['address']}</p>
                <p>Teléfono: {$invoice['company']['phone']}</p>
                <p>Email: {$invoice['company']['email']}</p>
            </div>
            
            <div class='customer-info'>
                <h3>Cliente:</h3>
                <p><strong>{$invoice['customer']['name']}</strong></p>
                <p>Email: {$invoice['customer']['email']}</p>
                <p>Teléfono: {$invoice['customer']['phone']}</p>
            </div>
            
            <div class='tour-details'>
                <h3>Detalles del Tour:</h3>
                <table>
                    <tr><td><strong>Tour:</strong></td><td>{$invoice['tour_details']['name']}</td></tr>
                    <tr><td><strong>Atractivo:</strong></td><td>{$invoice['tour_details']['attraction']}</td></tr>
                    <tr><td><strong>Departamento:</strong></td><td>{$invoice['tour_details']['department']}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>{$invoice['tour_details']['date']}</td></tr>
                    <tr><td><strong>Hora:</strong></td><td>{$invoice['tour_details']['time']}</td></tr>
                    <tr><td><strong>Personas:</strong></td><td>{$invoice['tour_details']['people']}</td></tr>
                </table>
            </div>
            
            <div class='financial'>
                <table>
                    <tr><td>Subtotal:</td><td>Bs. {$invoice['financial']['subtotal']}</td></tr>
                    <tr><td>Impuestos:</td><td>Bs. {$invoice['financial']['tax_amount']}</td></tr>
                    <tr class='total'><td>TOTAL:</td><td>Bs. {$invoice['financial']['total_amount']}</td></tr>
                </table>
                <p><strong>Método de pago:</strong> {$invoice['financial']['payment_method']}</p>
                <p><strong>Referencia:</strong> {$invoice['financial']['payment_reference']}</p>
            </div>
            
            <div style='margin-top: 40px; text-align: center;'>
                <p>¡Gracias por elegir Pacha Tour!</p>
                <p>Disfruta tu experiencia en Bolivia</p>
            </div>
        </body>
        </html>";
    }

    private function convertHTMLToPDF(string $html): string
    {
        // En una implementación real, aquí usarías DomPDF, wkhtmltopdf, etc.
        // Por ahora, retornamos el HTML como si fuera PDF
        return $html;
    }

    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Obtener el último número de factura del mes
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }
}