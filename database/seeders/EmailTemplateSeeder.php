<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Cobro Amable',
                'subject' => 'Recordatorio de Pago - {{invoice_number}}',
                'type' => 'invoice',
                'body' => '<p>Estimado/a {{client_name}},</p><p>Le recordamos que tiene una factura pendiente de pago:</p><p><strong>Número:</strong> {{invoice_number}}<br><strong>Monto:</strong> {{total}} {{currency}}<br><strong>Vencimiento:</strong> {{due_date}}</p><p>Puede realizar el pago desde su área de cliente.</p><p>Saludos cordiales.</p>',
            ],
            [
                'name' => 'Cobro Formal',
                'subject' => 'Factura Pendiente - {{invoice_number}}',
                'type' => 'invoice',
                'body' => '<p>Estimado/a {{client_name}},</p><p>Le informamos que tiene una factura pendiente de pago:</p><p><strong>Número:</strong> {{invoice_number}}<br><strong>Monto:</strong> {{total}} {{currency}}<br><strong>Vencimiento:</strong> {{due_date}}</p><p>Por favor, realice el pago a la brevedad posible para evitar inconvenientes.</p><p>Atentamente.</p>',
            ],
            [
                'name' => 'Ultimátum',
                'subject' => 'URGENTE: Factura Vencida - {{invoice_number}}',
                'type' => 'invoice',
                'body' => '<p>Estimado/a {{client_name}},</p><p>Le informamos que su factura <strong>{{invoice_number}}</strong> se encuentra vencida.</p><p><strong>Monto:</strong> {{total}} {{currency}}<br><strong>Vencimiento:</strong> {{due_date}}</p><p>Es necesario que realice el pago de forma inmediata para evitar la suspensión de servicios.</p><p>Atentamente.</p>',
            ],
            [
                'name' => 'Bienvenida',
                'subject' => 'Bienvenido a {{app_name}}',
                'type' => 'welcome',
                'body' => '<p>Estimado/a {{client_name}},</p><p>Le damos la bienvenida a nuestro sistema.</p><p>Sus credenciales de acceso son:<br><strong>Email:</strong> {{email}}<br><strong>Contraseña temporal:</strong> {{password}}</p><p>Por favor, cambie su contraseña al iniciar sesión por primera vez.</p><p>Saludos cordiales.</p>',
            ],
            [
                'name' => 'Pago Recibido',
                'subject' => 'Confirmación de Pago - {{invoice_number}}',
                'type' => 'payment',
                'body' => '<p>Estimado/a {{client_name}},</p><p>Hemos recibido su pago por la factura <strong>{{invoice_number}}</strong>.</p><p><strong>Monto:</strong> {{amount}} {{currency}}<br><strong>Fecha:</strong> {{payment_date}}</p><p>Gracias por su pago oportuno.</p><p>Saludos cordiales.</p>',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
