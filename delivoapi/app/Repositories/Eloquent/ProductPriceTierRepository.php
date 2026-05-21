<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IProductPriceTierInterface;
use App\Models\ProductPriceTier;

class ProductPriceTierRepository extends BaseRepository implements IProductPriceTierInterface
{
    public function __construct(ProductPriceTier $model)
    {
        parent::__construct($model);
    }

    public function deleteForProduct(int $productId): void
    {
        $this->model->where('product_id', $productId)->delete();
    }

    public function syncForProduct(int $productId, array $tiers): void
    {
        $this->deleteForProduct($productId);

        foreach ($tiers as $tier) {
            $this->model->create([
                'product_id' => $productId,
                'min_qty' => $tier['min_qty'],
                'unit_price' => $tier['unit_price'],
            ]);
        }
    }
}
