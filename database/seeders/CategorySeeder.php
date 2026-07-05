<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
         ['name' => 'بنوك', 'description' => 'Food and dining', 'image' => 'category_images/bank.jpg'],
         ['name' => 'حكومي', 'description' => 'Apparel and accessories', 'image' => 'category_images/gov.jpg'],
         ['name' => 'عيادات', 'description' => 'Gadgets and devices', 'image' => 'category_images/clinic.jpg'],
         ['name' => 'تعليم', 'description' => 'Healthcare and wellness', 'image' => 'category_images/education.jpg'],
         ['name' => 'مطاعم', 'description' => 'Learning and training', 'image' => 'category_images/food.jpg'],
         ['name' => 'ترفيه', 'description' => 'Movies, games, etc.', 'image' => 'category_images/fun.jpg'],
         ];
        
         foreach($categories as $category) {
               Category::create($category);
        }
    }
}
