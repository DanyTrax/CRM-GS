<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLog;
use Symfony\Component\HttpFoundation\Response;

class EmailInterceptor
{
    /**
     * Intercepta correos antes de enviarlos
     * Crea un log y permite edición manual antes del envío
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Interceptar el Mail facade
        Mail::macro('interceptedSend', function ($view, $data = [], $callback = null) {
            // Extraer información del correo
            $to = null;
            $subject = '';
            $body = '';

            // Si es un Mailable, extraer datos
            if (is_object($view) && method_exists($view, 'build')) {
                $mailable = $view;
                $message = new \Illuminate\Mail\Message(new \Swift_Message());
                $mailable->build();
                
                // Intentar obtener destinatario y asunto
                $to = $mailable->to[0]['address'] ?? null;
                $subject = $mailable->subject ?? 'Sin asunto';
                $body = view($mailable->view, $mailable->viewData)->render();
            }

            // Crear log de correo
            $emailLog = EmailLog::create([
                'to' => $to ?? 'unknown@example.com',
                'subject' => $subject,
                'body' => $body,
                'original_body' => $body,
                'status' => 'pending',
                'was_edited' => false,
                'sent_by' => auth()->id(),
            ]);

            // Retornar para que el usuario pueda editar
            return $emailLog;
        });

        return $next($request);
    }
}
