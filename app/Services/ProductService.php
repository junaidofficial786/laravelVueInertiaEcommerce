<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Support\Data\ProductData;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly DB $db,
    ) {
    }

    public function create(ProductData $data): Product
    {
        return $this->db->transaction(function () use ($data) {
            $prepared = $this->prepare($data);
            return $this->products->create($prepared['attributes'], $prepared['category_ids']);
        });
    }

    public function update(Product $product, ProductData $data): Product
    {
        return $this->db->transaction(function () use ($product, $data) {
            $prepared = $this->prepare($data, $product);
            return $this->products->update($product, $prepared['attributes'], $prepared['category_ids']);
        });
    }

    public function delete(Product $product): void
    {
        $this->db->transaction(function () use ($product) {
            $this->products->delete($product);
        });
    }

    private function prepare(ProductData $data, ?Product $existing = null): array
    {
        $attrs = [
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'compare_at_price' => $data->compare_at_price,
            'stock' => $data->stock,
            'is_active' => $data->is_active,
        ];

        $slugSource = $data->slug ?? $data->name;
        $slug = Str::slug($slugSource);
        if ($existing && $existing->slug === $slug) {
            $attrs['slug'] = $slug;
        } else {
            $base = $slug;
            $i = 1;
            while (Product::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $attrs['slug'] = $slug;
        }

        return [
            'attributes' => $attrs,
            'category_ids' => $data->category_ids,
        ];
    }
}


