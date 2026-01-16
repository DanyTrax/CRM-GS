<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\EmailInterceptorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('user')->paginate(15);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:clients,email',
            'id_type' => 'required',
            'id_number' => 'required|unique:clients,id_number',
            'entity_type' => 'required|in:natural,juridical',
        ]);

        $silentMode = $request->boolean('silent_mode', false);

        // Crear usuario si se proporciona contraseña
        $user = null;
        if ($request->filled('password')) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            $user->assignRole('Cliente');
        }

        $client = Client::create([
            'user_id' => $user?->id,
            'entity_type' => $request->entity_type,
            'name' => $request->name,
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'address' => $request->address,
            'city' => $request->city,
            'phone' => $request->phone,
            'email' => $request->email,
            'billing_email' => $request->billing_email,
            'status' => $request->status ?? 'draft',
            'notes' => $request->notes,
        ]);

        // Enviar correo de bienvenida si no es modo silencioso
        if (!$silentMode && $user) {
            app(EmailInterceptorService::class)->sendEmail([
                'to_email' => $client->billing_email ?: $client->email,
                'subject' => 'Bienvenido a ' . config('app.name'),
                'body' => view('emails.welcome', ['client' => $client, 'password' => $request->password])->render(),
            ], $client, false);
        }

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function show(Client $client)
    {
        $client->load(['services', 'invoices', 'tickets', 'emailLogs']);
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'id_type' => 'required',
            'id_number' => 'required|unique:clients,id_number,' . $client->id,
        ]);

        $client->update($request->only([
            'name', 'email', 'billing_email', 'id_type', 'id_number',
            'address', 'city', 'phone', 'status', 'notes',
        ]));

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }
}
