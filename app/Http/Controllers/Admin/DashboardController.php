<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_clients' => Client::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'total_revenue' => Payment::where('status', 'approved')->sum('amount'),
            'open_tickets' => Ticket::where('status', 'open')->count(),
        ];

        $recentInvoices = Invoice::with('client')->latest()->take(5)->get();
        $recentPayments = Payment::with(['invoice', 'service'])->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentInvoices', 'recentPayments'));
    }
}
