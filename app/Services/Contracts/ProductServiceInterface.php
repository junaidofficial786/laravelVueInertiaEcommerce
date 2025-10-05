<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Support\Data\ProductData;

interface ProductServiceInterface
{
    public function create(ProductData $data): Product;

    public function update(Product $product, ProductData $data): Product;

    public function delete(Product $product): void;
}


