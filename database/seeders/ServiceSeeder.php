<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        //Three services per business
        $businesses = Business::all();
        foreach ($businesses as $business) {
            for ($i = 0; $i < 3; $i++) {
                Service::create([
                    'business_id' => $business->id,
                    'name' => $business->name.' Service '.$i,
                    'description' => $business->description.' Service '.$i,
                    'price' => $faker->randomFloat(2, 10, 100),
                    'base_duration' => $faker->numberBetween(15, 60),
                ]);
            }
        }
    }
}
