<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly DB $db,
    ) {
    }

    public function create(array $payload): Category
    {
        return $this->db->transaction(function () use ($payload) {
            $data = $this->prepare($payload);
            return $this->categories->create($data);
        });
    }

    public function update(Category $category, array $payload): Category
    {
        return $this->db->transaction(function () use ($category, $payload) {
            $data = $this->prepare($payload, $category);
            return $this->categories->update($category, $data);
        });
    }

    public function delete(Category $category): void
    {
        $this->db->transaction(function () use ($category) {
            // Optional: handle reassigning/moving children before delete
            $this->categories->delete($category);
        });
    }

    private function prepare(array $payload, ?Category $existing = null): array
    {
        $data = [
            'name' => $payload['name'],
            'description' => $payload['description'] ?? null,
            'parent_id' => $payload['parent_id'] ?? null,
            'is_active' => (bool)($payload['is_active'] ?? true),
            'sort_order' => (int)($payload['sort_order'] ?? 0),
        ];

        $slugSource = $payload['slug'] ?? $data['name'];
        $slug = Str::slug($slugSource);
        if ($existing && $existing->slug === $slug) {
            $data['slug'] = $slug;
            return $data;
        }

        // Ensure slug uniqueness
        $base = $slug;
        $i = 1;
        while (Category::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        $data['slug'] = $slug;

        return $data;
    }
}


