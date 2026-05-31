<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Queue;
use App\Models\Business;

class ServicSeeder extends Seeder
{
    public function run()
    {
       $business = Business::all();
       foreach ($business as $b) {
        $service = Service::create([
            'business_id' => $b->id,
            'name' => "Service $b->id",
            'description' => "Description for Service $b->id",
            'price' => rand(100, 1000),
            'base_duration' => rand(1, 10),
        ]);
       }
    }
}