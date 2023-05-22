<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Trip $trip) {
            if (!$trip->bus_id) {
                $bus = Bus::factory()->create();
                $trip->bus()->associate($bus);
                $trip->available_seats = $bus->total_seats;
                $trip->save();
            }

            if (!$trip->route_id) {
                $route = Route::factory()->create();
                $trip->route()->associate($route);
            }
        });
    }

    public function definition(): array
    {
        return [
            'trip_time' => Carbon::now(),
            'available_seats' => rand(1, 50),
        ];
    }
}
