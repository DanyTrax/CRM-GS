<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'tax_id',
        'email_login',
        'email_billing',
        'phone',
        'address',
        'status',
    ];

    /**
     * Servicios del cliente
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Facturas del cliente
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Pagos del cliente
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Obtener el email de facturación (o el principal si no tiene)
     */
    public function getBillingEmail(): string
    {
        return $this->email_billing ?? $this->email_login;
    }

    /**
     * Verificar si el cliente está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'activo';
    }
}
