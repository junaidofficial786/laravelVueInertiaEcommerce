<?php

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Support\Str;

it('creates category with unique slug', function () {
    $repo = Mockery::mock(CategoryRepositoryInterface::class);
    $repo->shouldReceive('create')->andReturnUsing(function ($data) {
        $model = new Category($data);
        $model->id = 1;
        return $model;
    });

    $service = app()->make(CategoryService::class, ['categories' => $repo]);

    $result = $service->create(['name' => 'Men Shirts']);

    expect($result->slug)->toBe(Str::slug('Men Shirts'));
});


