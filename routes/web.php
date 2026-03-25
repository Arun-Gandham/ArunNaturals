<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Enable Laravel auth routes (login, register, etc.)
Auth::routes();

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

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Admin\AdminController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::post('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::patch('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.updateRole');
    Route::get('/orders/create', [AdminController::class, 'ordersCreate'])->name('orders.create');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/insights', [AdminController::class, 'insights'])->name('insights');
});
