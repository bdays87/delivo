<?php

namespace App\Interfaces\Repositories;

interface IProductVariantInterface extends IBaseInterface
{
    public function deleteForProduct(int $productId): void;

    public function syncForProduct(int $productId, array $variants): void;
}
