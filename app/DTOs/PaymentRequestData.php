<?php

namespace App\DTOs;

class PaymentRequestData
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $referenceId,
        public readonly string $returnUrl,
        public readonly string $cancelUrl,
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerName = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            currency: strtoupper($data['currency']),
            referenceId: (string) $data['reference_id'],
            returnUrl: (string) $data['return_url'],
            cancelUrl: (string) $data['cancel_url'],
            customerEmail: $data['customer_email'] ?? null,
            customerName: $data['customer_name'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }
}
