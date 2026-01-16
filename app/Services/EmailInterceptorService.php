<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class EmailInterceptorService
{
    /**
     * Intercepta el envío de correo y muestra modal para edición
     * Este método se llama desde el controlador antes de enviar
     */
    public function prepareEmailForSending(array $data): array
    {
        $template = EmailTemplate::find($data['template_id'] ?? null);
        
        $subject = $data['subject'] ?? $template?->subject ?? '';
        $body = $data['body'] ?? $template?->body ?? '';

        // Reemplazar variables
        $subject = $this->replaceVariables($subject, $data['variables'] ?? []);
        $body = $this->replaceVariables($body, $data['variables'] ?? []);

        return [
            'to_email' => $data['to_email'],
            'subject' => $subject,
            'body' => $body,
            'template_name' => $template?->name,
        ];
    }

    /**
     * Envía el correo a través de la cola
     */
    public function sendEmail(array $emailData, Client $client = null, bool $silentMode = false): EmailLog
    {
        if ($silentMode) {
            // Modo silencioso: solo registrar, no enviar
            return EmailLog::create([
                'client_id' => $client?->id,
                'to_email' => $emailData['to_email'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'status' => 'queued',
                'template_name' => $emailData['template_name'] ?? null,
            ]);
        }

        // Registrar en log
        $emailLog = EmailLog::create([
            'client_id' => $client?->id,
            'to_email' => $emailData['to_email'],
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'status' => 'queued',
            'template_name' => $emailData['template_name'] ?? null,
        ]);

        // Encolar correo
        Queue::push(function ($job) use ($emailData, $emailLog) {
            try {
                Mail::raw($emailData['body'], function ($message) use ($emailData) {
                    $message->to($emailData['to_email'])
                        ->subject($emailData['subject']);
                });

                $emailLog->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                $emailLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
            
            $job->delete();
        });

        return $emailLog;
    }

    /**
     * Reemplaza variables en el template
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}
