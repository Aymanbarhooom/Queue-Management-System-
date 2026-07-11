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
        if (User::where('role', 'manager')->count() === 0) {
            for ($i = 0; $i < 6; $i++) {
                User::create([
                    'name' => 'Manager '.$i,
                    'email' => 'manager'.$i.'@gmail.com',
                    'password' => Hash::make('password123'),
                    'role' => 'manager',
                    'image' => 'profile_images/manager.jpg',
                ]);
            }
        }
        //4 users for testing
        for($i=0; $i<4; $i++){
            User::create([
                'name' => 'Test User '.$i,
                'email' => 'test'.$i.'@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'image' => 'profile_images/user.jpg',
            ]);
        }
    }
}