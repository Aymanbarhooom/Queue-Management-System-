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
        //6 managers
        for($i=0; $i<6; $i++){
            User::create([
                'name' => 'Manager '.$i,
                'email' => 'manager'.$i.'@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'image' => 'profile-images/user.jpg',
            ]);
        }
    }
}