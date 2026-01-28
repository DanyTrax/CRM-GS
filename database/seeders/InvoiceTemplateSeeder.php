<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceTemplate;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Factura Legal Estándar',
                'type' => 'invoice',
                'html_content' => $this->getInvoiceTemplate(),
                'is_active' => true,
                'is_default' => true,
                'description' => 'Plantilla estándar para facturas legales',
            ],
            [
                'name' => 'Remisión de Mercancía',
                'type' => 'remision',
                'html_content' => $this->getRemisionTemplate(),
                'is_active' => true,
                'is_default' => true,
                'description' => 'Plantilla para remisiones de mercancía',
            ],
            [
                'name' => 'Cuenta de Cobro',
                'type' => 'cuenta_cobro',
                'html_content' => $this->getCuentaCobroTemplate(),
                'is_active' => true,
                'is_default' => true,
                'description' => 'Plantilla para cuentas de cobro',
            ],
        ];

        foreach ($templates as $template) {
            InvoiceTemplate::updateOrCreate(
                ['name' => $template['name'], 'type' => $template['type']],
                $template
            );
        }
    }

    private function getInvoiceTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{invoice_number}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .invoice-info { float: right; width: 45%; text-align: right; }
        .client-info { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 1.2em; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURA DE VENTA</h1>
        <div class="company-info">
            <strong>{{company_name}}</strong><br>
            NIT: {{company_tax_id}}<br>
            Dirección: {{company_address}}<br>
            Teléfono: {{company_phone}}<br>
            Email: {{company_email}}
        </div>
        <div class="invoice-info">
            <strong>Factura No:</strong> {{invoice_number}}<br>
            <strong>Fecha de Emisión:</strong> {{issue_date}}<br>
            <strong>Fecha de Vencimiento:</strong> {{due_date}}<br>
            <strong>Estado:</strong> {{status}}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="client-info">
        <h3>DATOS DEL CLIENTE</h3>
        <strong>{{client_name}}</strong><br>
        NIT/CC: {{client_tax_id}}<br>
        Email: {{client_email}}<br>
        Teléfono: {{client_phone}}<br>
        Dirección: {{client_address}}
    </div>

    <div>
        <h4>CONCEPTO:</h4>
        <p>{{concept}}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{service_name}}</td>
                <td>1</td>
                <td>{{unit_price}}</td>
                <td>{{total_amount}}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        <p><strong>TOTAL: {{currency}} {{total_amount}}</strong></p>
        @if(currency == "USD")
        <p>TRM: {{trm_snapshot}}</p>
        <p>Total en COP: {{total_cop}}</p>
        @endif
    </div>
</body>
</html>';
    }

    private function getRemisionTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Remisión {{invoice_number}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .remision-info { float: right; width: 45%; text-align: right; }
        .client-info { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REMISIÓN</h1>
        <div class="company-info">
            <strong>{{company_name}}</strong><br>
            NIT: {{company_tax_id}}<br>
            Dirección: {{company_address}}
        </div>
        <div class="remision-info">
            <strong>Remisión No:</strong> {{invoice_number}}<br>
            <strong>Fecha:</strong> {{issue_date}}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="client-info">
        <h3>DATOS DEL CLIENTE</h3>
        <strong>{{client_name}}</strong><br>
        NIT/CC: {{client_tax_id}}<br>
        Dirección: {{client_address}}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{service_name}}</td>
                <td>1</td>
                <td>{{concept}}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>';
    }

    private function getCuentaCobroTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cuenta de Cobro {{invoice_number}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .account-info { float: right; width: 45%; text-align: right; }
        .client-info { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 1.2em; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CUENTA DE COBRO</h1>
        <div class="company-info">
            <strong>{{company_name}}</strong><br>
            NIT: {{company_tax_id}}<br>
            Dirección: {{company_address}}
        </div>
        <div class="account-info">
            <strong>Cuenta No:</strong> {{invoice_number}}<br>
            <strong>Fecha:</strong> {{issue_date}}<br>
            <strong>Vence:</strong> {{due_date}}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="client-info">
        <h3>SEÑOR(ES):</h3>
        <strong>{{client_name}}</strong><br>
        NIT/CC: {{client_tax_id}}<br>
        Dirección: {{client_address}}
    </div>

    <div>
        <h4>CONCEPTO:</h4>
        <p>{{concept}}</p>
    </div>

    <div class="total">
        <p><strong>VALOR A COBRAR: {{currency}} {{total_amount}}</strong></p>
    </div>
</body>
</html>';
    }
}
