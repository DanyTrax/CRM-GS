<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'type',
        'billing_cycle',
        'price',
        'currency',
        'start_date',
        'current_due_date',
        'next_due_date',
        'status',
        'credentials',
        'cancellation_reason',
        'cancellation_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'current_due_date' => 'date',
            'next_due_date' => 'date',
            'price' => 'decimal:2',
            'credentials' => 'array',
            'cancellation_requested_at' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Renueva el servicio basándose en la fecha de vencimiento actual
     * NO en la fecha de pago (anti-fraude)
     */
    public function renew($period = null): void
    {
        $period = $period ?: $this->billing_cycle;
        
        $currentDueDate = $this->current_due_date ?? Carbon::now();
        $newDueDate = $this->calculateNextDueDate($currentDueDate, $period);

        $this->update([
            'current_due_date' => $newDueDate,
            'next_due_date' => $newDueDate,
            'status' => 'active',
        ]);
    }

    /**
     * Calcula la próxima fecha de vencimiento según el ciclo
     */
    protected function calculateNextDueDate(Carbon $fromDate, string $cycle): Carbon
    {
        return match($cycle) {
            'monthly' => $fromDate->copy()->addMonth(),
            'quarterly' => $fromDate->copy()->addMonths(3),
            'semiannual' => $fromDate->copy()->addMonths(6),
            'annual' => $fromDate->copy()->addYear(),
            'biannual' => $fromDate->copy()->addYears(2),
            'triennal' => $fromDate->copy()->addYears(3),
            default => $fromDate->copy()->addMonth(),
        };
    }

    public function isExpired(): bool
    {
        return $this->current_due_date && $this->current_due_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
