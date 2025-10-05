<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Services\CategoryService;
use App\Services\Contracts\CategoryServiceInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\ProductService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
