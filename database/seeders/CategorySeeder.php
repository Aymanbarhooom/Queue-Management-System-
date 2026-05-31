<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
         ['name' => 'Restaurants', 'description' => 'Food and dining', 'image' => null],
         ['name' => 'Clothing', 'description' => 'Apparel and accessories', 'image' => null],
         ['name' => 'Electronics', 'description' => 'Gadgets and devices', 'image' => null],
         ['name' => 'Health', 'description' => 'Healthcare and wellness', 'image' => null],
         ['name' => 'Education', 'description' => 'Learning and training', 'image' => null],
         ['name' => 'Entertainment', 'description' => 'Movies, games, etc.', 'image' => null],
         ];
        
         foreach($categories as $category) {
               Category::create($category);
        }
    }
}
