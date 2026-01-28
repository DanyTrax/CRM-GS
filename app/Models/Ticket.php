<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'client_id',
        'service_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Servicio relacionado
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Usuario asignado
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Mensajes del ticket
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * Generar número de ticket único
     */
    public static function generateTicketNumber(): string
    {
        $year = now()->year;
        $lastTicket = self::where('ticket_number', 'like', "TKT-{$year}-%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "TKT-{$year}-{$newNumber}";
    }
}
