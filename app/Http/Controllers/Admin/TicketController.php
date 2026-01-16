<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['client', 'service'])->latest()->paginate(15);
        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['client', 'service', 'replies.user']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        if ($request->boolean('close_ticket')) {
            $ticket->update(['status' => 'closed']);
        } else {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->back()
            ->with('success', 'Respuesta enviada exitosamente');
    }
}
