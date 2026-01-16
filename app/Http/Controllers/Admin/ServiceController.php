<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceRenewalService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('client')->paginate(15);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required',
            'type' => 'required|in:one_time,recurring',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:COP,USD',
            'start_date' => 'required|date',
        ]);

        $service = Service::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'billing_cycle' => $request->billing_cycle,
            'price' => $request->price,
            'currency' => $request->currency,
            'start_date' => $request->start_date,
            'current_due_date' => $request->current_due_date ?? $request->start_date,
            'status' => 'active',
            'credentials' => $request->credentials ? json_decode($request->credentials, true) : null,
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Servicio creado exitosamente');
    }

    public function show(Service $service)
    {
        $service->load(['client', 'invoices', 'payments']);
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($request->only([
            'name', 'description', 'price', 'currency', 'billing_cycle',
            'status', 'credentials',
        ]));

        return redirect()->route('admin.services.index')
            ->with('success', 'Servicio actualizado exitosamente');
    }

    public function renew(Request $request, Service $service)
    {
        app(ServiceRenewalService::class)->renewServiceFromPayment(
            $service->payments()->latest()->first()
        );

        return redirect()->back()
            ->with('success', 'Servicio renovado exitosamente');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')
            ->with('success', 'Servicio eliminado exitosamente');
    }
}
