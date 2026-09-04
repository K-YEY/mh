<?php

use App\Http\Controllers\Api\TrackingWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Webhook & Third-Party Integration Routes
|--------------------------------------------------------------------------
*/

Route::post('/v1/webhooks/tracking/{provider}', [TrackingWebhookController::class, 'handle'])
    ->name('api.webhooks.tracking');
