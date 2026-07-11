<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::all();
        foreach ($businesses as $business) {
            if (User::where('role', 'employee')->where('business_id', $business->id)->exists()) {
                continue;
            }

            User::create([
                'name' => 'Employee '.$business->name,
                'email' => 'employee'.$business->id.'@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'business_id' => $business->id,
                'image' => 'profile_images/user.jpg',
            ]);
        }
    }
}
