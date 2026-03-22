<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DelhiveryController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('delhivery')->group(function () {
    Route::get('/pincode/{pincode}', [DelhiveryController::class, 'checkPincode']);
    Route::post('/shipping-cost', [DelhiveryController::class, 'calculateShippingCost']);
    Route::post('/shipping-label', [DelhiveryController::class, 'generateShippingLabel']);
    Route::post('/track', [DelhiveryController::class, 'trackShipment']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
