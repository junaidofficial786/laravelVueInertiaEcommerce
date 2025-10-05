<?php

namespace App\Support\Data;

class ProductData
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public float $price = 0.0,
        public ?float $compare_at_price = null,
        public int $stock = 0,
        public bool $is_active = true,
        public array $category_ids = [],
    ) {
    }

    public static function fromArray(array $input): self
    {
        return new self(
            name: $input['name'],
            slug: $input['slug'] ?? null,
            description: $input['description'] ?? null,
            price: (float)($input['price'] ?? 0),
            compare_at_price: isset($input['compare_at_price']) ? (float)$input['compare_at_price'] : null,
            stock: (int)($input['stock'] ?? 0),
            is_active: (bool)($input['is_active'] ?? true),
            category_ids: $input['category_ids'] ?? [],
        );
    }
}


