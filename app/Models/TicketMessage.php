<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'is_internal',
        'message',
        'attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Ticket al que pertenece
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Usuario que escribió el mensaje
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
