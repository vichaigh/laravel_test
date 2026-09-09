<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentRequestData;
use App\Exceptions\UnsupportedProviderException;
use App\Factories\PaymentProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use UnexpectedValueException;

class PaymentController extends Controller
{
    /**
     * Handle a dynamic checkout call for any registered provider.
     *
     * Route pattern: POST /api/{provider}/checkout
     */
    public function checkout(string $provider, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|string|size:3',
            'reference_id' => 'required|string',
            'return_url' => 'required|url',
            'cancel_url' => 'required|url',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $driver = PaymentProviderFactory::make($provider);
            $result = $driver->charge(PaymentRequestData::fromArray($validated));

            return response()->json($result->toArray(), $result->success ? 200 : 400);
        } catch (UnsupportedProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Checkout failed.'], 500);
        }
    }

    /**
     * Handle a dynamic webhook for any registered provider.
     *
     * Route pattern: POST /api/{provider}/webhook
     */
    public function webhook(string $provider, Request $request): JsonResponse
    {
        $webhookSecret = (string) config("services.{$provider}.webhook_secret");
        $providedToken = (string) $request->bearerToken();

        if ($webhookSecret === '' || ! hash_equals($webhookSecret, $providedToken)) {
            abort(401);
        }

        try {
            $driver = PaymentProviderFactory::make($provider);
            $result = $driver->handleWebhook($request);

            return response()->json(['status' => 'acknowledged', 'data' => $result], 200);
        } catch (UnsupportedProviderException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (UnexpectedValueException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Webhook processing failed.'], 500);
        }
    }

    /**
     * Get transaction status from a payment provider.
     *
     * Route pattern: GET /api/{provider}/status/{transactionId}
     */
    public function status(string $provider, string $transactionId): JsonResponse
    {
        try {
            $driver = PaymentProviderFactory::make($provider);

            return response()->json($driver->getStatus($transactionId)->toArray());
        } catch (UnsupportedProviderException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Unable to fetch status.'], 500);
        }
    }

    /**
     * Trigger a refund request.
     *
     * Route pattern: POST /api/{provider}/refund/{transactionId}
     */
    public function refund(string $provider, string $transactionId, Request $request): JsonResponse
    {
        $validated = $request->validate(['amount' => 'required|numeric|gt:0']);

        try {
            $driver = PaymentProviderFactory::make($provider);
            $result = $driver->refund($transactionId, (float) $validated['amount']);

            return response()->json($result->toArray(), $result->success ? 200 : 400);
        } catch (UnsupportedProviderException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Refund failed.'], 500);
        }
    }
}
