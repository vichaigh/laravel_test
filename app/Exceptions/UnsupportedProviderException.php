<?php

namespace App\Exceptions;

use Exception;

class UnsupportedProviderException extends Exception
{
    public static function make(string $provider): self
    {
        return new self("Payment provider [{$provider}] is not configured or supported.");
    }
}
