<?php

namespace App\Services\Contracts;

use App\Models\Category;
use App\Support\Data\CategoryData;

interface CategoryServiceInterface
{
    public function create(CategoryData $data): Category;

    public function update(Category $category, CategoryData $data): Category;

    public function delete(Category $category): void;
}


