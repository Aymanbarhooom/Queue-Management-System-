<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use App\Models\BusinessWorkingTime;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
class UserSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 9; $i++) {
         User::create([
         'name' => "Manager $i",
            'email' => "manager$i@email.com",
            'password' => Hash::make("manager$i"),
            'role' => 'manager',
            'business_id' => null,
        ]);
    }                    
        $categories = Category::take(3)->get(); 
        $managers = User::where('role', 'manager')->get();

        foreach ($managers as $manager) {
            foreach ($categories as $category) {
                if ($i > 18) break;
                     Business::create([
                     'user_id' => $manager->id,
                    'category_id' => $category->id,
                    'name' => "Business $i",
                    'description' => "Description for Business $i",
                    'longitude' => rand(-18000, 18000) / 1000,
                    'latitude' => rand(-9000, 9000) / 1000,
                    'phone' => '123-456-7890',
                    'image' => null,
                    'avg_rating' => rand(3, 5),
                ]);
                $i++;
            }
        }
        $businessHours = [
            ['day_of_week' => 'Sunday',    'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Monday',    'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Tuesday',   'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Wednesday', 'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Thursday',  'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Friday',    'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
            ['day_of_week' => 'Saturday',  'open_hour' => '08:00', 'close_hour' => '16:00', 'is_closed' => false],
        ];
        $businesses = Business::all();
        foreach ($businesses as $business) {
                User::create([
                    'name' => "Employee $business->id",
                    'email' => "employee$business->id@email.com",
                    'password' => Hash::make("employee$business->id"),
                    'role' => 'employee',
                    'business_id' => $business->id,
                ]);
                
               foreach($businessHours as $hours){
                $hours['business_id'] = $business->id;
                BusinessWorkingTime::create($hours);
               }
        }
     }
}