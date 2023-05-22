<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Seeder;

class RoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = Route::factory()->count(10)->create();
        $routes->each(function (Route $route) {
            RouteStop::factory()->count(5)->for($route)->create();
        });
    }
}
