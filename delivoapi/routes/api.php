<?php

use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminDeliveryFeeController;
use App\Http\Controllers\Api\Admin\AdminDeliveryProviderController;
use App\Http\Controllers\Api\Admin\AdminDeliveryZoneController;
use App\Http\Controllers\Api\Admin\AdminExchangeRateController;
use App\Http\Controllers\Api\Admin\AdminInfluencerController;
use App\Http\Controllers\Api\Admin\AdminMobileWalletController;
use App\Http\Controllers\Api\Admin\AdminModuleController;
use App\Http\Controllers\Api\Admin\AdminPlatformSettingsController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\AdminVehicleTypeController;
use App\Http\Controllers\Api\Admin\AdminVendorController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CoverageAreaController;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Influencer\InfluencerController;
use App\Http\Controllers\Api\Influencer\InfluencerProductController;
use App\Http\Controllers\Api\MobileWalletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Provider\ProviderController;
use App\Http\Controllers\Api\Provider\ProviderShipmentController;
use App\Http\Controllers\Api\VehicleTypeController;
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

    // Public coverage areas — the strict list of cities Delivo delivers to.
    // Used by vendor apply + checkout address forms to constrain choices.
    Route::get('coverage-areas/list', [CoverageAreaController::class, 'listActive'])
        ->name('v1.coverage-areas.list');

    // Public vehicle types — powers the provider apply multi-select.
    Route::get('vehicle-types/list', [VehicleTypeController::class, 'listActive'])
        ->name('v1.vehicle-types.list');

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
        Route::post('cart/coupon', [CartController::class, 'applyCoupon'])->name('v1.cart.coupon.apply');
        Route::delete('cart/coupon', [CartController::class, 'removeCoupon'])->name('v1.cart.coupon.remove');

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

        // Influencer self-service
        Route::post('influencer/apply', [InfluencerController::class, 'apply'])->name('v1.influencer.apply');
        Route::get('influencer/me', [InfluencerController::class, 'currentInfluencer'])->name('v1.influencer.me');
        Route::post('influencer/me/handles', [InfluencerController::class, 'addHandle'])->name('v1.influencer.handles.add');
        Route::delete('influencer/me/handles/{handleId}', [InfluencerController::class, 'deleteHandle'])
            ->whereNumber('handleId')->name('v1.influencer.handles.delete');

        // Influencer product browse + code generation
        Route::get('influencer/me/products', [InfluencerProductController::class, 'index'])
            ->name('v1.influencer.products.index');
        Route::post('influencer/me/products/{productId}/code', [InfluencerProductController::class, 'createCode'])
            ->whereNumber('productId')->name('v1.influencer.products.code');
        Route::get('influencer/me/codes', [InfluencerProductController::class, 'codes'])
            ->name('v1.influencer.codes.index');

        // Delivery provider self-service (apply, KYC, coverage selection)
        Route::post('provider/apply', [ProviderController::class, 'apply'])->name('v1.provider.apply');
        Route::get('provider/me', [ProviderController::class, 'currentProvider'])->name('v1.provider.me');
        Route::post('provider/me/kyc-documents', [ProviderController::class, 'uploadKyc'])->name('v1.provider.kyc.upload');
        Route::post('provider/me/coverage', [ProviderController::class, 'syncCoverage'])->name('v1.provider.coverage.sync');
        Route::post('provider/me/routes', [ProviderController::class, 'syncRoutes'])->name('v1.provider.routes.sync');
        Route::post('provider/me/offers-intra-city', [ProviderController::class, 'setOffersIntraCity'])
            ->name('v1.provider.offers-intra-city');

        // Provider shipment pipeline
        Route::get('provider/me/shipments', [ProviderShipmentController::class, 'index'])->name('v1.provider.shipments.index');
        Route::get('provider/me/shipments/{shipmentId}', [ProviderShipmentController::class, 'show'])
            ->whereNumber('shipmentId')->name('v1.provider.shipments.show');
        Route::post('provider/me/shipments/{shipmentId}/pickup', [ProviderShipmentController::class, 'pickup'])
            ->whereNumber('shipmentId')->name('v1.provider.shipments.pickup');
        Route::post('provider/me/shipments/{shipmentId}/dispatch', [ProviderShipmentController::class, 'dispatch'])
            ->whereNumber('shipmentId')->name('v1.provider.shipments.dispatch');
        Route::post('provider/me/shipments/{shipmentId}/deliver', [ProviderShipmentController::class, 'deliver'])
            ->whereNumber('shipmentId')->name('v1.provider.shipments.deliver');

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

            // Delivery provider moderation
            Route::get('delivery-providers', [AdminDeliveryProviderController::class, 'index'])
                ->name('v1.admin.delivery-providers.index');
            Route::get('delivery-providers/{id}', [AdminDeliveryProviderController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.delivery-providers.show');
            Route::post('delivery-providers/{id}/approve', [AdminDeliveryProviderController::class, 'approve'])
                ->whereNumber('id')->name('v1.admin.delivery-providers.approve');
            Route::post('delivery-providers/{id}/reject', [AdminDeliveryProviderController::class, 'reject'])
                ->whereNumber('id')->name('v1.admin.delivery-providers.reject');
            Route::post('delivery-providers/{id}/suspend', [AdminDeliveryProviderController::class, 'suspend'])
                ->whereNumber('id')->name('v1.admin.delivery-providers.suspend');
            Route::get('delivery-providers/{provider}/kyc-documents/{document}', [AdminDeliveryProviderController::class, 'downloadKyc'])
                ->whereNumber(['provider', 'document'])
                ->name('v1.admin.delivery-providers.kyc.download');

            // Influencer moderation
            Route::get('influencers', [AdminInfluencerController::class, 'index'])
                ->name('v1.admin.influencers.index');
            Route::get('influencers/{id}', [AdminInfluencerController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.influencers.show');
            Route::post('influencers/{id}/approve', [AdminInfluencerController::class, 'approve'])
                ->whereNumber('id')->name('v1.admin.influencers.approve');
            Route::post('influencers/{id}/reject', [AdminInfluencerController::class, 'reject'])
                ->whereNumber('id')->name('v1.admin.influencers.reject');
            Route::post('influencers/{id}/suspend', [AdminInfluencerController::class, 'suspend'])
                ->whereNumber('id')->name('v1.admin.influencers.suspend');
            Route::post('influencers/{influencer}/handles/{handle}/status', [AdminInfluencerController::class, 'setHandleStatus'])
                ->whereNumber(['influencer', 'handle'])
                ->name('v1.admin.influencers.handles.status');

            // Vehicle types (CRUD).
            Route::get('vehicle-types', [AdminVehicleTypeController::class, 'index'])
                ->name('v1.admin.vehicle-types.index');
            Route::post('vehicle-types', [AdminVehicleTypeController::class, 'store'])
                ->name('v1.admin.vehicle-types.store');
            Route::get('vehicle-types/{id}', [AdminVehicleTypeController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.vehicle-types.show');
            Route::put('vehicle-types/{id}', [AdminVehicleTypeController::class, 'update'])
                ->whereNumber('id')->name('v1.admin.vehicle-types.update');
            Route::delete('vehicle-types/{id}', [AdminVehicleTypeController::class, 'destroy'])
                ->whereNumber('id')->name('v1.admin.vehicle-types.destroy');

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

            // Distance-based delivery fee bands.
            Route::get('delivery-fees', [AdminDeliveryFeeController::class, 'index'])
                ->name('v1.admin.delivery-fees.index');
            Route::post('delivery-fees', [AdminDeliveryFeeController::class, 'store'])
                ->name('v1.admin.delivery-fees.store');
            Route::get('delivery-fees/{id}', [AdminDeliveryFeeController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.delivery-fees.show');
            Route::put('delivery-fees/{id}', [AdminDeliveryFeeController::class, 'update'])
                ->whereNumber('id')->name('v1.admin.delivery-fees.update');
            Route::delete('delivery-fees/{id}', [AdminDeliveryFeeController::class, 'destroy'])
                ->whereNumber('id')->name('v1.admin.delivery-fees.destroy');

            // System modules tree (read-only). Modules + submodules + permissions
            // are seeder-managed. The submodule-permissions endpoint self-heals
            // the two default permissions on every read.
            Route::get('modules', [AdminModuleController::class, 'index'])->name('v1.admin.modules.index');
            Route::get('modules/tree', [AdminModuleController::class, 'tree'])->name('v1.admin.modules.tree');
            Route::get('modules/{id}', [AdminModuleController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.modules.show');
            Route::get('modules/{moduleId}/submodules/{submoduleId}/permissions', [AdminModuleController::class, 'submodulePermissions'])
                ->whereNumber(['moduleId', 'submoduleId'])
                ->name('v1.admin.modules.submodule-permissions');
            Route::get('permissions', [AdminModuleController::class, 'allPermissions'])
                ->name('v1.admin.permissions.index');

            // Roles CRUD + permission assignment.
            Route::get('roles', [AdminRoleController::class, 'index'])->name('v1.admin.roles.index');
            Route::post('roles', [AdminRoleController::class, 'store'])->name('v1.admin.roles.store');
            Route::get('roles/{id}', [AdminRoleController::class, 'show'])
                ->whereNumber('id')->name('v1.admin.roles.show');
            Route::put('roles/{id}', [AdminRoleController::class, 'update'])
                ->whereNumber('id')->name('v1.admin.roles.update');
            Route::delete('roles/{id}', [AdminRoleController::class, 'destroy'])
                ->whereNumber('id')->name('v1.admin.roles.destroy');
            Route::post('roles/{id}/permissions/sync', [AdminRoleController::class, 'syncPermissions'])
                ->whereNumber('id')->name('v1.admin.roles.permissions.sync');
        });
    });
});
