<?php

use App\Http\Controllers\PaymentController;
use App\Http\Requests\Provider2CheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Standalone routes specific to Provider2 (mounted under the "provider2" prefix).

Route::post('/checkout', fn (Provider2CheckoutRequest $request) => app(PaymentController::class)->checkout('provider2', $request));

Route::post('/webhook', fn (Request $request) => app(PaymentController::class)->webhook('provider2', $request));
