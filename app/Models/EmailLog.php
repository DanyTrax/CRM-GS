<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'original_body',
        'was_edited',
        'status',
        'message_id',
        'error_message',
        'mailable_type',
        'mailable_id',
        'sent_by',
        'suppressed_by_migration_mode',
        'sent_at',
    ];

    protected $casts = [
        'was_edited' => 'boolean',
        'suppressed_by_migration_mode' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * Usuario que envió el correo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Relación polimórfica con el mailable
     */
    public function mailable()
    {
        return $this->morphTo();
    }

    /**
     * Marcar como enviado
     */
    public function markAsSent(string $messageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
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
