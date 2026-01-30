<?php

namespace App\Http\Controllers;

use App\Models\EmailConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZohoOAuthController extends Controller
{
    /**
     * Iniciar autorización con Zoho
     */
    public function startAuthorization(Request $request)
    {
        $request->validate([
            'config_id' => 'required|exists:email_configurations,id',
        ]);

        $config = EmailConfiguration::findOrFail($request->config_id);

        if (!$config->zoho_client_id || !$config->zoho_client_secret) {
            return redirect()->back()->with('error', 'Debe configurar Client ID y Client Secret primero.');
        }

        // Generar state para seguridad
        $state = Str::random(40);
        session(['zoho_oauth_state' => $state]);
        session(['zoho_config_id' => $config->id]);

        // Construir URL de autorización
        $redirectUri = route('zoho.oauth.callback');
        $authUrl = 'https://accounts.zoho.com/oauth/v2/auth?' . http_build_query([
            'scope' => 'ZohoMail.messages.CREATE,ZohoMail.accounts.READ',
            'client_id' => $config->zoho_client_id,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect($authUrl);
    }

    /**
     * Callback de OAuth de Zoho
     */
    public function callback(Request $request)
    {
        // Verificar state
        $sessionState = session('zoho_oauth_state');
        if (!$sessionState || $sessionState !== $request->state) {
            return redirect()->route('filament.admin.resources.email-configurations.index')
                ->with('error', 'Error de seguridad: state no coincide.');
        }

        // Verificar si hay error
        if ($request->has('error')) {
            $error = $request->error;
            $errorDescription = $request->error_description ?? 'Error desconocido';
            
            Log::error('Error en callback de Zoho OAuth', [
                'error' => $error,
                'error_description' => $errorDescription,
            ]);

            return redirect()->route('filament.admin.resources.email-configurations.edit', [
                'record' => session('zoho_config_id')
            ])->with('error', "Error de autorización: {$errorDescription}");
        }

        // Obtener código de autorización
        $code = $request->code;
        if (!$code) {
            return redirect()->route('filament.admin.resources.email-configurations.edit', [
                'record' => session('zoho_config_id')
            ])->with('error', 'No se recibió código de autorización.');
        }

        // Obtener configuración
        $configId = session('zoho_config_id');
        $config = EmailConfiguration::findOrFail($configId);

        // Intercambiar código por tokens
        try {
            $redirectUri = route('zoho.oauth.callback');
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $config->zoho_client_id,
                'client_secret' => $config->zoho_client_secret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('Error obteniendo tokens de Zoho', [
                    'response' => $error,
                ]);

                $errorMessage = $error['error'] ?? 'Error desconocido';
                if (isset($error['error_description'])) {
                    $errorMessage .= ': ' . $error['error_description'];
                }

                return redirect()->route('filament.admin.resources.email-configurations.edit', [
                    'record' => $configId
                ])->with('error', $errorMessage);
            }

            $tokens = $response->json();

            // Guardar tokens
            $config->update([
                'zoho_refresh_token' => $tokens['refresh_token'] ?? null,
                'zoho_access_token' => $tokens['access_token'] ?? null,
                'zoho_token_expires_at' => isset($tokens['expires_in']) 
                    ? now()->addSeconds($tokens['expires_in']) 
                    : null,
            ]);

            // Limpiar sesión
            session()->forget(['zoho_oauth_state', 'zoho_config_id']);

            return redirect()->route('filament.admin.resources.email-configurations.edit', [
                'record' => $configId
            ])->with('success', 'Refresh Token generado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Excepción obteniendo tokens de Zoho', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('filament.admin.resources.email-configurations.edit', [
                'record' => $configId
            ])->with('error', 'Error al obtener tokens: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar Refresh Token
     */
    public function clearToken(Request $request)
    {
        $request->validate([
            'config_id' => 'required|exists:email_configurations,id',
        ]);

        $config = EmailConfiguration::findOrFail($request->config_id);
        $config->update([
            'zoho_refresh_token' => null,
            'zoho_access_token' => null,
            'zoho_token_expires_at' => null,
        ]);

        return redirect()->back()->with('success', 'Refresh Token limpiado exitosamente.');
    }
}
