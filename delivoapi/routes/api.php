<?php

use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminDeliveryZoneController;
use App\Http\Controllers\Api\Admin\AdminExchangeRateController;
use App\Http\Controllers\Api\Admin\AdminMobileWalletController;
use App\Http\Controllers\Api\Admin\AdminPlatformSettingsController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminVendorController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\MobileWalletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Vendor\VendorController;
use App\Http\Controllers\Api\Vendor\VendorDashboardController;
use App\Http\Controllers\Api\Vendor\VendorPayoutAccountController;
use App\Http\Controllers\Api\Vendor\VendorProductController;
use App\Http\Controllers\Api\VendorPublicController;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;




Route::prefix('v1')->group(function () {
    Route::get('ping', fn () => ApiResponse::success(['pong' => true], 'Delivo API is up.'))
        ->name('v1.ping');

    Route::post('auth/register', [AuthController::class, 'register'])->name('v1.auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('v1.auth.login');

    Route::get('categories/list', [CategoryController::class, 'listActive'])->name('v1.categories.list');

    // Public storefront product browse
    Route::get('products', [ProductController::class, 'index'])->name('v1.products.index');
    Route::get('products/{slug}', [ProductController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('v1.products.show');

    // Public vendor directory ("Shop by store")
    Route::get('vendors/list', [VendorPublicController::class, 'listActive'])
        ->name('v1.vendors.list');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('v1.auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('v1.me');

        // Reference lookup — active mobile wallets for vendor payout config
        Route::get('mobile-wallets/list', [MobileWalletController::class, 'listActive'])
            ->name('v1.mobile-wallets.list');

        // Storefront-side vendor self-service
        // Customer cart + addresses + checkout + orders
        Route::get('cart', [CartController::class, 'show'])->name('v1.cart.show');
        Route::post('cart/items', [CartController::class, 'addItem'])->name('v1.cart.items.add');
        Route::put('cart/items/{itemId}', [CartController::class, 'updateItem'])
            ->whereNumber('itemId')->name('v1.cart.items.update');
        Route::delete('cart/items/{itemId}', [CartController::class, 'removeItem'])
            ->whereNumber('itemId')->name('v1.cart.items.remove');
        Route::delete('cart', [CartController::class, 'clear'])->name('v1.cart.clear');

        Route::get('addresses', [AddressController::class, 'index'])->name('v1.addresses.index');
        Route::post('addresses', [AddressController::class, 'store'])->name('v1.addresses.store');
        Route::put('addresses/{id}', [AddressController::class, 'update'])
            ->whereNumber('id')->name('v1.addresses.update');
        Route::delete('addresses/{id}', [AddressController::class, 'destroy'])
            ->whereNumber('id')->name('v1.addresses.destroy');
        Route::post('addresses/{id}/default', [AddressController::class, 'setDefault'])
            ->whereNumber('id')->name('v1.addresses.default');

        Route::post('checkout/quote', [CheckoutController::class, 'quote'])->name('v1.checkout.quote');
        Route::post('checkout', [CheckoutController::class, 'place'])->name('v1.checkout.place');

        Route::get('orders', [OrderController::class, 'index'])->name('v1.orders.index');
        Route::get('orders/{orderNumber}', [OrderController::class, 'show'])
            ->where('orderNumber', '[A-Z0-9-]+')->name('v1.orders.show');

        Route::post('vendor/apply', [VendorController::class, 'apply'])->name('v1.vendor.apply');
        Route::get('vendor/me', [VendorController::class, 'currentVendor'])->name('v1.vendor.me');
        Route::get('vendor/me/dashboard', [VendorDashboardController::class, 'show'])->name('v1.vendor.dashboard');
        Route::post('vendor/me/kyc-documents', [VendorController::class, 'uploadKyc'])->name('v1.vendor.kyc.upload');

        // Vendor payout accounts (one vendor → many accounts; ZWG + USD + mobile wallets)
        Route::get('vendor/me/payout-accounts', [VendorPayoutAccountController::class, 'index'])
            ->name('v1.vendor.payout-accounts.index');
        Route::post('vendor/me/payout-accounts', [VendorPayoutAccountController::class, 'store'])
            ->name('v1.vendor.payout-accounts.store');
        Route::put('vendor/me/payout-accounts/{id}', [VendorPayoutAccountController::class, 'update'])
            ->whereNumber('id')->name('v1.vendor.payout-accounts.update');
        Route::delete('vendor/me/payout-accounts/{id}', [VendorPayoutAccountController::class, 'destroy'])
            ->whereNumber('id')->name('v1.vendor.payout-accounts.destroy');
        Route::post('vendor/me/payout-accounts/{id}/primary', [VendorPayoutAccountController::class, 'setPrimary'])
            ->whereNumber('id')->name('v1.vendor.payout-accounts.primary');

        // Vendor product CRUD
        Route::get('vendor/me/products', [VendorProductController::class, 'index'])->name('v1.vendor.products.index');
        Route::post('vendor/me/products', [VendorProductController::class, 'store'])->name('v1.vendor.products.store');
        Route::get('vendor/me/products/{id}', [VendorProductController::class, 'show'])
            ->whereNumber('id')->name('v1.vendor.products.show');
        Route::put('vendor/me/products/{id}', [VendorProductController::class, 'update'])
            ->whereNumber('id')->name('v1.vendor.products.update');
        Route::delete('vendor/me/products/{id}', [VendorProductController::class, 'destroy'])
            ->whereNumber('id')->name('v1.vendor.products.destroy');
        Route::post('vendor/me/products/{id}/resubmit', [VendorProductController::class, 'resubmit'])
            ->whereNumber('id')->name('v1.vendor.products.resubmit');
        Route::post('vendor/me/products/{id}/images', [VendorProductController::class, 'uploadImage'])
            ->whereNumber('id')->name('v1.vendor.products.images.upload');
        Route::post('vendor/me/products/{id}/images/{imageId}/primary', [VendorProductController::class, 'setPrimaryImage'])
            ->whereNumber(['id', 'imageId'])->name('v1.vendor.products.images.primary');
        Route::delete('vendor/me/products/{id}/images/{imageId}', [VendorProductController::class, 'deleteImage'])
            ->whereNumber(['id', 'imageId'])->name('v1.vendor.products.images.destroy');

        // Admin moderation surface
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('vendors', [AdminVendorController::class, 'index'])->name('v1.admin.vendors.index');
            Route::get('vendors/{id}', [AdminVendorController::class, 'show'])->whereNumber('id')->name('v1.admin.vendors.show');
            Route::post('vendors/{id}/approve', [AdminVendorController::class, 'approve'])->whereNumber('id')->name('v1.admin.vendors.approve');
            Route::post('vendors/{id}/reject', [AdminVendorController::class, 'reject'])->whereNumber('id')->name('v1.admin.vendors.reject');
            Route::post('vendors/{id}/suspend', [AdminVendorController::class, 'suspend'])->whereNumber('id')->name('v1.admin.vendors.suspend');
            Route::get('vendors/{vendor}/kyc-documents/{document}', [AdminVendorController::class, 'downloadKyc'])
                ->whereNumber(['vendor', 'document'])
                ->name('v1.admin.vendors.kyc.download');

            // Mobile wallet reference data
            Route::get('mobile-wallets', [AdminMobileWalletController::class, 'index'])->name('v1.admin.mobile-wallets.index');
            Route::post('mobile-wallets', [AdminMobileWalletController::class, 'store'])->name('v1.admin.mobile-wallets.store');
            Route::get('mobile-wallets/{id}', [AdminMobileWalletController::class, 'show'])->whereNumber('id')->name('v1.admin.mobile-wallets.show');
            Route::put('mobile-wallets/{id}', [AdminMobileWalletController::class, 'update'])->whereNumber('id')->name('v1.admin.mobile-wallets.update');
            Route::delete('mobile-wallets/{id}', [AdminMobileWalletController::class, 'destroy'])->whereNumber('id')->name('v1.admin.mobile-wallets.destroy');

            Route::get('categories', [AdminCategoryController::class, 'index'])->name('v1.admin.categories.index');
            Route::post('categories', [AdminCategoryController::class, 'store'])->name('v1.admin.categories.store');
            Route::get('categories/{id}', [AdminCategoryController::class, 'show'])->whereNumber('id')->name('v1.admin.categories.show');
            Route::put('categories/{id}', [AdminCategoryController::class, 'update'])->whereNumber('id')->name('v1.admin.categories.update');
            Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy'])->whereNumber('id')->name('v1.admin.categories.destroy');

            // Admin product moderation
            Route::get('products', [AdminProductController::class, 'index'])->name('v1.admin.products.index');
            Route::get('products/{id}', [AdminProductController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.products.show');
            Route::post('products/{id}/approve', [AdminProductController::class, 'approve'])
                ->whereNumber('id')->name('v1.admin.products.approve');
            Route::post('products/{id}/reject', [AdminProductController::class, 'reject'])
                ->whereNumber('id')->name('v1.admin.products.reject');
            Route::post('products/{id}/takedown', [AdminProductController::class, 'takedown'])
                ->whereNumber('id')->name('v1.admin.products.takedown');

            // Admin USD→ZWG exchange rate
            Route::get('exchange-rates/usd-zwg', [AdminExchangeRateController::class, 'show'])
                ->name('v1.admin.exchange-rates.show');
            Route::put('exchange-rates/usd-zwg', [AdminExchangeRateController::class, 'update'])
                ->name('v1.admin.exchange-rates.update');

            // Platform settings (service charge, default delivery fee)
            Route::get('platform-settings', [AdminPlatformSettingsController::class, 'show'])
                ->name('v1.admin.platform-settings.show');
            Route::put('platform-settings', [AdminPlatformSettingsController::class, 'update'])
                ->name('v1.admin.platform-settings.update');

            // Delivery zones (per-city delivery fees)
            Route::get('delivery-zones', [AdminDeliveryZoneController::class, 'index'])
                ->name('v1.admin.delivery-zones.index');
            Route::post('delivery-zones', [AdminDeliveryZoneController::class, 'store'])
                ->name('v1.admin.delivery-zones.store');
            Route::get('delivery-zones/{id}', [AdminDeliveryZoneController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.delivery-zones.show');
            Route::put('delivery-zones/{id}', [AdminDeliveryZoneController::class, 'update'])
                ->whereNumber('id')->name('v1.admin.delivery-zones.update');
            Route::delete('delivery-zones/{id}', [AdminDeliveryZoneController::class, 'destroy'])
                ->whereNumber('id')->name('v1.admin.delivery-zones.destroy');
        });
    });
});
