<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Support\Data\CategoryData;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly DB $db,
    ) {
    }

    public function create(CategoryData $data): Category
    {
        return $this->db->transaction(function () use ($data) {
            $prepared = $this->prepare($data);
            return $this->categories->create($prepared);
        });
    }

    public function update(Category $category, CategoryData $data): Category
    {
        return $this->db->transaction(function () use ($category, $data) {
            $prepared = $this->prepare($data, $category);
            return $this->categories->update($category, $prepared);
        });
    }

    public function delete(Category $category): void
    {
        $this->db->transaction(function () use ($category) {
            // Optional: handle reassigning/moving children before delete
            $this->categories->delete($category);
        });
    }

    private function prepare(CategoryData $payload, ?Category $existing = null): array
    {
        $data = [
            'name' => $payload->name,
            'description' => $payload->description,
            'parent_id' => $payload->parent_id,
            'is_active' => $payload->is_active,
            'sort_order' => $payload->sort_order,
        ];

        $slugSource = $payload->slug ?? $data['name'];
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


