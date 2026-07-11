<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // تأكد من المسار الصحيح لنموذج Category الخاص بك
use App\Models\Business; // تأكد من المسار الصحيح لنموذج Business الخاص بك
use App\Models\User;     // تأكد من المسار الصحيح لنموذج User الخاص بك
use Faker\Factory as Faker;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $images = [
            'category_images/bank.jpg',
            'category_images/gov.jpg',
            'category_images/clinic.jpg',
            'category_images/education.jpg',
            'category_images/food.jpg',
            'category_images/fun.jpg',
        ];
        $faker = Faker::create('ar_SA'); // استخدام اللغة العربية لـ Faker لتوليد بيانات مناسبة

        $categories = Category::all();

   
        $managerIds = User::take(6)->pluck('id')->toArray();

        // التحقق إذا كان هناك عدد كافٍ من المدراء
        if (empty($managerIds) || count($managerIds) < 6) {
            $this->command->warn('لم يتم العثور على 6 مدراء. يرجى التأكد من وجود 6 مستخدمين على الأقل في جدول users الخاص بك.');
            return;
        }

        $managerIndex = 0; // مؤشر لتتبع المدير الحالي
        foreach ($categories as $category) {
            for ($i = 0; $i < 4; $i++) { 
                Business::create([
                    'user_id' => $managerIds[$managerIndex],
                    'category_id' => $category->id,
                    'name' => $category->name . ' - ' . 'مؤسسة ' . ($i + 1),
                    'description' => $faker->sentence(10, true) . ' ' . $category->name . ' ' . 'مؤسسة ' . ($i + 1),
                    'longitude' => $faker->longitude($min = 34, $max = 55),
                    'latitude' => $faker->latitude($min = 16, $max = 32),
                    'phone' => $faker->phoneNumber,
                    'image' => $images[$category->id - 1], 
                    'avg_rating' => $faker->randomFloat(1, 1, 5),
                ]);

                //move to the next manager
                $managerIndex = ($managerIndex + 1) % count($managerIds);
            }
        }
    }
}