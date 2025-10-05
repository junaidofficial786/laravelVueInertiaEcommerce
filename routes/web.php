<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/categories', [\App\Http\Controllers\Web\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\Web\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Web\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Web\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/products', [\App\Http\Controllers\Web\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [\App\Http\Controllers\Web\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\Web\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Web\ProductController::class, 'destroy'])->name('products.destroy');
});
