<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function index()
    {
        $invoices = Invoice::with('client')->paginate(15);
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.invoices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'due_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'currency' => 'required|in:COP,USD',
        ]);

        $silentMode = $request->boolean('silent_mode', false);

        $invoice = $this->invoiceService->createInvoice([
            'client_id' => $request->client_id,
            'service_id' => $request->service_id,
            'document_type' => $request->document_type ?? 'invoice',
            'due_date' => $request->due_date,
            'subtotal' => $request->subtotal,
            'tax_amount' => $request->tax_amount ?? 0,
            'total' => $request->total,
            'currency' => $request->currency,
            'notes' => $request->notes,
        ], $silentMode);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Factura creada exitosamente');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'service', 'payments']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'service']);
        
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        return $pdf->download("factura_{$invoice->invoice_number}.pdf");
    }
}
