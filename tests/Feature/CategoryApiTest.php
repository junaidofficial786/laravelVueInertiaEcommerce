<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->getJson('/api/v1/categories');
    $response->assertOk();
});

it('creates category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $payload = [
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
        'sort_order' => 1,
    ];
    $response = $this->postJson('/api/v1/categories', $payload);
    $response->assertCreated()->assertJsonPath('data.name', 'Electronics');
});


