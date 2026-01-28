<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BoldPaymentService;
use Illuminate\Support\Facades\Log;

class BoldWebhookController extends Controller
{
    public function __construct(
        protected BoldPaymentService $boldService
    ) {}

    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            $signature = $request->header('X-Bold-Signature');

            // Validar firma
            if (!$this->boldService->validateWebhookSignature($data, $signature)) {
                Log::warning('Bold webhook: Firma inválida', $data);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Procesar webhook
            $this->boldService->processWebhook($data);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Bold webhook error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
