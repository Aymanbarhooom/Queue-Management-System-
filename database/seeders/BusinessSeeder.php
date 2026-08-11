<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // تأكد من المسار الصحيح لنموذج Category الخاص بك
use App\Models\Business; // تأكد من المسار الصحيح لنموذج Business الخاص بك
use App\Models\BusinessWorkingTime;
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


        $bankCategory = Category::where('name', 'بنوك')->first();
        $govCategory = Category::where('name', 'حكومي')->first();
        $clinicCategory = Category::where('name', 'عيادات')->first();
        $managers = User::where('role', 'manager')->get()->take(3);
        $bankmanager = $managers->first();
        $govmanager = $managers->last();
        $clinicmanager = $managers->skip(1)->first();


        //bank businesses
        $bankBusinesses = [
            [
                'user_id' => $bankmanager->id,
                'name' => 'المصرف التجاري السوري',
                'description' => 'المصرف التجاري السوري هو أكبر مصرف حكومي في سوريا، يقدم الخدمات المصرفية التجارية والمالية وتمويل المشاريع للأفراد والشركات.',
                'image' => 'category_images/bank.jpg',
                'category_id' => $bankCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.5,
                'phone' => '03123456789'
            ],
            [
                'user_id' => $bankmanager->id,
                'name' => 'بنك بيمو السعودي الفرنسي',
                'description' => 'بنك بيمو السعودي الفرنسي هو أول مصرف خاص تأسس في سوريا، ويقدم باقة متكاملة من الخدمات المصرفية والحلول التمويلية المبتكرة.',
                'image' => 'category_images/bank.jpg',
                'category_id' => $bankCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 3.5,
                'phone' => '03123456789'
            ],
            [
                'user_id' => $bankmanager->id,
                'name' => 'بنك البركة سوريا',
                'description' => 'بنك البركة سوريا هو مصرف إسلامي رائد يقدم خدمات مصرفية واستثمارية متوافقة مع أحكام الشريعة الإسلامية لتلبية احتياجات السوق السورية.',
                'image' => 'category_images/bank.jpg',
                'category_id' => $bankCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.9,
                'phone' => '03123456789'
            ],
            [
                'user_id' => $bankmanager->id,
                'name' => 'بنك سورية الدولي الإسلامي',
                'description' => 'بنك سورية الدولي الإسلامي يقدم شبكة واسعة من الخدمات المالية الإسلامية والحلول الرقمية للأفراد والشركات في مختلف المحافظات السورية.',
                'image' => 'category_images/bank.jpg',
                'category_id' => $bankCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.1,
                'phone' => '03123456789'
            ],
            [
                'user_id' => $bankmanager->id,
                'name' => 'بنك الشام',
                'description' => 'بنك الشام هو أول مصرف إسلامي تأسس في سوريا، ويوفر مجموعة متطورة من المنتجات المصرفية الاستثمارية والتجارية المتوافقة مع الشريعة.',
                'image' => 'category_images/bank.jpg',
                'category_id' => $bankCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 3.9,
                'phone' => '03123456789'
            ],
        ];
        foreach ($bankBusinesses as $business) {
            Business::create($business);
        }
        //gov businesses
        $govBusinesses = [
            [
                'user_id' => $govmanager->id,
                'name' => 'وزارة الاتصالات وتقانة المعلومات',
                'description' => 'الجهة الحكومية المسؤولة عن رسم السياسات والاستراتيجيات لقطاعات الاتصالات والبريد، وتطوير تكنولوجيا المعلومات والتحول الرقمي في سوريا.',
                'image' => 'category_images/gov.jpg',
                'category_id' => $govCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.2,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $govmanager->id,
                'name' => 'المؤسسة العامة للبريد',
                'description' => 'المؤسسة الوطنية المسؤولة عن تقديم الخدمات البريدية التقليدية والمالية، بالإضافة إلى خدمات السجل المدني والعدلي والوثائق الحكومية الإلكترونية.',
                'image' =>  'category_images/gov.jpg',
                'category_id' => $govCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 3.8,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $govmanager->id,
                'name' => 'المديرية العامة للآثار والمتاحف',
                'description' => 'الهيئة الحكومية المنوط بها حماية، صيانة، وترميم الأماكن الأثرية والمباني التاريخية وإدارة المتاحف الوطنية في مختلف المحافظات السورية.',
                'image' =>  'category_images/gov.jpg',
                'category_id' => $govCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.7,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $govmanager->id,
                'name' => 'المؤسسة العامة للتأمينات الاجتماعية',
                'description' => 'المؤسسة الخدمية المسؤولة عن تأمين الرعاية الاجتماعية للمشتركين، وتوفير معاشات الشيخوخة، العجز، الوفاة، وإصابات العمل للعاملين.',
                'image' =>  'category_images/gov.jpg',
                'category_id' => $govCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 3.5,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $govmanager->id,
                'name' => 'المؤسسة العامة للمعارض والأسواق الدولية',
                'description' => 'الجهة الرسمية المسؤولة عن تنظيم المعارض التجارية والاقتصادية داخل سوريا وخارجها، وإدارة معرض دمشق الدولي السنوي ونشاطات اليانصيب.',
                'image' => 'category_images/gov.jpg',
                'category_id' => $govCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.0,
                'phone' => '0112345678'
            ],
        ];
        foreach ($govBusinesses as $business) {
            Business::create($business);
        }

        //clinic businesses
        $clinicBusinesses = [
            [
                'user_id' => $clinicmanager->id,
                'name' => 'مستشفى دمشق (المجتهد)',
                'description' => 'من أقدم وأكبر المستشفيات الحكومية والتعليمية في سوريا، يقدم خدمات طبية وإسعافية متكاملة في كافة التخصصات الطبية على مدار الساعة.',
                'image' => 'category_images/clinic.jpg',
                'category_id' => $clinicCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.2,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $clinicmanager->id,
                'name' => 'المشفى الوطني الجامعي',
                'description' => 'مستشفى تعليمي وطبي رائد في دمشق، يضم أحدث التجهيزات الطبية ويقدم خدمات تشخيصية وعلاجية متقدمة وجراحات نوعية معقدة.',
                'image' => 'category_images/clinic.jpg',
                'category_id' => $clinicCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.6,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $clinicmanager->id,
                'name' => 'مستشفى المواساة الجامعي',
                'description' => 'صرح طبي وتعليمي ضخم يتبع لجامعة دمشق، يحتوي على أقسام تخصصية واسعة وعيادات خارجية تستقبل المرضى من كافة المحافظات.',
                'image' => 'category_images/clinic.jpg',
                'category_id' => $clinicCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.0,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $clinicmanager->id,
                'name' => 'مستشفى  لأمراض وجراحة القلب',
                'description' => 'مركز طبي تخصصي متطور رائد في تقديم الرعاية الطبية لمرضى القلب، وإجراء عمليات القسطرة وجراحات القلب المفتوح المعقدة.',
                'image' => 'category_images/clinic.jpg',
                'category_id' => $clinicCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.7,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $clinicmanager->id,
                'name' => 'مستشفى الأطفال الجامعي',
                'description' => 'المستشفى التخصصي الحكومي الوحيد في سوريا المكرس بالكامل لتقديم الرعاية الطبية الشاملة والجراحية للأطفال والخدج.',
                'image' => 'category_images/clinic.jpg',
                'category_id' => $clinicCategory->id,
                'longitude' => 35.2270,
                'latitude' => 31.7940,
                'avg_rating' => 4.3,
                'phone' => '0112345678'
            ],
        ];
        foreach ($clinicBusinesses as $business) {
            Business::create($business);
        }
        //working times
        $businesses = Business::all();
        $workingDays = [
            'Monday'    => false,
            'Tuesday'   => false,
            'Wednesday' => false,
            'Thursday'  => false,
            'Friday'    => false,
            'Saturday'  => true,   // Closed
            'Sunday'    => true    // Closed
        ];

        foreach ($businesses as $business) {
            foreach ($workingDays as $day => $isClosed) {
                \App\Models\BusinessWorkingTime::create([
                    'business_id' => $business->id,
                    'day_of_week' => $day,
                    'open_hour'   => $isClosed ? null : '08:00',
                    'close_hour'  => $isClosed ? null : '16:00',
                    'is_closed'   => $isClosed,
                ]);
            }
        }
    }
}
