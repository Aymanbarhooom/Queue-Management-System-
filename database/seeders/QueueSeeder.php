<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $employees = User::where('role', 'employee')->get();

        $serviceByBusiness = Service::all()
            ->groupBy('business_id')
            ->map(fn ($services) => $services->first());

        foreach ($employees as $employee) {
            $service = $serviceByBusiness->get($employee->business_id);

            if (!$service) {
                continue;
            }

            Queue::create([
                'user_id' => $employee->id,
                'service_id' => $service->id,
                'name' => 'Queue - '.$employee->name,
                'description' => $faker->sentence(),
                'status' => 'active',
                'type' => 'main',
                'congestion' => $faker->randomElement(['low', 'medium', 'high']),
            ]);
        }
    }
}
