<?php

namespace App\Http\Controllers;

use App\Services\BoldPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoldWebhookController extends Controller
{
    protected $boldService;

    public function __construct(BoldPaymentService $boldService)
    {
        $this->boldService = $boldService;
    }

    /**
     * Endpoint para recibir webhooks de Bold
     * 
     * POST /api/bold/webhook
     */
    public function handle(Request $request)
    {
        try {
            $signature = $request->header('X-Bold-Signature') ?? $request->header('Signature');
            $webhookData = $request->all();

            Log::info('Bold Webhook recibido', [
                'signature' => $signature ? 'present' : 'missing',
                'status' => $webhookData['status'] ?? 'unknown',
            ]);

            $result = $this->boldService->processWebhook($webhookData, $signature ?? '');

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        } catch (\Exception $e) {
            Log::error('Bold Webhook: Excepción no manejada', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error procesando webhook',
            ], 500);
        }
    }
}
