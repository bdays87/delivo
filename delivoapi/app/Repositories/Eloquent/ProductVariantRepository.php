<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IProductVariantInterface;
use App\Models\ProductVariant;

class ProductVariantRepository extends BaseRepository implements IProductVariantInterface
{
    public function __construct(ProductVariant $model)
    {
        parent::__construct($model);
    }

    public function deleteForProduct(int $productId): void
    {
        $this->model->where('product_id', $productId)->delete();
    }

    public function syncForProduct(int $productId, array $variants): void
    {
        $this->deleteForProduct($productId);

        foreach ($variants as $variant) {
            $this->model->create([
                'product_id' => $productId,
                'color' => $variant['color'] ?? null,
                'stock_quantity' => $variant['stock_quantity'] ?? 0,
                'sku' => $variant['sku'] ?? null,
            ]);
        }
    }
}
