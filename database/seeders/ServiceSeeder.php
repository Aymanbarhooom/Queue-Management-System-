<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use App\Models\Category; // تأكد من عمل import لموديل التصنيفات
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. إحضار التصنيفات من قاعدة البيانات
        $bankCategory = Category::where('name', 'بنوك')->first();
        $govCategory = Category::where('name', 'حكومي')->first();
        $clinicCategory = Category::where('name', 'عيادات')->first();

        // --- أولاً: خدمات قطاع البنوك (4 خدمات مخصصة) ---
        if ($bankCategory) {
            $bankServicesTemplate = [
                ['name' => 'فتح حساب مصرفي جديد', 'desc' => 'إجراءات فتح حساب جاري أو توفير وإصدار بطاقة الصراف الآلي.'],
                ['name' => 'إيداع وسحب نقدي', 'desc' => 'عمليات السحب والإيداع النقدي المباشر من خلال صناديق الفروع.'],
                ['name' => 'تحويل أموال محلي ودولي', 'desc' => 'إرسال واستقبال الحوالات المالية السريعة بين المحافظات أو دولياً.'],
                ['name' => 'تقديم طلب قرض أو تمويل', 'desc' => 'دراسة وتجهيز ملفات القروض الشخصية، السكنية، أو تمويل المشاريع.'],
            ];

            $bankBusinesses = Business::where('category_id', $bankCategory->id)->get();
            foreach ($bankBusinesses as $business) {
                foreach ($bankServicesTemplate as $serviceData) {
                    Service::create([
                        'business_id' => $business->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['desc'] . ' لدى ' . $business->name,
                        'price' => $faker->randomFloat(2, 5, 50), // رسوم بنكية منخفضة عادة
                        'base_duration' => $faker->numberBetween(10, 30),
                    ]);
                }
            }
        }

        // --- ثانياً: خدمات القطاع الحكومي (3 خدمات مخصصة) ---
        if ($govCategory) {
            $govServicesTemplate = [
                ['name' => 'إصدار وتجديد الوثائق الرسمية', 'desc' => 'معاملات استخراج الشهادات، البطاقات الشخصية، وجوازات السفر.'],
                ['name' => 'دفع الرسوم والمخالفات المترتبة', 'desc' => 'تسديد المخالفات المرورية، الفواتير المتأخرة، أو رسوم البناء.'],
                ['name' => 'تقديم وتصديق طلبات الاسترحام والشكاوى', 'desc' => 'استقبال الطلبات القانونية، الشكاوى الإدارية وتوثيقها رسمياً.'],
            ];

            $govBusinesses = Business::where('category_id', $govCategory->id)->get();
            foreach ($govBusinesses as $business) {
                foreach ($govServicesTemplate as $serviceData) {
                    Service::create([
                        'business_id' => $business->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['desc'] . ' في ' . $business->name,
                        'price' => $faker->randomFloat(2, 2, 20), // رسوم طوابع ومعاملات حكومية
                        'base_duration' => $faker->numberBetween(15, 45),
                    ]);
                }
            }
        }

        // --- ثالثاً: خدمات قطاع العيادات والمشافي (3 خدمات مخصصة) ---
        if ($clinicCategory) {
            $clinicServicesTemplate = [
                ['name' => 'معاينة وفحص طبي عام', 'desc' => 'استشارة طبية أولية، فحص السريري، وتشخيص الأعراض المرضية.'],
                ['name' => 'تحليل دم وفحوصات مخبرية', 'desc' => 'سحب العينات وإجراء التحاليل الشاملة في مختبر المستشفى.'],
                ['name' => 'إجراء عملية جراحية صغرى/كبرى', 'desc' => 'العمليات الجراحية المجدولة داخل غرف العمليات تحت إشراف متخصصين.'],
            ];

            $clinicBusinesses = Business::where('category_id', $clinicCategory->id)->get();
            foreach ($clinicBusinesses as $business) {
                foreach ($clinicServicesTemplate as $serviceData) {
                    Service::create([
                        'business_id' => $business->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['desc'] . ' بمركز ' . $business->name,
                        'price' => $faker->randomFloat(2, 50, 500), // أسعار طبية أعلى نسبياً
                        'base_duration' => $faker->numberBetween(20, 120), // العمليات قد تستغرق وقتاً أطول
                    ]);
                }
            }
        }
    }
}
