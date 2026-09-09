<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestData;
use App\DTOs\PaymentResponseData;
use Illuminate\Http\Request;

interface PaymentProviderInterface
{
    /**
     * Initiate or process a payment transaction.
     */
    public function charge(PaymentRequestData $data): PaymentResponseData;

    /**
     * Verify incoming webhook signature.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Process the payload sent by payment provider webhooks.
     *
     * @return array<string, mixed>
     */
    public function handleWebhook(Request $request): array;

    /**
     * Query provider status for a specific transaction.
     */
    public function getStatus(string $transactionId): PaymentResponseData;

    /**
     * Process refund for a given transaction.
     */
    public function refund(string $transactionId, float $amount): PaymentResponseData;
}
