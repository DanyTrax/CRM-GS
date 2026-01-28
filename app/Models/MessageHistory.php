<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'message_history';

    protected $fillable = [
        'message_type',
        'recipient_type',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'subject',
        'body',
        'template_type',
        'template_id',
        'status',
        'provider',
        'external_id',
        'error_message',
        'metadata',
        'sent_at',
        'delivered_at',
        'read_at',
        'sent_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Usuario que envió el mensaje
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Plantilla usada
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Marcar como enviado
     */
    public function markAsSent(string $externalId = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'external_id' => $externalId,
        ]);
    }

    /**
     * Marcar como entregado
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Marcar como leído
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
