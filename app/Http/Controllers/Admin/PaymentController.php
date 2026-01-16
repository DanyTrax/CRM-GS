<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ServiceRenewalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['invoice', 'service'])->paginate(15);
        return view('admin.payments.index', compact('payments'));
    }

    public function approve(Request $request, Payment $payment)
    {
        $payment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Actualizar factura
        $payment->invoice->update(['status' => 'paid']);

        // Renovar servicio si aplica
        if ($payment->service_id) {
            app(ServiceRenewalService::class)->renewServiceFromPayment($payment);
        }

        return redirect()->back()
            ->with('success', 'Pago aprobado exitosamente');
    }

    public function reject(Request $request, Payment $payment)
    {
        $payment->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? $payment->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Pago rechazado');
    }
}
