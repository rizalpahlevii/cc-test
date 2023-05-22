<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\BusClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusClassFactory extends Factory
{
    protected $model = BusClass::class;


    public function definition(): array
    {
        return [
            'bus_id' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'price' => $this->faker->randomFloat(),
            'total_seats' => $this->faker->randomElement([20, 30, 40]),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (BusClass $busClass) {
            if (!$busClass->bus_id) {
                $bus = Bus::factory()->create();
                $busClass->bus()->associate($bus);
            }
        });
    }

    public function business(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Bussiness',
            ];
        });
    }

    public function economy(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Economy',
            ];
        });
    }

    public function vip(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'VIP',
            ];
        });
    }
}
