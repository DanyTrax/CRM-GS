<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            text-align: left;
            margin-top: 20px;
        }
        .invoice-info {
            margin-top: 20px;
            text-align: right;
        }
        .invoice-details {
            margin: 30px 0;
        }
        .client-info, .invoice-data {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .invoice-data {
            text-align: right;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px 10px;
        }
        .totals .total-row {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .currency-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURA DE VENTA</h1>
        <div class="company-info">
            <strong>EMPRESA DE SERVICIOS</strong><br>
            NIT: 900.000.000-1<br>
            Dirección: Calle 123 #45-67<br>
            Teléfono: (57) 1 234 5678<br>
            Email: facturacion@empresa.com
        </div>
        <div class="invoice-info">
            <strong>Factura No:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Fecha de Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
            <strong>Fecha de Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
            <strong>Estado:</strong> {{ strtoupper($invoice->status) }}
        </div>
    </div>

    <div class="invoice-details">
        <div class="client-info">
            <h3>DATOS DEL CLIENTE</h3>
            <strong>{{ $invoice->client->name }}</strong><br>
            @if($invoice->client->document_type && $invoice->client->document_number)
            {{ strtoupper($invoice->client->document_type) }}: {{ $invoice->client->document_number }}<br>
            @endif
            Email: {{ $invoice->client->getBillingEmail() }}<br>
            @if($invoice->client->phone)
            Teléfono: {{ $invoice->client->phone }}<br>
            @endif
            @if($invoice->client->address)
            Dirección: {{ $invoice->client->address }}<br>
            @endif
        </div>
    </div>

    @if($invoice->concept)
    <div style="margin: 20px 0;">
        <h4>CONCEPTO:</h4>
        <p>{{ $invoice->concept }}</p>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Impuesto</th>
                <th class="text-right">Descuento</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($invoice->service)
                        {{ $invoice->service->name }}
                        @if($invoice->service->description)
                        <br><small>{{ $invoice->service->description }}</small>
                        @endif
                    @else
                        {{ $invoice->concept ?? 'Servicio' }}
                    @endif
                </td>
                <td class="text-right">${{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($invoice->discount, 2, ',', '.') }}</td>
                <td class="text-right">
                    <strong>${{ number_format($invoice->total, 2, ',', '.') }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    @if($invoice->currency === 'USD' && $invoice->exchange_rate)
    <div class="currency-info">
        <strong>Información de Conversión:</strong><br>
        Monto en USD: ${{ number_format($invoice->usd_amount ?? $invoice->total, 2, ',', '.') }}<br>
        TRM + Spread: ${{ number_format($invoice->exchange_rate, 2, ',', '.') }}<br>
        Monto en COP: ${{ number_format($invoice->cop_amount ?? $invoice->total, 2, ',', '.') }}
    </div>
    @endif

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">${{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Impuestos:</td>
                <td class="text-right">${{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td>Descuento:</td>
                <td class="text-right">-${{ number_format($invoice->discount, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="text-right">${{ number_format($invoice->total, 2, ',', '.') }} {{ $invoice->currency }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 30px;">
        <strong>Notas:</strong><br>
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        <p>Esta factura fue generada electrónicamente y es válida sin firma.</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
