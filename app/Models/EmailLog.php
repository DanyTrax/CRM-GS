<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'to_email',
        'subject',
        'body',
        'status',
        'error_message',
        'template_name',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
