<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_dashboard_returns_grouped_business_data(): void
    {
        $manager = User::create([
            'name' => 'Manager One',
            'email' => 'manager@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
        ]);
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Category for tests',
        ]);

        $businessOne = Business::create([
            'user_id' => $manager->id,
            'category_id' => $category->id,
            'name' => 'Business One',
            'description' => 'First business',
            'longitude' => 12.3456789,
            'latitude' => 98.7654321,
            'phone' => '1234567890',
            'image' => null,
        ]);

        $businessTwo = Business::create([
            'user_id' => $manager->id,
            'category_id' => $category->id,
            'name' => 'Business Two',
            'description' => 'Second business',
            'longitude' => 11.1111111,
            'latitude' => 22.2222222,
            'phone' => '0987654321',
            'image' => null,
        ]);

        $response = $this->actingAs($manager)->getJson('/api/manager/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.all_businesses', 2)
            ->assertJsonCount(2, 'data.all_data.businesses')
            ->assertJsonPath('data.all_data.businesses.0.business_id', $businessOne->id)
            ->assertJsonPath('data.all_data.businesses.1.business_id', $businessTwo->id);
    }
}
