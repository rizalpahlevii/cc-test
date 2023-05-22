<?php

namespace Database\Factories;

use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteStopFactory extends Factory
{
    protected $model = RouteStop::class;

    public function configure(): static
    {
        return $this->afterMaking(function (RouteStop $routeStop) {
            if (!$routeStop->route_id) {
                $route = Route::factory()->create();
                $routeStop->route()->associate($route);
            }
        });
    }

    public function definition(): array
    {
        return [
            'city' => $this->faker->city(),
            'location' => $this->faker->address,
        ];
    }
}
