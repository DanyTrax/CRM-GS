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
        'total_amount',
        'currency',
        'trm_snapshot',
        'status',
        'pdf_template',
        'issue_date',
        'due_date',
        'concept',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'trm_snapshot' => 'decimal:4',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Cliente de la factura
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Servicio asociado
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Pagos de la factura
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Generar número de factura único
     */
    public static function generateInvoiceNumber(string $template = 'legal'): string
    {
        $prefix = $template === 'cuenta_cobro' ? 'CTA' : 'FAC';
        $year = now()->year;
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid(\Carbon\Carbon $paidDate = null): void
    {
        $this->update([
            'status' => 'pagada',
        ]);
    }

    /**
     * Verificar si está pagada
     */
    public function isPaid(): bool
    {
        return $this->status === 'pagada';
    }
}
