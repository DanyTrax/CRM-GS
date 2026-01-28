<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Listado de servicios
     */
    public function index(Request $request)
    {
        $query = Service::with(['client']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expiration_date', '<', now());
            }
        }

        $services = $query->latest()->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    /**
     * Formulario de creación
     */
    public function create(Request $request)
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $clientId = $request->get('client_id');

        return view('admin.services.create', compact('clients', 'clientId'));
    }

    /**
     * Guardar nuevo servicio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:unique,recurrent',
            'billing_cycle_months' => 'required_if:type,recurrent|integer|min:1|max:36',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,COP',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'expiration_date' => 'required|date|after:start_date',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Si es único, no necesita ciclo
        if ($validated['type'] === 'unique') {
            $validated['billing_cycle_months'] = 0;
            $validated['auto_renew'] = false;
        }

        // Calcular próxima fecha de facturación (7 días antes del vencimiento)
        $validated['next_billing_date'] = Carbon::parse($validated['expiration_date'])->subDays(7);

        $service = Service::create($validated);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Ver detalle del servicio
     */
    public function show(Service $service)
    {
        $service->load(['client', 'invoices', 'renewals']);

        return view('admin.services.show', compact('service'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Service $service)
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'clients'));
    }

    /**
     * Actualizar servicio
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:unique,recurrent',
            'billing_cycle_months' => 'required_if:type,recurrent|integer|min:1|max:36',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,COP',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'expiration_date' => 'required|date|after:start_date',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $service->update($validated);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Renovar servicio manualmente
     */
    public function renew(Request $request, Service $service)
    {
        $request->validate([
            'months_to_add' => 'nullable|integer|min:1|max:36',
        ]);

        $monthsToAdd = $request->months_to_add ?? $service->billing_cycle_months;

        try {
            $renewal = $service->renew($monthsToAdd);

            return redirect()
                ->route('admin.services.show', $service)
                ->with('success', "Servicio renovado exitosamente. Nueva fecha de vencimiento: {$renewal->new_expiration_date->format('d/m/Y')}");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al renovar servicio: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar ciclo de facturación (Upselling)
     */
    public function changeCycle(Request $request, Service $service)
    {
        $request->validate([
            'new_cycle_months' => 'required|integer|min:1|max:36',
        ]);

        try {
            $result = $service->changeBillingCycle($request->new_cycle_months);

            return redirect()
                ->route('admin.services.show', $service)
                ->with('success', "Ciclo cambiado. Diferencial a cobrar: $" . number_format($result['differential_amount'], 2));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar ciclo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar servicio (soft delete)
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }
}
