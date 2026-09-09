<?php

use App\Factories\PaymentProviderFactory;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Prefixed with "/api" and assigned the "api" middleware group.
|
| Multi-provider payment endpoints (Option A: dynamic URL routing):
|   POST /api/{provider}/checkout
|   POST /api/{provider}/webhook
|   GET  /api/{provider}/status/{transactionId}
|   POST /api/{provider}/refund/{transactionId}
|
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::prefix('{provider}')
    ->whereIn('provider', PaymentProviderFactory::supported())
    ->group(function () {
        Route::post('/checkout', [PaymentController::class, 'checkout']);
        Route::post('/webhook', [PaymentController::class, 'webhook']);
        Route::get('/status/{transactionId}', [PaymentController::class, 'status']);
        Route::post('/refund/{transactionId}', [PaymentController::class, 'refund']);
    });

/*
| Option B: isolated per-provider route files. Use these instead of the
| dynamic group above if you want each provider fully separated.
|
| Route::prefix('provider1')->group(base_path('routes/providers/provider1.php'));
| Route::prefix('provider2')->group(base_path('routes/providers/provider2.php'));
*/
