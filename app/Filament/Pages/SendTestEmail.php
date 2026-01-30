<?php

namespace App\Filament\Pages;

use App\Models\EmailConfiguration;
use App\Models\MessageHistory;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTestEmail extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.send-test-email';

    protected static ?string $title = 'Enviar Email de Prueba';

    protected static ?string $navigationLabel = 'Enviar Email de Prueba';
    
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    
    protected static ?string $navigationGroup = 'Mensajería';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $slug = 'send-test-email';
    
    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return true;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'email_configuration_id' => EmailConfiguration::getDefault()?->id,
            'to' => '',
            'subject' => 'Email de Prueba - ' . config('app.name'),
            'body' => 'Este es un email de prueba enviado desde el sistema de gestión de servicios.',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Email')
                    ->schema([
                        Forms\Components\Select::make('email_configuration_id')
                            ->label('Configuración de Email')
                            ->options(EmailConfiguration::where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($config) => [
                                    $config->id => $config->name . ' (' . strtoupper($config->provider) . ')'
                                ]))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Selecciona la configuración de email a usar para el envío'),
                    ]),

                Forms\Components\Section::make('Destinatario')
                    ->schema([
                        Forms\Components\TextInput::make('to')
                            ->label('Email Destinatario')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ejemplo@correo.com')
                            ->helperText('Email al que se enviará la prueba'),
                    ]),

                Forms\Components\Section::make('Contenido del Email')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(255)
                            ->default('Email de Prueba - ' . config('app.name')),

                        Forms\Components\Textarea::make('body')
                            ->label('Mensaje')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->default('Este es un email de prueba enviado desde el sistema de gestión de servicios.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        
        try {
            $config = EmailConfiguration::findOrFail($data['email_configuration_id']);

            if (!$config->is_active) {
                throw new \Exception('La configuración de email seleccionada no está activa.');
            }

            // Enviar según el proveedor
            $messageHistory = null;
            if ($config->provider === 'smtp') {
                $messageHistory = $this->sendViaSMTP($config, $data);
            } elseif ($config->provider === 'zoho') {
                $messageHistory = $this->sendViaZoho($config, $data);
            } else {
                throw new \Exception('Proveedor no soportado para envío de prueba.');
            }

            Notification::make()
                ->title('Email de prueba enviado exitosamente')
                ->body('El email se envió correctamente a ' . $data['to'])
                ->success()
                ->send();

            // Limpiar formulario
            $this->form->fill([
                'email_configuration_id' => $config->id,
                'to' => '',
                'subject' => 'Email de Prueba - ' . config('app.name'),
                'body' => 'Este es un email de prueba enviado desde el sistema de gestión de servicios.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando email de prueba', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Error al enviar email de prueba')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function sendViaSMTP(EmailConfiguration $config, array $data): MessageHistory
    {
        // Validar configuración SMTP
        if (!$config->smtp_host || !$config->smtp_port) {
            throw new \Exception('La configuración SMTP está incompleta. Verifica Host y Puerto.');
        }

        // Aplicar configuración
        $config->applyToMailConfig();

        // Crear registro en historial antes de enviar
        $messageHistory = MessageHistory::create([
            'message_type' => 'email',
            'recipient_type' => 'user',
            'recipient_email' => $data['to'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'template_type' => 'test',
            'status' => 'pending',
            'provider' => 'smtp',
            'sent_by' => auth()->id(),
        ]);

        try {
            // Enviar email
            Mail::raw($data['body'], function ($message) use ($config, $data) {
                $message->to($data['to'])
                    ->subject($data['subject']);

                if ($config->from_email) {
                    $message->from($config->from_email, $config->from_name);
                }

                if ($config->reply_to_email) {
                    $message->replyTo($config->reply_to_email, $config->reply_to_name);
                }
            });

            // Marcar como enviado
            $messageHistory->markAsSent();

        } catch (\Exception $e) {
            // Marcar como fallido
            $messageHistory->markAsFailed($e->getMessage());
            throw $e;
        }

        return $messageHistory;
    }

    protected function sendViaZoho(EmailConfiguration $config, array $data): MessageHistory
    {
        // Validar configuración Zoho
        if (!$config->zoho_client_id || !$config->zoho_client_secret) {
            throw new \Exception('La configuración Zoho está incompleta. Verifica Client ID y Client Secret.');
        }

        if (!$config->zoho_refresh_token) {
            throw new \Exception('Refresh Token no configurado. Debes autorizar con Zoho primero.');
        }

        // Crear registro en historial antes de enviar
        $messageHistory = MessageHistory::create([
            'message_type' => 'email',
            'recipient_type' => 'user',
            'recipient_email' => $data['to'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'template_type' => 'test',
            'status' => 'pending',
            'provider' => 'zoho',
            'sent_by' => auth()->id(),
        ]);

        try {
            // Obtener Access Token
            $accessToken = $this->getZohoAccessToken($config);

            // Obtener Account ID
            $accountId = $this->getZohoAccountId($config, $accessToken);

            // Enviar email usando Zoho Mail API
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://mail.zoho.com/api/accounts/{$accountId}/messages", [
                'fromAddress' => $config->from_email,
                'toAddress' => $data['to'],
                'subject' => $data['subject'],
                'content' => $data['body'],
                'mailFormat' => 'html',
            ]);

            if (!$response->successful()) {
                $error = $response->json();
                throw new \Exception('Error al enviar email vía Zoho: ' . ($error['message'] ?? 'Error desconocido'));
            }

            // Obtener external_id de la respuesta si está disponible
            $responseData = $response->json();
            $externalId = $responseData['data']['messageId'] ?? $responseData['messageId'] ?? null;

            // Marcar como enviado
            $messageHistory->markAsSent($externalId);

        } catch (\Exception $e) {
            // Marcar como fallido
            $messageHistory->markAsFailed($e->getMessage());
            throw $e;
        }

        return $messageHistory;
    }

    protected function getZohoAccessToken(EmailConfiguration $config): string
    {
        // Si hay access token válido, usarlo
        if ($config->zoho_access_token && $config->zoho_token_expires_at && $config->zoho_token_expires_at->isFuture()) {
            return $config->zoho_access_token;
        }

        // Renovar access token usando refresh token
        $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $config->zoho_client_id,
            'client_secret' => $config->zoho_client_secret,
            'refresh_token' => $config->zoho_refresh_token,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al renovar Access Token de Zoho. Verifica tu Refresh Token.');
        }

        $tokens = $response->json();

        // Guardar nuevo access token
        $config->update([
            'zoho_access_token' => $tokens['access_token'] ?? null,
            'zoho_token_expires_at' => isset($tokens['expires_in']) 
                ? now()->addSeconds($tokens['expires_in']) 
                : null,
        ]);

        return $tokens['access_token'];
    }

    protected function getZohoAccountId(EmailConfiguration $config, string $accessToken): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        ])->get('https://mail.zoho.com/api/accounts');

        if (!$response->successful()) {
            throw new \Exception('Error al obtener Account ID de Zoho.');
        }

        $accounts = $response->json();
        
        if (empty($accounts['data'])) {
            throw new \Exception('No se encontraron cuentas de Zoho Mail.');
        }

        // Buscar cuenta que coincida con el email remitente
        foreach ($accounts['data'] as $account) {
            if (isset($account['accountName']) && $account['accountName'] === $config->from_email) {
                return $account['accountId'];
            }
        }

        // Si no se encuentra, usar la primera cuenta
        return $accounts['data'][0]['accountId'];
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('send')
                ->label('Enviar Email de Prueba')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action('send'),
        ];
    }
}
