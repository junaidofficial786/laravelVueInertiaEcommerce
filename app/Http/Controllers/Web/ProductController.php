<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Support\Data\ProductData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductServiceInterface $service,
    ) {
    }

    public function index(): Response
    {
        $perPage = 10;
        $search = request()->string('search')->toString() ?: null;
        $categoryId = request()->integer('category_id') ?: null;
        $items = $this->products->paginate($perPage, $search, $categoryId);
        return Inertia::render('Products', [
            'products' => $items,
            'filters' => compact('search', 'categoryId'),
            'allCategories' => \App\Models\Category::query()->active()->orderBy('name')->get(['id','name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->service->create(ProductData::fromArray($request->validated()));
        $this->storeImages($product, $request->file('images', []));
        return back()->with('success', 'Product created');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product = $this->service->update($product, ProductData::fromArray($request->validated()));
        $this->storeImages($product, $request->file('images', []), (bool)$request->boolean('replace_images'));
        return back()->with('success', 'Product updated');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->service->delete($product);
        return back()->with('success', 'Product deleted');
    }

    private function storeImages(Product $product, array $files, bool $replace = false): void
    {
        if ($replace) {
            $product->images()->delete();
        }
        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) continue;
            $path = $file->store('products/'.$product->id, 'public');
            $product->images()->create([
                'disk' => 'public',
                'path' => $path,
                'sort_order' => $index,
            ]);
        }
    }
}


