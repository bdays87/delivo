<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IProductImageInterface;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageRepository extends BaseRepository implements IProductImageInterface
{
    public function __construct(ProductImage $model)
    {
        parent::__construct($model);
    }

    public function createForProduct(Product $product, array $data): ProductImage
    {
        return $product->images()->create($data);
    }

    public function findForProduct(int $imageId, int $productId): ?ProductImage
    {
        return $this->model->where('id', $imageId)->where('product_id', $productId)->first();
    }

    public function clearPrimaryForProduct(int $productId): void
    {
        $this->model->where('product_id', $productId)->update(['is_primary' => false]);
    }
}
