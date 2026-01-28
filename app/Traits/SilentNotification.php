<?php

namespace App\Traits;

trait SilentNotification
{
    /**
     * Verificar si las notificaciones deben ser suprimidas
     * (Modo migración silenciosa)
     * 
     * @param \Illuminate\Database\Eloquent\Model|null $relatedModel Modelo relacionado (Client, Service, etc.)
     * @return bool
     */
    protected function shouldSuppressNotifications($relatedModel = null): bool
    {
        // Si hay un modelo relacionado con modo migración
        if ($relatedModel && method_exists($relatedModel, 'isInMigrationMode')) {
            return $relatedModel->isInMigrationMode();
        }

        // Verificar flag global en request (si existe)
        if (request()->has('silent_mode') && request()->boolean('silent_mode')) {
            return true;
        }

        // Verificar si el cliente asociado tiene modo migración activo
        if (isset($this->client) && $this->client instanceof \App\Models\Client) {
            return $this->client->isInMigrationMode();
        }

        return false;
    }

    /**
     * Enviar notificación solo si no está en modo silencioso
     * 
     * @param \Illuminate\Notifications\Notification $notification
     * @param \Illuminate\Database\Eloquent\Model|null $relatedModel
     * @return void
     */
    protected function sendNotificationIfNotSilent($notification, $relatedModel = null): void
    {
        if ($this->shouldSuppressNotifications($relatedModel)) {
            // Log en email_logs marcado como suprimido
            \App\Models\EmailLog::create([
                'to' => $notification->toMail($notification)->to[0] ?? 'unknown',
                'subject' => $notification->toMail($notification)->subject ?? 'N/A',
                'body' => '',
                'status' => 'pending',
                'suppressed_by_migration_mode' => true,
            ]);
            
            return;
        }

        // Enviar notificación normal
        if (method_exists($this, 'notify')) {
            $this->notify($notification);
        }
    }
}
