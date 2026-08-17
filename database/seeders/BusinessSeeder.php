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
        $educationCategory = Category::where('name', 'تعليم')->first();
        $restaurantCategory = Category::where('name', 'مطاعم')->first();
        $entertainmentCategory = Category::where('name', 'ترفيه')->first();
        $managers = User::where('role', 'manager')->get()->take(6);
        $bankmanager = $managers->first();
        $govmanager = $managers->skip(1)->first();
        $clinicmanager = $managers->skip(2)->first();
        $educationmanager = $managers->skip(3)->first();
        $restaurantmanager = $managers->skip(4)->first();
        $entertainmentmanager = $managers->skip(5)->first();


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

        //education businesses
        $educationBusinesses = [
            [
                'user_id' => $educationmanager->id,
                'name' => 'جامعة دمشق',
                'description' => 'أقدم وأكبر جامعة حكومية في سوريا، تضم كليات متعددة في العلوم والهندسة والطب والآداب وتقدم برامج بكالوريوس وماجستير ودكتوراه.',
                'image' => 'category_images/education.jpg',
                'category_id' => $educationCategory->id,
                'longitude' => 36.2760,
                'latitude' => 33.5130,
                'avg_rating' => 4.4,
                'phone' => '0112345678'
            ],
            [
                'user_id' => $educationmanager->id,
                'name' => 'جامعة تشرين',
                'description' => 'جامعة حكومية رائدة في اللاذقية تقدم تعليماً أكاديمياً في التخصصات العلمية والإنسانية والطبية وتستقطب آلاف الطلاب سنوياً.',
                'image' => 'category_images/education.jpg',
                'category_id' => $educationCategory->id,
                'longitude' => 35.7890,
                'latitude' => 35.5310,
                'avg_rating' => 4.1,
                'phone' => '0412345678'
            ],
            [
                'user_id' => $educationmanager->id,
                'name' => 'المعهد العالي للعلوم التطبيقية والتكنولوجيا',
                'description' => 'معهد تعليمي متخصص يقدم برامج تقنية وتطبيقية في هندسة الحاسوب والإلكترونيات وشبكات المعلومات لإعداد كوادر تقنية مؤهلة.',
                'image' => 'category_images/education.jpg',
                'category_id' => $educationCategory->id,
                'longitude' => 36.2910,
                'latitude' => 33.5020,
                'avg_rating' => 4.0,
                'phone' => '0113456789'
            ],
            [
                'user_id' => $educationmanager->id,
                'name' => 'أكاديمية اللغات والترجمة',
                'description' => 'مركز تعليمي متخصص في تعليم اللغات الأجنبية والترجمة الفورية والتحريرية، يقدم دورات للأفراد والمؤسسات في دمشق.',
                'image' => 'category_images/education.jpg',
                'category_id' => $educationCategory->id,
                'longitude' => 36.2800,
                'latitude' => 33.5180,
                'avg_rating' => 4.3,
                'phone' => '0114567890'
            ],
            [
                'user_id' => $educationmanager->id,
                'name' => 'معهد التدريب المهني والتقني',
                'description' => 'مؤسسة تدريبية تقدم برامج مهنية وتقنية في الكهرباء والميكانيك والنجارة والخياطة لتمكين الشباب من دخول سوق العمل.',
                'image' => 'category_images/education.jpg',
                'category_id' => $educationCategory->id,
                'longitude' => 36.2650,
                'latitude' => 33.5080,
                'avg_rating' => 3.9,
                'phone' => '0115678901'
            ],
        ];
        foreach ($educationBusinesses as $business) {
            Business::create($business);
        }

        //restaurant businesses
        $restaurantBusinesses = [
            [
                'user_id' => $restaurantmanager->id,
                'name' => 'مطعم بيت جبري',
                'description' => 'من أشهر المطاعم السورية التراثية في دمشق، يقدم المشاوي والكباب والفتوش والأطباق الشامية الأصيلة في أجواء دمشقية تقليدية.',
                'image' => 'category_images/food.jpg',
                'category_id' => $restaurantCategory->id,
                'longitude' => 36.3040,
                'latitude' => 33.5100,
                'avg_rating' => 4.6,
                'phone' => '0116789012'
            ],
            [
                'user_id' => $restaurantmanager->id,
                'name' => 'مطعم النابلسي',
                'description' => 'مطعم شامي راقٍ يشتهر بكباب الكباب والحمص والمتبل والأطباق الدمشقية الفاخرة مع خدمة ضيافة مميزة.',
                'image' => 'category_images/food.jpg',
                'category_id' => $restaurantCategory->id,
                'longitude' => 36.2980,
                'latitude' => 33.5150,
                'avg_rating' => 4.5,
                'phone' => '0117890123'
            ],
            [
                'user_id' => $restaurantmanager->id,
                'name' => 'مطعم الشام',
                'description' => 'مطعم عائلي يقدم وجبات غداء وعشاء يومية تشمل المشاوي والمحاشي واليبرق والحلويات الشرقية في موقع مميز بدمشق.',
                'image' => 'category_images/food.jpg',
                'category_id' => $restaurantCategory->id,
                'longitude' => 36.2870,
                'latitude' => 33.5200,
                'avg_rating' => 4.2,
                'phone' => '0118901234'
            ],
            [
                'user_id' => $restaurantmanager->id,
                'name' => 'مطعم أبو شكري',
                'description' => 'مطعم تراثي في قلب دمشق القديمة يقدم الفول والحمص والفلافل والمناقيش والعصائر الطازجة على مدار اليوم.',
                'image' => 'category_images/food.jpg',
                'category_id' => $restaurantCategory->id,
                'longitude' => 36.3060,
                'latitude' => 33.5110,
                'avg_rating' => 4.4,
                'phone' => '0119012345'
            ],
            [
                'user_id' => $restaurantmanager->id,
                'name' => 'مطعم الساحة',
                'description' => 'مطعم عصري يجمع بين المطبخ السوري التقليدي والأطباق العالمية، مع تراس خارجي وإطلالة على ساحة دمشق.',
                'image' => 'category_images/food.jpg',
                'category_id' => $restaurantCategory->id,
                'longitude' => 36.2930,
                'latitude' => 33.5170,
                'avg_rating' => 4.0,
                'phone' => '0110123456'
            ],
        ];
        foreach ($restaurantBusinesses as $business) {
            Business::create($business);
        }

        //entertainment businesses
        $entertainmentBusinesses = [
            [
                'user_id' => $entertainmentmanager->id,
                'name' => 'سينما دمشق',
                'description' => 'أقدم دور عرض الأفلام في سوريا، تعرض أحدث الأفلام العربية والعالمية في قاعات مجهزة بأحدث تقنيات العرض والصوت.',
                'image' => 'category_images/fun.jpg',
                'category_id' => $entertainmentCategory->id,
                'longitude' => 36.2950,
                'latitude' => 33.5120,
                'avg_rating' => 4.3,
                'phone' => '0111234567'
            ],
            [
                'user_id' => $entertainmentmanager->id,
                'name' => 'ملعب العباسيين',
                'description' => 'الملعب الرياضي الرئيسي في دمشق، يستضيف مباريات كرة القدم والفعاليات الرياضية الكبرى ويتسع لآلاف المشجعين.',
                'image' => 'category_images/fun.jpg',
                'category_id' => $entertainmentCategory->id,
                'longitude' => 36.2500,
                'latitude' => 33.5050,
                'avg_rating' => 4.1,
                'phone' => '0112345670'
            ],
            [
                'user_id' => $entertainmentmanager->id,
                'name' => 'حديقة تشرين',
                'description' => 'حديقة عامة واسعة في دمشق تضم مساحات خضراء وملاعب أطفال ومسارات للمشي، وجهة مثالية للعائلات في عطلات نهاية الأسبوع.',
                'image' => 'category_images/fun.jpg',
                'category_id' => $entertainmentCategory->id,
                'longitude' => 36.2700,
                'latitude' => 33.5300,
                'avg_rating' => 4.5,
                'phone' => '0113456701'
            ],
            [
                'user_id' => $entertainmentmanager->id,
                'name' => 'مركز الأموي للثقافة والفنون',
                'description' => 'مركز ثقافي يستضيف معارض فنية وحفلات موسيقية وعروض مسرحية وورشاً إبداعية للأطفال والشباب في قلب دمشق.',
                'image' => 'category_images/fun.jpg',
                'category_id' => $entertainmentCategory->id,
                'longitude' => 36.3080,
                'latitude' => 33.5130,
                'avg_rating' => 4.6,
                'phone' => '0114567012'
            ],
            [
                'user_id' => $entertainmentmanager->id,
                'name' => 'مدينة الألعاب المائية',
                'description' => 'منتجع ترفيهي عائلي يضم مسابح ومنزلقات مائية ومناطق لعب للأطفال، وجهة مثالية للترفيه في فصل الصيف.',
                'image' => 'category_images/fun.jpg',
                'category_id' => $entertainmentCategory->id,
                'longitude' => 36.3200,
                'latitude' => 33.5400,
                'avg_rating' => 4.2,
                'phone' => '0115670123'
            ],
        ];
        foreach ($entertainmentBusinesses as $business) {
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
