<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Finance\ProcessEpaycoWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpaycoWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessEpaycoWebhookAction $action): JsonResponse
    {
        try {
            $payload = $request->all();

            // ePayco webhook logs for tracing
            // Often highly sensitive but required for forensic in Sandbox or MVP if there are issues
            Log::info('ePayco Webhook received', ['reference' => $payload['x_ref_payco'] ?? null]);

            if (empty($payload)) {
                return response()->json(['message' => 'Empty payload'], 400);
            }

            $action->execute($payload);

            return response()->json(['message' => 'Processed'], 200);
        } catch (\InvalidArgumentException $e) {
            Log::warning('ePayco webhook validation error: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('ePayco webhook exception: '.$e->getMessage());

            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
