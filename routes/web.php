<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DelhiveryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('delhivery')->group(function () {
    Route::get('/pincode/{pincode}', [DelhiveryController::class, 'checkPincode']);
    Route::post('/shipping-cost', [DelhiveryController::class, 'calculateShippingCost']);
    Route::post('/shipping-label', [DelhiveryController::class, 'generateShippingLabel']);
    Route::post('/track', [DelhiveryController::class, 'trackShipment']);
});
Route::get('/', function () {
    return view('welcome');
});
