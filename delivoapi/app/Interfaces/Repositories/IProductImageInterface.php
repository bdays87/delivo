<?php

namespace App\Interfaces\Repositories;

use App\Models\Product;
use App\Models\ProductImage;

interface IProductImageInterface extends IBaseInterface
{
    public function createForProduct(Product $product, array $data): ProductImage;

    public function findForProduct(int $imageId, int $productId): ?ProductImage;

    public function clearPrimaryForProduct(int $productId): void;
}
