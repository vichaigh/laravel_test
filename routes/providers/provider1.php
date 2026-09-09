<?php

use App\Http\Controllers\PaymentController;
use App\Http\Requests\Provider1CheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Standalone routes specific to Provider1 (mounted under the "provider1" prefix).

Route::post('/checkout', fn (Provider1CheckoutRequest $request) => app(PaymentController::class)->checkout('provider1', $request));

Route::post('/webhook', fn (Request $request) => app(PaymentController::class)->webhook('provider1', $request));
