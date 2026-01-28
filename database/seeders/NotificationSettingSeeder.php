<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationSetting;
use App\Models\EmailTemplate;

class NotificationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // SERVICIOS
            [
                'module' => 'service',
                'event_type' => 'expiring',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'service_expiring')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando su servicio está próximo a vencer',
            ],
            [
                'module' => 'service',
                'event_type' => 'expiring',
                'recipient_type' => 'admin',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'service_expiring')->where('recipient_type', 'admin')->first()?->id,
                'description' => 'Notificar al administrador cuando un servicio está próximo a vencer',
            ],
            [
                'module' => 'service',
                'event_type' => 'expired',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'service_expired_grace')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando su servicio ha vencido (periodo de gracia)',
            ],
            [
                'module' => 'service',
                'event_type' => 'suspended',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'service_expired_suspended')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando su servicio ha sido suspendido',
            ],
            [
                'module' => 'service',
                'event_type' => 'cancelled',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'service_cancelled')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando su servicio ha sido cancelado',
            ],
            
            // FACTURAS
            [
                'module' => 'invoice',
                'event_type' => 'created',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'invoice_created')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando se crea una factura',
            ],
            [
                'module' => 'invoice',
                'event_type' => 'overdue',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => null, // Se puede crear plantilla específica
                'description' => 'Notificar al cliente cuando una factura está vencida',
            ],
            
            // PAGOS
            [
                'module' => 'payment',
                'event_type' => 'received',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'payment_received')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando se recibe un pago',
            ],
            [
                'module' => 'payment',
                'event_type' => 'approved',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => null,
                'description' => 'Notificar al cliente cuando un pago es aprobado',
            ],
            
            // TICKETS
            [
                'module' => 'ticket',
                'event_type' => 'created',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'ticket_created')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando se crea un ticket',
            ],
            [
                'module' => 'ticket',
                'event_type' => 'replied',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'ticket_replied')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al cliente cuando hay una respuesta a su ticket',
            ],
            
            // USUARIOS
            [
                'module' => 'user',
                'event_type' => 'created',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'welcome')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al usuario cuando se crea su cuenta',
            ],
            [
                'module' => 'user',
                'event_type' => 'password_reset',
                'recipient_type' => 'user',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'in_app_enabled' => true,
                'template_id' => EmailTemplate::where('type', 'password_reset')->where('recipient_type', 'user')->first()?->id,
                'description' => 'Notificar al usuario cuando solicita recuperación de contraseña',
            ],
        ];

        foreach ($settings as $setting) {
            NotificationSetting::updateOrCreate(
                [
                    'module' => $setting['module'],
                    'event_type' => $setting['event_type'],
                    'recipient_type' => $setting['recipient_type'],
                ],
                $setting
            );
        }
    }
}
