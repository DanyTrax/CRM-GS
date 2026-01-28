<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Listado de facturas
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'service']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $invoices = $query->latest()->paginate(15);

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Formulario de creación
     */
    public function create(Request $request)
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $services = Service::where('is_active', true)->with('client')->get();
        
        $clientId = $request->get('client_id');
        $serviceId = $request->get('service_id');

        return view('admin.invoices.create', compact('clients', 'services', 'clientId', 'serviceId'));
    }

    /**
     * Guardar nueva factura
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'type' => 'required|in:invoice,payment_receipt,billing_account',
            'concept' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'required|in:USD,COP',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Generar número de factura
        $validated['invoice_number'] = Invoice::generateInvoiceNumber($validated['type']);
        $validated['status'] = 'draft';

        // Si tiene servicio, usar precio del servicio
        if (isset($validated['service_id'])) {
            $service = Service::find($validated['service_id']);
            if ($service && !isset($validated['subtotal'])) {
                $validated['subtotal'] = $service->price;
                $validated['currency'] = $service->currency;
            }
        }

        $invoice = Invoice::create($validated);

        // Calcular totales (incluye conversión USD->COP si aplica)
        $invoice->calculateTotals();

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Factura creada exitosamente.');
    }

    /**
     * Ver detalle de factura
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'service', 'payments', 'renewals']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Generar PDF de factura
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client', 'service']);

        // Determinar plantilla según tipo
        $template = match($invoice->type) {
            'invoice' => 'pdfs.invoice',
            'payment_receipt' => 'pdfs.payment_receipt',
            'billing_account' => 'pdfs.billing_account',
            default => 'pdfs.invoice',
        };

        $pdf = Pdf::loadView($template, compact('invoice'));

        $filename = "{$invoice->invoice_number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Marcar factura como pagada
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'paid_date' => 'nullable|date',
        ]);

        $invoice->markAsPaid($request->paid_date ? \Carbon\Carbon::parse($request->paid_date) : null);

        // Si tiene servicio, renovarlo automáticamente
        if ($invoice->service && $invoice->service->type === 'recurrent' && $invoice->service->auto_renew) {
            $invoice->service->renew();
        }

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Factura marcada como pagada.');
    }
}
