<?php

namespace App\Services\PaymentProviders;

use App\DTOs\PaymentRequestData;
use App\DTOs\PaymentResponseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class Provider2Service extends BasePaymentProvider
{
    public function __construct()
    {
        parent::__construct('provider2');
    }

    public function charge(PaymentRequestData $data): PaymentResponseData
    {
        $payload = [
            'app_id' => $this->apiKey,
            'txn_amount' => (int) round($data->amount * 100), // convert to cents
            'currency_code' => $data->currency,
            'merchant_ref' => $data->referenceId,
            'success_url' => $data->returnUrl,
            'cancel_url' => $data->cancelUrl,
        ];

        $response = Http::withBasicAuth($this->apiKey, $this->secret)
            ->post("{$this->baseUrl}/checkout/create", $payload);

        if ($response->successful()) {
            $resData = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $resData['checkout_token'],
                status: $resData['state'],
                redirectUrl: $resData['checkout_url'] ?? null,
                rawResponse: $resData,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: '',
            status: 'failed',
            errorMessage: $response->json('error.message') ?? 'Provider2 transaction failed',
            rawResponse: $response->json() ?? [],
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $providedAuth = $request->header('Authorization');
        $expectedAuth = 'Bearer '.$this->webhookSecret;

        return hash_equals($expectedAuth, (string) $providedAuth);
    }

    public function handleWebhook(Request $request): array
    {
        $this->logEvent('Webhook Received', $request->all());

        if (! $this->verifyWebhookSignature($request)) {
            throw new UnexpectedValueException('Invalid Provider2 webhook signature');
        }

        $payload = $request->all();

        return [
            'event' => $payload['action'] ?? 'unknown',
            'transaction_id' => $payload['checkout_token'] ?? null,
            'status' => $payload['state'] ?? null,
            'processed' => true,
        ];
    }

    public function getStatus(string $transactionId): PaymentResponseData
    {
        $response = Http::withBasicAuth($this->apiKey, $this->secret)
            ->get("{$this->baseUrl}/checkout/{$transactionId}");

        if ($response->successful()) {
            $data = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $data['checkout_token'],
                status: $data['state'],
                rawResponse: $data,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: $transactionId,
            status: 'unknown',
            errorMessage: 'Unable to fetch Provider2 status',
        );
    }

    public function refund(string $transactionId, float $amount): PaymentResponseData
    {
        $response = Http::withBasicAuth($this->apiKey, $this->secret)
            ->post("{$this->baseUrl}/checkout/{$transactionId}/reverse", [
                'amount_cents' => (int) round($amount * 100),
            ]);

        if ($response->successful()) {
            $data = $response->json();

            return new PaymentResponseData(
                success: true,
                transactionId: $data['reversal_id'],
                status: 'reversed',
                rawResponse: $data,
            );
        }

        return new PaymentResponseData(
            success: false,
            transactionId: $transactionId,
            status: 'refund_failed',
            errorMessage: $response->json('error.message') ?? 'Refund failed',
        );
    }
}
