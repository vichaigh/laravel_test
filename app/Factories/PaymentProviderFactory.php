<?php

namespace App\Factories;

use App\Contracts\PaymentProviderInterface;
use App\Exceptions\UnsupportedProviderException;
use App\Services\PaymentProviders\Provider1Service;
use App\Services\PaymentProviders\Provider2Service;

class PaymentProviderFactory
{
    /**
     * Map available payment provider keys to driver classes.
     *
     * @var array<string, class-string<PaymentProviderInterface>>
     */
    protected static array $drivers = [
        'provider1' => Provider1Service::class,
        'provider2' => Provider2Service::class,
    ];

    /**
     * Resolve provider instance by key.
     *
     * @throws UnsupportedProviderException
     */
    public static function make(string $provider): PaymentProviderInterface
    {
        $key = strtolower(trim($provider));

        if (! array_key_exists($key, static::$drivers)) {
            throw UnsupportedProviderException::make($provider);
        }

        return app(static::$drivers[$key]);
    }

    /**
     * Register a provider driver at runtime.
     *
     * @param  class-string<PaymentProviderInterface>  $class
     */
    public static function registerDriver(string $name, string $class): void
    {
        static::$drivers[strtolower($name)] = $class;
    }

    /**
     * List the registered provider keys.
     *
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return array_keys(static::$drivers);
    }
}
