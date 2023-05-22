<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusClass;
use Illuminate\Database\Seeder;

class BusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buses = Bus::factory()->count(10)->create();
        $buses->each(function (Bus $bus) {
            BusClass::factory()->business()->for($bus)->create();
            BusClass::factory()->economy()->for($bus)->create();
            BusClass::factory()->vip()->for($bus)->create();
        });
    }
}
