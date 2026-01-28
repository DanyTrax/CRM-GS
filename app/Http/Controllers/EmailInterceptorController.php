<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailInterceptorController extends Controller
{
    /**
     * Mostrar modal con el contenido del correo para edición
     * 
     * GET /admin/emails/preview/{emailLog}
     */
    public function preview(EmailLog $emailLog)
    {
        return view('admin.emails.preview', compact('emailLog'));
    }

    /**
     * Interceptar correo antes de enviarlo
     * Crea un log y retorna el HTML para edición
     * 
     * POST /admin/emails/intercept
     */
    public function intercept(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
        ]);

        // Crear log de correo pendiente
        $emailLog = EmailLog::create([
            'to' => $request->to,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'subject' => $request->subject,
            'body' => $request->body,
            'original_body' => $request->body,
            'status' => 'pending',
            'was_edited' => false,
            'sent_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'email_log_id' => $emailLog->id,
            'email' => [
                'to' => $emailLog->to,
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
            ],
        ]);
    }

    /**
     * Actualizar el contenido del correo después de edición
     * 
     * PUT /admin/emails/{emailLog}/update-content
     */
    public function updateContent(Request $request, EmailLog $emailLog)
    {
        $request->validate([
            'body' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        $wasEdited = $emailLog->body !== $request->body;

        $emailLog->update([
            'body' => $request->body,
            'subject' => $request->subject ?? $emailLog->subject,
            'was_edited' => $wasEdited,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contenido actualizado',
            'was_edited' => $wasEdited,
        ]);
    }

    /**
     * Enviar el correo después de la edición
     * 
     * POST /admin/emails/{emailLog}/send
     */
    public function send(EmailLog $emailLog)
    {
        try {
            // Despachar el correo a la cola
            dispatch(new \App\Jobs\SendInterceptedEmail($emailLog));

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado a la cola de envío',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando correo interceptado', [
                'email_log_id' => $emailLog->id,
                'error' => $e->getMessage(),
            ]);

            $emailLog->markAsFailed($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
