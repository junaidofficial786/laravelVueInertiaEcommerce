<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Support\Data\CategoryData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly CategoryServiceInterface $service,
    ) {
    }

    public function index(): Response
    {
        $perPage = 10;
        $search = request()->string('search')->toString() ?: null;
        $items = $this->categories->paginate($perPage, $search);
        return Inertia::render('Categories', [
            'categories' => $items,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->service->create(CategoryData::fromArray($request->validated()));
        return back()->with('success', 'Category created');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->service->update($category, CategoryData::fromArray($request->validated()));
        return back()->with('success', 'Category updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->service->delete($category);
        return back()->with('success', 'Category deleted');
    }
}


