<?php

namespace App\Providers;

use App\Interfaces\Repositories\IAddressInterface;
use App\Interfaces\Repositories\ICartInterface;
use App\Interfaces\Repositories\ICategoryInterface;
use App\Interfaces\Repositories\IDeliveryFeeInterface;
use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Interfaces\Repositories\IDeliveryProviderKycInterface;
use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Interfaces\Repositories\IExchangeRateInterface;
use App\Interfaces\Repositories\IMobileWalletInterface;
use App\Interfaces\Repositories\IModuleInterface;
use App\Interfaces\Repositories\IOrderInterface;
use App\Interfaces\Repositories\IPlatformSettingsInterface;
use App\Interfaces\Repositories\IProductImageInterface;
use App\Interfaces\Repositories\IProductInterface;
use App\Interfaces\Repositories\IProductPriceTierInterface;
use App\Interfaces\Repositories\IProductVariantInterface;
use App\Interfaces\Repositories\IUserInterface;
use App\Interfaces\Repositories\IVendorInterface;
use App\Interfaces\Repositories\IVendorKycDocumentInterface;
use App\Interfaces\Repositories\IVendorPayoutAccountInterface;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\DeliveryFeeRepository;
use App\Repositories\Eloquent\DeliveryProviderKycRepository;
use App\Repositories\Eloquent\DeliveryProviderRepository;
use App\Repositories\Eloquent\DeliveryZoneRepository;
use App\Repositories\Eloquent\ExchangeRateRepository;
use App\Repositories\Eloquent\MobileWalletRepository;
use App\Repositories\Eloquent\ModuleRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PlatformSettingsRepository;
use App\Repositories\Eloquent\ProductImageRepository;
use App\Repositories\Eloquent\ProductPriceTierRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VendorKycDocumentRepository;
use App\Repositories\Eloquent\VendorPayoutAccountRepository;
use App\Repositories\Eloquent\VendorRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository interfaces to their Eloquent implementations.
 *
 * As each domain resource is added in subsequent slices, add a bind() line
 * here pairing the interface with the concrete repository.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IUserInterface::class, UserRepository::class);
        $this->app->bind(IVendorInterface::class, VendorRepository::class);
        $this->app->bind(IVendorKycDocumentInterface::class, VendorKycDocumentRepository::class);
        $this->app->bind(IModuleInterface::class, ModuleRepository::class);
        $this->app->bind(ICategoryInterface::class, CategoryRepository::class);
        $this->app->bind(IMobileWalletInterface::class, MobileWalletRepository::class);
        $this->app->bind(IVendorPayoutAccountInterface::class, VendorPayoutAccountRepository::class);
        $this->app->bind(IProductInterface::class, ProductRepository::class);
        $this->app->bind(IProductImageInterface::class, ProductImageRepository::class);
        $this->app->bind(IProductPriceTierInterface::class, ProductPriceTierRepository::class);
        $this->app->bind(IProductVariantInterface::class, ProductVariantRepository::class);
        $this->app->bind(IExchangeRateInterface::class, ExchangeRateRepository::class);
        $this->app->bind(IAddressInterface::class, AddressRepository::class);
        $this->app->bind(ICartInterface::class, CartRepository::class);
        $this->app->bind(IOrderInterface::class, OrderRepository::class);
        $this->app->bind(IPlatformSettingsInterface::class, PlatformSettingsRepository::class);
        $this->app->bind(IDeliveryZoneInterface::class, DeliveryZoneRepository::class);
        $this->app->bind(IDeliveryFeeInterface::class, DeliveryFeeRepository::class);
        $this->app->bind(IDeliveryProviderInterface::class, DeliveryProviderRepository::class);
        $this->app->bind(IDeliveryProviderKycInterface::class, DeliveryProviderKycRepository::class);
    }
}
