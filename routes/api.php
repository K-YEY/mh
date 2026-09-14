<?php

use App\Http\Controllers\Operations\ContainerTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('tracking')->group(function () {
    Route::get('containers/{containerNumber}', [ContainerTrackingController::class, 'apiGetContainerTracking']);
    Route::post('containers/{containerNumber}/sync', [ContainerTrackingController::class, 'apiSyncContainerTracking']);
});
