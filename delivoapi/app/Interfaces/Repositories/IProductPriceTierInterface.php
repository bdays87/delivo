<?php

namespace App\Interfaces\Repositories;

interface IProductPriceTierInterface extends IBaseInterface
{
    public function deleteForProduct(int $productId): void;

    public function syncForProduct(int $productId, array $tiers): void;
}
