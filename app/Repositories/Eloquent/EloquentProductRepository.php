<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $categoryId = null): LengthAwarePaginator
    {
        return Product::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->whereHas('categories', fn ($q2) => $q2->where('categories.id', $categoryId)))
            ->with(['categories:id,name', 'images'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::query()->with(['categories', 'images'])->find($id);
    }

    public function create(array $data, array $categoryIds = []): Product
    {
        $product = Product::create($data);
        if ($categoryIds) {
            $product->categories()->sync($categoryIds);
        }
        return $product->load(['categories', 'images']);
    }

    public function update(Product $product, array $data, array $categoryIds = []): Product
    {
        $product->update($data);
        if ($categoryIds !== []) {
            $product->categories()->sync($categoryIds);
        }
        return $product->load(['categories', 'images']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}


