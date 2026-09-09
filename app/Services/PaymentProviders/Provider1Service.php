<?php

namespace App\Services\PaymentProviders;

use App\DTOs\PaymentRequestData;
use App\DTOs\PaymentResponseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class Provider1Service extends BasePaymentProvider
{
    public function __construct()
    {
        parent::__construct('provider1');
    }

    public function charge(PaymentRequestData $data): PaymentResponseData
    {
        $payload = [
            'merchant_key' => $this->apiKey,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'order_ref' => $data->referenceId,
            'callback_url' => $data->returnUrl,
            'cancel_url' => $data->cancelUrl,
            'email' => $data->customerEmail,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secret,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/charges", $payload);

        if ($response->successful()) {
            $resData = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $resData['id'],
                status: $resData['status'],
                redirectUrl: $resData['payment_url'] ?? null,
                rawResponse: $resData,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: '',
            status: 'failed',
            errorMessage: $response->json('message') ?? 'Provider1 transaction failed',
            rawResponse: $response->json() ?? [],
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Provider1-Signature');

        if (! $signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return hash_equals($computedSignature, $signature);
    }

    public function handleWebhook(Request $request): array
    {
        $this->logEvent('Webhook Received', $request->all());

        if (! $this->verifyWebhookSignature($request)) {
            throw new UnexpectedValueException('Invalid Provider1 webhook signature');
        }

        $payload = $request->all();

        return [
            'event' => $payload['event_type'] ?? 'unknown',
            'transaction_id' => $payload['data']['id'] ?? null,
            'status' => $payload['data']['status'] ?? null,
            'processed' => true,
        ];
    }

    public function getStatus(string $transactionId): PaymentResponseData
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secret,
        ])->get("{$this->baseUrl}/charges/{$transactionId}");

        if ($response->successful()) {
            $data = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $data['id'],
                status: $data['status'],
                rawResponse: $data,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: $transactionId,
            status: 'unknown',
            errorMessage: 'Unable to fetch Provider1 status',
        );
    }

    public function refund(string $transactionId, float $amount): PaymentResponseData
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secret,
        ])->post("{$this->baseUrl}/refunds", [
            'charge_id' => $transactionId,
            'amount' => $amount,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $data['refund_id'],
                status: 'refunded',
                rawResponse: $data,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: $transactionId,
            status: 'refund_failed',
            errorMessage: $response->json('message') ?? 'Refund failed',
        );
    }
}
