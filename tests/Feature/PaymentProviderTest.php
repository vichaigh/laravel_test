<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.provider1', [
            'base_url' => 'https://api.provider1.test/v1',
            'api_key' => 'p1_key',
            'secret' => 'p1_secret',
            'webhook_secret' => 'whsec_p1',
        ]);

        config()->set('services.provider2', [
            'base_url' => 'https://api.provider2.test/v2',
            'api_key' => 'p2_key',
            'secret' => 'p2_secret',
            'webhook_secret' => 'whsec_p2',
        ]);
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'amount' => 49.99,
            'currency' => 'USD',
            'reference_id' => 'ORDER-1001',
            'return_url' => 'https://shop.test/success',
            'cancel_url' => 'https://shop.test/cancel',
        ], $overrides);
    }

    public function test_checkout_returns_redirect_url_from_provider(): void
    {
        Http::fake([
            'api.provider1.test/*' => Http::response([
                'id' => 'ch_123',
                'status' => 'pending',
                'payment_url' => 'https://pay.provider1.test/ch_123',
            ], 200),
        ]);

        $response = $this->postJson('/api/provider1/checkout', $this->checkoutPayload());

        $response->assertOk()->assertJson([
            'success' => true,
            'transaction_id' => 'ch_123',
            'status' => 'pending',
            'redirect_url' => 'https://pay.provider1.test/ch_123',
        ]);
    }

    public function test_checkout_validates_input(): void
    {
        $this->postJson('/api/provider1/checkout', $this->checkoutPayload(['amount' => 0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->postJson('/api/stripe/checkout', $this->checkoutPayload())
            ->assertNotFound();
    }

    public function test_failed_provider_response_maps_to_400(): void
    {
        Http::fake([
            'api.provider1.test/*' => Http::response(['message' => 'card_declined'], 402),
        ]);

        $this->postJson('/api/provider1/checkout', $this->checkoutPayload())
            ->assertStatus(400)
            ->assertJson(['success' => false, 'error_message' => 'card_declined']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->withToken('whsec_p1')
            ->postJson('/api/provider1/webhook', ['event_type' => 'charge.succeeded'])
            ->assertStatus(401);
    }

    public function test_webhook_returns_401_when_bearer_token_is_missing(): void
    {
        $this->postJson('/api/provider1/webhook', ['event_type' => 'charge.succeeded'])
            ->assertUnauthorized();
    }

    public function test_webhook_returns_401_when_bearer_token_does_not_match_provider_secret(): void
    {
        $this->withToken('invalid-webhook-secret')
            ->postJson('/api/provider1/webhook', ['event_type' => 'charge.succeeded'])
            ->assertUnauthorized();
    }

    public function test_webhook_returns_401_when_provider_webhook_secret_is_not_configured(): void
    {
        config()->set('services.provider1.webhook_secret');

        $this->withToken('whsec_p1')
            ->postJson('/api/provider1/webhook', ['event_type' => 'charge.succeeded'])
            ->assertUnauthorized();
    }

    public function test_webhook_accepts_valid_signature(): void
    {
        $payload = ['event_type' => 'charge.succeeded', 'data' => ['id' => 'ch_9', 'status' => 'paid']];
        $signature = hash_hmac('sha256', json_encode($payload), 'whsec_p1');

        $this->withToken('whsec_p1')
            ->withHeaders(['X-Provider1-Signature' => $signature])
            ->postJson('/api/provider1/webhook', $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'acknowledged',
                'data' => ['event' => 'charge.succeeded', 'transaction_id' => 'ch_9', 'status' => 'paid'],
            ]);
    }
}
