<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $categoryId = null): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function create(array $data, array $categoryIds = []): Product;

    public function update(Product $product, array $data, array $categoryIds = []): Product;

    public function delete(Product $product): void;
}


