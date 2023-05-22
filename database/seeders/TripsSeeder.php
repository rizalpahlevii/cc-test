<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripsSeeder extends Seeder
{
    private const TRIPS_COUNT = 100;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < self::TRIPS_COUNT; $i++) {
            Trip::factory()
                ->for(Bus::inRandomOrder()->first())
                ->for(Route::inRandomOrder()->first())
                ->create([
                    'trip_time' => Carbon::now()->addDays($i)->addHours(rand(1, 24))->addMinutes(rand(1, 60)),
                ]);
        }
    }
}
