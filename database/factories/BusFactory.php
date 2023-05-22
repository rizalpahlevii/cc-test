<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusFactory extends Factory
{
    protected $model = Bus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'number' => $this->faker->word(),
            'total_seats' => $this->faker->randomElement([20, 30, 40, 50, 60, 70, 80, 90, 100]),
        ];
    }
}
