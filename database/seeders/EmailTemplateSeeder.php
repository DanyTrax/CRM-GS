<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // BIENVENIDA - CLIENTE
            [
                'name' => 'Bienvenida - Cliente',
                'type' => 'welcome',
                'recipient_type' => 'user',
                'subject' => '¡Bienvenido a {{company_name}}!',
                'body' => $this->getWelcomeUserBody(),
                'variables' => ['company_name', 'client_name', 'login_url', 'support_email'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // BIENVENIDA - ADMIN
            [
                'name' => 'Bienvenida - Administrativo',
                'type' => 'welcome',
                'recipient_type' => 'admin',
                'subject' => 'Nuevo cliente registrado: {{client_name}}',
                'body' => $this->getWelcomeAdminBody(),
                'variables' => ['client_name', 'client_email', 'registration_date'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // CREACIÓN/RECUPERACIÓN DE CONTRASEÑA - CLIENTE
            [
                'name' => 'Creación de Contraseña - Cliente',
                'type' => 'password_reset',
                'recipient_type' => 'user',
                'subject' => 'Crea tu contraseña - {{company_name}}',
                'body' => $this->getPasswordResetUserBody(),
                'variables' => ['company_name', 'client_name', 'reset_link', 'expires_in'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // PRÓXIMO VENCIMIENTO - CLIENTE
            [
                'name' => 'Próximo Vencimiento - Cliente',
                'type' => 'service_expiring',
                'recipient_type' => 'user',
                'subject' => 'Recordatorio: Tu servicio {{service_name}} vence pronto',
                'body' => $this->getServiceExpiringUserBody(),
                'variables' => ['client_name', 'service_name', 'due_date', 'amount', 'payment_link'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // PRÓXIMO VENCIMIENTO - ADMIN
            [
                'name' => 'Próximo Vencimiento - Administrativo',
                'type' => 'service_expiring',
                'recipient_type' => 'admin',
                'subject' => 'Alerta: Servicio próximo a vencer - {{service_name}}',
                'body' => $this->getServiceExpiringAdminBody(),
                'variables' => ['client_name', 'service_name', 'due_date', 'amount'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // PRODUCTO ÚNICO ADQUIRIDO - CLIENTE
            [
                'name' => 'Producto Único Adquirido - Cliente',
                'type' => 'product_purchased',
                'recipient_type' => 'user',
                'subject' => 'Confirmación de compra: {{service_name}}',
                'body' => $this->getProductPurchasedUserBody(),
                'variables' => ['client_name', 'service_name', 'amount', 'invoice_number', 'invoice_link'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // PRODUCTO ÚNICO ADQUIRIDO - ADMIN
            [
                'name' => 'Producto Único Adquirido - Administrativo',
                'type' => 'product_purchased',
                'recipient_type' => 'admin',
                'subject' => 'Nueva compra de producto único: {{service_name}}',
                'body' => $this->getProductPurchasedAdminBody(),
                'variables' => ['client_name', 'service_name', 'amount', 'invoice_number'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO VENCIDO - PERIODO DE GRACIA - CLIENTE
            [
                'name' => 'Servicio Vencido - Periodo de Gracia - Cliente',
                'type' => 'service_expired_grace',
                'recipient_type' => 'user',
                'subject' => 'Importante: Tu servicio {{service_name}} está en periodo de gracia',
                'body' => $this->getServiceExpiredGraceUserBody(),
                'variables' => ['client_name', 'service_name', 'expired_date', 'grace_period_days', 'amount', 'payment_link'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO VENCIDO - PERIODO DE GRACIA - ADMIN
            [
                'name' => 'Servicio Vencido - Periodo de Gracia - Administrativo',
                'type' => 'service_expired_grace',
                'recipient_type' => 'admin',
                'subject' => 'Alerta: Servicio en periodo de gracia - {{service_name}}',
                'body' => $this->getServiceExpiredGraceAdminBody(),
                'variables' => ['client_name', 'service_name', 'expired_date', 'grace_period_days', 'amount'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO VENCIDO Y SUSPENDIDO - CLIENTE
            [
                'name' => 'Servicio Vencido y Suspendido - Cliente',
                'type' => 'service_expired_suspended',
                'recipient_type' => 'user',
                'subject' => 'Urgente: Tu servicio {{service_name}} ha sido suspendido',
                'body' => $this->getServiceExpiredSuspendedUserBody(),
                'variables' => ['client_name', 'service_name', 'expired_date', 'amount', 'payment_link', 'support_email'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO VENCIDO Y SUSPENDIDO - ADMIN
            [
                'name' => 'Servicio Vencido y Suspendido - Administrativo',
                'type' => 'service_expired_suspended',
                'recipient_type' => 'admin',
                'subject' => 'Alerta Crítica: Servicio suspendido - {{service_name}}',
                'body' => $this->getServiceExpiredSuspendedAdminBody(),
                'variables' => ['client_name', 'service_name', 'expired_date', 'amount'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO CANCELADO - CLIENTE
            [
                'name' => 'Servicio Cancelado - Cliente',
                'type' => 'service_cancelled',
                'recipient_type' => 'user',
                'subject' => 'Confirmación de cancelación: {{service_name}}',
                'body' => $this->getServiceCancelledUserBody(),
                'variables' => ['client_name', 'service_name', 'cancellation_date', 'reason'],
                'is_active' => true,
                'auto_send' => true,
            ],
            
            // SERVICIO CANCELADO - ADMIN
            [
                'name' => 'Servicio Cancelado - Administrativo',
                'type' => 'service_cancelled',
                'recipient_type' => 'admin',
                'subject' => 'Servicio cancelado: {{service_name}}',
                'body' => $this->getServiceCancelledAdminBody(),
                'variables' => ['client_name', 'service_name', 'cancellation_date', 'reason'],
                'is_active' => true,
                'auto_send' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name'], 'type' => $template['type'], 'recipient_type' => $template['recipient_type']],
                $template
            );
        }
    }

    private function getWelcomeUserBody(): string
    {
        return '<h2>¡Bienvenido a {{company_name}}!</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Nos complace darte la bienvenida a nuestra plataforma. Estamos aquí para ayudarte a gestionar tus servicios de manera eficiente.</p>
        <p>Puedes acceder a tu cuenta en: <a href="{{login_url}}">{{login_url}}</a></p>
        <p>Si tienes alguna pregunta, no dudes en contactarnos en: {{support_email}}</p>
        <p>¡Gracias por confiar en nosotros!</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getWelcomeAdminBody(): string
    {
        return '<h2>Nuevo Cliente Registrado</h2>
        <p>Se ha registrado un nuevo cliente en el sistema:</p>
        <ul>
            <li><strong>Nombre:</strong> {{client_name}}</li>
            <li><strong>Email:</strong> {{client_email}}</li>
            <li><strong>Fecha de Registro:</strong> {{registration_date}}</li>
        </ul>
        <p>Por favor, revisa la información del cliente y verifica que todo esté correcto.</p>';
    }

    private function getPasswordResetUserBody(): string
    {
        return '<h2>Crea tu Contraseña</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Has solicitado crear/restablecer tu contraseña en {{company_name}}.</p>
        <p>Haz clic en el siguiente enlace para crear tu nueva contraseña:</p>
        <p><a href="{{reset_link}}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Crear Contraseña</a></p>
        <p>Este enlace expirará en {{expires_in}}.</p>
        <p>Si no solicitaste este cambio, ignora este mensaje.</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getServiceExpiringUserBody(): string
    {
        return '<h2>Recordatorio de Vencimiento</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Te recordamos que tu servicio <strong>{{service_name}}</strong> vence el <strong>{{due_date}}</strong>.</p>
        <p><strong>Monto a pagar:</strong> {{amount}}</p>
        <p>Para evitar interrupciones en tu servicio, te invitamos a realizar el pago:</p>
        <p><a href="{{payment_link}}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Realizar Pago</a></p>
        <p>Si ya realizaste el pago, puedes ignorar este mensaje.</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getServiceExpiringAdminBody(): string
    {
        return '<h2>Alerta: Servicio Próximo a Vencer</h2>
        <p>El servicio <strong>{{service_name}}</strong> del cliente <strong>{{client_name}}</strong> vence el <strong>{{due_date}}</strong>.</p>
        <p><strong>Monto:</strong> {{amount}}</p>
        <p>Por favor, contacta al cliente para recordarle el pago.</p>';
    }

    private function getProductPurchasedUserBody(): string
    {
        return '<h2>Confirmación de Compra</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Hemos recibido tu compra del producto <strong>{{service_name}}</strong>.</p>
        <p><strong>Monto pagado:</strong> {{amount}}</p>
        <p><strong>Número de factura:</strong> {{invoice_number}}</p>
        <p>Puedes descargar tu factura aquí: <a href="{{invoice_link}}">Ver Factura</a></p>
        <p>¡Gracias por tu compra!</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getProductPurchasedAdminBody(): string
    {
        return '<h2>Nueva Compra de Producto Único</h2>
        <p>El cliente <strong>{{client_name}}</strong> ha adquirido el producto <strong>{{service_name}}</strong>.</p>
        <p><strong>Monto:</strong> {{amount}}</p>
        <p><strong>Número de factura:</strong> {{invoice_number}}</p>
        <p>Por favor, verifica el pago y activa el servicio si corresponde.</p>';
    }

    private function getServiceExpiredGraceUserBody(): string
    {
        return '<h2>Periodo de Gracia</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Tu servicio <strong>{{service_name}}</strong> venció el <strong>{{expired_date}}</strong>, pero actualmente está en un periodo de gracia de <strong>{{grace_period_days}} días</strong>.</p>
        <p><strong>Monto a pagar:</strong> {{amount}}</p>
        <p>Para evitar la suspensión del servicio, realiza el pago antes de que finalice el periodo de gracia:</p>
        <p><a href="{{payment_link}}" style="background-color: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Realizar Pago</a></p>
        <p>Si tienes alguna duda, contáctanos en: {{support_email}}</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getServiceExpiredGraceAdminBody(): string
    {
        return '<h2>Alerta: Servicio en Periodo de Gracia</h2>
        <p>El servicio <strong>{{service_name}}</strong> del cliente <strong>{{client_name}}</strong> venció el <strong>{{expired_date}}</strong> y está en periodo de gracia.</p>
        <p><strong>Monto pendiente:</strong> {{amount}}</p>
        <p>Por favor, contacta al cliente para recordarle el pago antes de que finalice el periodo de gracia.</p>';
    }

    private function getServiceExpiredSuspendedUserBody(): string
    {
        return '<h2>Servicio Suspendido</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Lamentamos informarte que tu servicio <strong>{{service_name}}</strong> ha sido suspendido debido a falta de pago.</p>
        <p>El servicio venció el <strong>{{expired_date}}</strong>.</p>
        <p><strong>Monto pendiente:</strong> {{amount}}</p>
        <p>Para reactivar tu servicio, realiza el pago pendiente:</p>
        <p><a href="{{payment_link}}" style="background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Realizar Pago</a></p>
        <p>Si tienes alguna duda o necesitas asistencia, contáctanos en: {{support_email}}</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getServiceExpiredSuspendedAdminBody(): string
    {
        return '<h2>Alerta Crítica: Servicio Suspendido</h2>
        <p>El servicio <strong>{{service_name}}</strong> del cliente <strong>{{client_name}}</strong> ha sido suspendido.</p>
        <p><strong>Fecha de vencimiento:</strong> {{expired_date}}</p>
        <p><strong>Monto pendiente:</strong> {{amount}}</p>
        <p>El servicio ha sido suspendido automáticamente. Contacta al cliente para resolver la situación.</p>';
    }

    private function getServiceCancelledUserBody(): string
    {
        return '<h2>Confirmación de Cancelación</h2>
        <p>Estimado/a <strong>{{client_name}}</strong>,</p>
        <p>Confirmamos la cancelación de tu servicio <strong>{{service_name}}</strong>.</p>
        <p><strong>Fecha de cancelación:</strong> {{cancellation_date}}</p>
        <p><strong>Motivo:</strong> {{reason}}</p>
        <p>Si tienes alguna pregunta o necesitas reactivar el servicio, contáctanos.</p>
        <p>El equipo de {{company_name}}</p>';
    }

    private function getServiceCancelledAdminBody(): string
    {
        return '<h2>Servicio Cancelado</h2>
        <p>El servicio <strong>{{service_name}}</strong> del cliente <strong>{{client_name}}</strong> ha sido cancelado.</p>
        <p><strong>Fecha de cancelación:</strong> {{cancellation_date}}</p>
        <p><strong>Motivo:</strong> {{reason}}</p>
        <p>Por favor, verifica que todos los procesos relacionados se hayan completado correctamente.</p>';
    }
}
