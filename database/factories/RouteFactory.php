<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        return [
            'source_city' => $this->faker->city(),
            'destination_city' => $this->faker->city(),
        ];
    }
}
