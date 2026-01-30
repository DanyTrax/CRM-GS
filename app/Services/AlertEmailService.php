<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\EmailConfiguration;
use App\Models\EmailTemplate;
use App\Models\NotificationSetting;
use App\Models\MessageHistory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class AlertEmailService
{
    /**
     * Enviar alerta por email
     */
    public function sendAlert(Alert $alert): bool
    {
        try {
            // Verificar si la notificación está habilitada
            $module = $this->getModuleFromAlertType($alert->type);
            $eventType = $this->getEventTypeFromAlertType($alert->type);
            
            // Determinar tipo de destinatario (user o admin)
            $recipientType = $this->getRecipientTypeFromAlert($alert);
            
            // Verificar si está habilitada la notificación por email
            if (!NotificationSetting::isEnabled($module, $eventType, $recipientType, 'email')) {
                Log::info("Notificación deshabilitada para: {$module}.{$eventType}.{$recipientType}");
                return false;
            }

            // Obtener plantilla de email
            $template = NotificationSetting::getTemplate($module, $eventType, $recipientType);
            if (!$template) {
                Log::warning("No se encontró plantilla para: {$module}.{$eventType}.{$recipientType}");
                return false;
            }

            // Obtener configuración de email por defecto
            $emailConfig = EmailConfiguration::getDefault();
            if (!$emailConfig || !$emailConfig->is_active) {
                Log::error("No hay configuración de email activa o por defecto");
                return false;
            }

            // Aplicar configuración de email
            $emailConfig->applyToMailConfig();

            // Obtener destinatario
            $recipientEmail = $this->getRecipientEmail($alert, $recipientType);
            if (!$recipientEmail) {
                Log::warning("No se pudo obtener email del destinatario para la alerta {$alert->id}");
                return false;
            }

            // Procesar plantilla con variables
            $subject = $this->processTemplate($template->subject ?? $alert->name, $alert);
            $body = $this->processTemplate($template->body ?? $alert->message, $alert);

            // Enviar email
            Mail::send([], [], function ($message) use ($recipientEmail, $subject, $body, $emailConfig) {
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->html($body);

                if ($emailConfig->from_email) {
                    $message->from($emailConfig->from_email, $emailConfig->from_name);
                }

                if ($emailConfig->reply_to_email) {
                    $message->replyTo($emailConfig->reply_to_email, $emailConfig->reply_to_name);
                }
            });

            // Obtener información del destinatario
            $recipientId = null;
            if ($entity instanceof \App\Models\Service) {
                $recipientId = $entity->client_id;
            } elseif ($entity instanceof \App\Models\Invoice) {
                $recipientId = $entity->client_id;
            } elseif ($entity instanceof \App\Models\Payment) {
                $recipientId = $entity->invoice->client_id ?? null;
            }

            // Registrar en historial de mensajes
            MessageHistory::create([
                'message_type' => 'email',
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body' => $body,
                'template_type' => 'email',
                'template_id' => $template->id,
                'status' => 'sent',
                'provider' => $emailConfig->provider ?? 'smtp',
                'sent_at' => now(),
            ]);

            // Marcar alerta como enviada
            $alert->markAsSent();

            Log::info("Alerta {$alert->id} enviada exitosamente a {$recipientEmail}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error enviando alerta {$alert->id}: " . $e->getMessage(), [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Registrar fallo en historial
            $entity = $alert->entity;
            $recipientId = null;
            if ($entity instanceof \App\Models\Service) {
                $recipientId = $entity->client_id;
            } elseif ($entity instanceof \App\Models\Invoice) {
                $recipientId = $entity->client_id;
            } elseif ($entity instanceof \App\Models\Payment) {
                $recipientId = $entity->invoice->client_id ?? null;
            }

            MessageHistory::create([
                'message_type' => 'email',
                'recipient_type' => $this->getRecipientTypeFromAlert($alert),
                'recipient_id' => $recipientId,
                'recipient_email' => $this->getRecipientEmail($alert, $this->getRecipientTypeFromAlert($alert)),
                'subject' => $alert->name,
                'body' => $alert->message,
                'status' => 'failed',
                'provider' => 'smtp',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Obtener módulo desde el tipo de alerta
     */
    protected function getModuleFromAlertType(string $type): string
    {
        return match($type) {
            'service_expiring', 'service_expired' => 'services',
            'invoice_overdue' => 'invoices',
            'payment_pending' => 'payments',
            default => 'general',
        };
    }

    /**
     * Obtener tipo de evento desde el tipo de alerta
     */
    protected function getEventTypeFromAlertType(string $type): string
    {
        return match($type) {
            'service_expiring' => 'expiring',
            'service_expired' => 'expired',
            'invoice_overdue' => 'overdue',
            'payment_pending' => 'pending_approval',
            default => 'alert',
        };
    }

    /**
     * Obtener tipo de destinatario desde la alerta
     */
    protected function getRecipientTypeFromAlert(Alert $alert): string
    {
        // Por defecto, las alertas se envían a usuarios (clientes)
        // Pero algunas pueden ser para admins
        if (in_array($alert->type, ['payment_pending'])) {
            return 'admin';
        }
        
        return 'user';
    }

    /**
     * Obtener email del destinatario
     */
    protected function getRecipientEmail(Alert $alert, string $recipientType): ?string
    {
        if ($recipientType === 'admin') {
            // Obtener email de administrador (puedes ajustar esto según tu lógica)
            $admin = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'super-admin');
            })->first();
            
            return $admin?->email;
        }

        // Obtener email del cliente desde la entidad relacionada
        $entity = $alert->entity;
        if (!$entity) {
            return null;
        }

        if ($entity instanceof \App\Models\Service) {
            return $entity->client->email_billing ?? $entity->client->email_login ?? null;
        }

        if ($entity instanceof \App\Models\Invoice) {
            return $entity->client->email_billing ?? $entity->client->email_login ?? null;
        }

        if ($entity instanceof \App\Models\Payment) {
            return $entity->invoice->client->email_billing ?? $entity->invoice->client->email_login ?? null;
        }

        return null;
    }

    /**
     * Procesar plantilla con variables
     */
    protected function processTemplate(string $template, Alert $alert): string
    {
        $entity = $alert->entity;
        $variables = [
            'alert_name' => $alert->name,
            'alert_message' => $alert->message,
            'alert_priority' => $alert->priority,
            'alert_type' => $alert->type,
            'trigger_date' => $alert->trigger_date->format('d/m/Y'),
        ];

        // Agregar variables según el tipo de entidad
        if ($entity instanceof \App\Models\Service) {
            $variables = array_merge($variables, [
                'service_name' => $entity->name,
                'service_price' => number_format($entity->price, 2),
                'service_currency' => $entity->currency,
                'service_due_date' => $entity->next_due_date->format('d/m/Y'),
                'client_name' => $entity->client->company_name ?? '',
                'client_email' => $entity->client->email_billing ?? $entity->client->email_login ?? '',
            ]);
        }

        if ($entity instanceof \App\Models\Invoice) {
            $variables = array_merge($variables, [
                'invoice_number' => $entity->invoice_number,
                'invoice_amount' => number_format($entity->total_amount, 2),
                'invoice_due_date' => $entity->due_date->format('d/m/Y'),
                'client_name' => $entity->client->company_name ?? '',
                'client_email' => $entity->client->email_billing ?? $entity->client->email_login ?? '',
            ]);
        }

        // Reemplazar variables en la plantilla
        foreach ($variables as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
            $template = str_replace("{{ $key }}", $value, $template);
        }

        return $template;
    }

    /**
     * Enviar alertas pendientes que están listas para enviar
     */
    public function sendPendingAlerts(): int
    {
        $alerts = Alert::where('status', 'pending')
            ->where('trigger_date', '<=', now())
            ->get();

        $sentCount = 0;
        foreach ($alerts as $alert) {
            if ($this->sendAlert($alert)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }
}
