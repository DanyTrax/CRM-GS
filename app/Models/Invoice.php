<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'service_id',
        'invoice_number',
        'prefix',
        'consecutive_number',
        'document_type',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total',
        'currency',
        'exchange_rate',
        'spread_percentage',
        'status',
        'notes',
        'tax_breakdown',
        'dian_resolution',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'spread_percentage' => 'decimal:2',
            'tax_breakdown' => 'array',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->due_date->isPast() && $this->status !== 'paid');
    }

    /**
     * Convierte el monto de USD a COP usando TRM y spread
     */
    public function convertToCOP(float $usdAmount, float $exchangeRate, float $spread = 0): float
    {
        $rateWithSpread = $exchangeRate * (1 + ($spread / 100));
        return $usdAmount * $rateWithSpread;
    }
}
