<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client;
        
        $activeServices = $client->services()->where('status', 'active')->count();
        $pendingInvoices = $client->invoices()->where('status', 'pending')->sum('total');
        $openTickets = $client->tickets()->where('status', 'open')->count();

        return view('client.dashboard', compact(
            'activeServices',
            'pendingInvoices',
            'openTickets'
        ));
    }

    public function services()
    {
        $client = Auth::user()->client;
        $services = $client->services()->with('invoices')->get();
        
        return view('client.services', compact('services'));
    }

    public function invoices()
    {
        $client = Auth::user()->client;
        $invoices = $client->invoices()->latest()->paginate(15);
        
        return view('client.invoices', compact('invoices'));
    }

    public function tickets()
    {
        $client = Auth::user()->client;
        $tickets = $client->tickets()->with('replies')->latest()->paginate(15);
        
        return view('client.tickets', compact('tickets'));
    }
}
