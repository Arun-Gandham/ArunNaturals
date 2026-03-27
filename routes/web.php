<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\WhatsappCampaignController;
use App\Http\Controllers\Admin\CouponController;
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

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.edit');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');

    // Delivery tools
    Route::get('/delivery/shipments', [AdminController::class, 'deliveryShipments'])->name('delivery.shipments');
    Route::get('/delivery/pickups', [AdminController::class, 'deliveryPickups'])->name('delivery.pickups');
    Route::get('/delivery/serviceability', [AdminController::class, 'deliveryServiceability'])->name('delivery.serviceability');

    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::post('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::patch('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.updateRole');

    Route::get('/orders/create', [AdminController::class, 'ordersCreate'])->name('orders.create');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'orderShow'])->name('orders.show');
    Route::get('/orders/{order}/label', [AdminController::class, 'orderLabel'])->name('orders.label');
    Route::post('/orders/labels/bulk', [AdminController::class, 'bulkLabels'])->name('orders.labels.bulk');

    Route::get('/insights', [AdminController::class, 'insights'])->name('insights');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    // Product gallery images
    Route::delete('/products/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');

    // WhatsApp offer campaigns
    Route::get('/whatsapp/campaigns', [WhatsappCampaignController::class, 'index'])->name('whatsapp.campaigns.index');
    Route::get('/whatsapp/campaigns/create', [WhatsappCampaignController::class, 'create'])->name('whatsapp.campaigns.create');
    Route::post('/whatsapp/campaigns', [WhatsappCampaignController::class, 'store'])->name('whatsapp.campaigns.store');
    Route::get('/whatsapp/campaigns/{campaign}/recipients', [WhatsappCampaignController::class, 'recipients'])
        ->name('whatsapp.campaigns.recipients');

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::get('/coupons/{coupon}/customers', [CouponController::class, 'customers'])->name('coupons.customers');
    Route::post('/coupons/{coupon}/exclude-recipient', [CouponController::class, 'excludeRecipient'])->name('coupons.excludeRecipient');
    Route::post('/coupons/{coupon}/recipients/save', [CouponController::class, 'saveRecipients'])->name('coupons.recipients.save');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::get('/coupons/{coupon}/recipients', [CouponController::class, 'recipients'])->name('coupons.recipients');
});
