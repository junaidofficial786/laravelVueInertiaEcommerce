<?php

namespace App\Support\Data;

class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public ?int $parent_id = null,
        public bool $is_active = true,
        public int $sort_order = 0,
    ) {
    }

    public static function fromArray(array $input): self
    {
        return new self(
            name: $input['name'],
            slug: $input['slug'] ?? null,
            description: $input['description'] ?? null,
            parent_id: $input['parent_id'] ?? null,
            is_active: (bool)($input['is_active'] ?? true),
            sort_order: (int)($input['sort_order'] ?? 0),
        );
    }
}


