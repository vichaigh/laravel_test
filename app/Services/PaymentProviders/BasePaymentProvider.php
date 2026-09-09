<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

abstract class BasePaymentProvider implements PaymentProviderInterface
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $secret;

    protected string $webhookSecret;

    public function __construct(string $providerKey)
    {
        $config = config("services.{$providerKey}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Configuration for provider [{$providerKey}] missing.");
        }

        $this->baseUrl = $config['base_url'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->secret = $config['secret'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? '';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function logEvent(string $type, array $data): void
    {
        Log::info('['.static::class."] {$type}", $data);
    }
}
