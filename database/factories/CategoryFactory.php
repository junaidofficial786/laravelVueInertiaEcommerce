<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 100000)),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}


