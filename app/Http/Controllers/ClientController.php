<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Listado de clientes
     */
    public function index(Request $request)
    {
        $query = Client::with('services');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->latest()->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Guardar nuevo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:10',
            'document_number' => 'nullable|string|max:50',
            'email' => 'required|email|unique:clients,email',
            'billing_email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
            'is_migration_mode' => 'boolean',
        ]);

        $client = Client::create($validated);

        // Si está en modo migración, no enviar notificaciones
        if ($client->is_migration_mode) {
            // Log de creación silenciosa
            \Log::info('Cliente creado en modo migración silenciosa', ['client_id' => $client->id]);
        }

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Ver detalle del cliente
     */
    public function show(Client $client)
    {
        $client->load(['services', 'invoices', 'payments', 'tickets']);
        
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:10',
            'document_number' => 'nullable|string|max:50',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'billing_email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
            'is_migration_mode' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Eliminar cliente (soft delete)
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
