<?php

namespace App\DTOs;

class PaymentResponseData
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $status,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'status' => $this->status,
            'redirect_url' => $this->redirectUrl,
            'error_message' => $this->errorMessage,
            'raw' => $this->rawResponse,
        ];
    }
}
