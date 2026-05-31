<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Business;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $businesses = Business::all();
       for($i=0; $i<count($businesses); $i++){
           Service::create([
               'business_id' => $businesses[$i]->id,
               'name' => 'Service '.$i,
               'description' => 'Description of service '.$i,
               'price' => 100,
               'base_duration' => 10
           ]);
       }
    }
}