<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing admin requests with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AdminRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();

        $admins = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->get();

        $requestTypes = [
            'leave' => 'طلب إجازة',
            'certificate_request' => 'طلب شهادة دراسية',
            'group_transfer' => 'طلب نقل مجموعة',
            'course_withdrawal' => 'طلب انسحاب من مقرر',
            'absence_excuse' => 'طلب عذر غياب',
            'transcript' => 'طلب كشف درجات',
            'other' => 'طلب آخر'
        ];

        $priorities = ['low', 'normal', 'high', 'urgent'];
        $statuses = ['pending', 'approved', 'rejected'];

        $requestDetails = [
            'leave' => [
                'أطلب إجازة لمدة أسبوع لظروف عائلية طارئة',
                'أحتاج إجازة مرضية لمدة ثلاثة أيام',
                'طلب إجازة لحضور مؤتمر علمي',
                'إجازة لأداء فريضة الحج'
            ],
            'certificate_request' => [
                'أحتاج شهادة تخرج للتقديم على وظيفة',
                'طلب شهادة قيد للحصول على خصم طلابي',
                'شهادة درجات للتحويل لجامعة أخرى',
                'شهادة حسن سير وسلوك'
            ],
            'group_transfer' => [
                'أرغب في النقل لمجموعة أخرى لتناسب ظروف عملي',
                'طلب نقل لمجموعة صباحية',
                'النقل لمجموعة مسائية لظروف شخصية',
                'تغيير المجموعة لتحسين الأداء الأكاديمي'
            ],
            'course_withdrawal' => [
                'أرغب في الانسحاب من مقرر الرياضيات لصعوبته',
                'انسحاب من مقرر اختياري لتقليل العبء الدراسي',
                'طلب انسحاب لظروف صحية',
                'الانسحاب من المقرر لإعادة تسجيله في فصل آخر'
            ],
            'absence_excuse' => [
                'عذر غياب لمدة يومين بسبب مرض',
                'غياب لحضور جنازة أحد الأقارب',
                'عذر غياب لظروف عائلية طارئة',
                'غياب بسبب حادث مروري'
            ],
            'transcript' => [
                'أحتاج كشف درجات للتقديم على منحة دراسية',
                'كشف درجات للتحويل لكلية أخرى',
                'طلب كشف درجات للتقديم على وظيفة',
                'كشف درجات لاستكمال إجراءات التخرج'
            ],
            'other' => [
                'طلب تأجيل امتحان لظروف خاصة',
                'استفسار عن إجراءات التخرج',
                'طلب تعديل بيانات شخصية',
                'شكوى من أحد المقررات'
            ]
        ];

        foreach ($students as $student) {
            // Create 1-3 requests per student
            $requestCount = rand(1, 3);
            
            for ($i = 0; $i < $requestCount; $i++) {
                $type = array_rand($requestTypes);
                $priority = $priorities[array_rand($priorities)];
                $status = $statuses[array_rand($statuses)];
                $details = $requestDetails[$type][array_rand($requestDetails[$type])];
                
                $requestDate = Carbon::now()->subDays(rand(1, 90));
                
                $adminRequest = AdminRequest::create([
                    'user_id' => $student->id,
                    'type' => $type,
                    'details' => $details,
                    'priority' => $priority,
                    'request_date' => $requestDate,
                    'status' => $status,
                    'admin_comment' => $status !== 'pending' ? $this->getAdminComment($status) : null,
                    'admin_id' => $status !== 'pending' ? $admins->random()->id : null,
                    'attachment' => rand(1, 5) == 1 ? 'documents/request_' . uniqid() . '.pdf' : null,
                    'created_at' => $requestDate,
                    'updated_at' => $status !== 'pending' ? $requestDate->copy()->addDays(rand(1, 7)) : $requestDate,
                ]);
            }
        }

        // Create some urgent pending requests for testing
        $urgentStudents = $students->random(3);
        foreach ($urgentStudents as $student) {
            AdminRequest::create([
                'user_id' => $student->id,
                'type' => 'absence_excuse',
                'details' => 'طلب عذر غياب عاجل لظروف صحية طارئة',
                'priority' => 'urgent',
                'request_date' => Carbon::now()->subDays(1),
                'status' => 'pending',
                'admin_comment' => null,
                'admin_id' => null,
                'attachment' => 'documents/medical_report_' . uniqid() . '.pdf',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ]);
        }

        $this->command->info('تم إنشاء الطلبات الإدارية بنجاح');
    }

    private function getAdminComment($status)
    {
        $comments = [
            'approved' => [
                'تم الموافقة على الطلب',
                'طلب مقبول ومبرر',
                'تمت الموافقة بعد مراجعة الوثائق',
                'طلب مقبول وفقاً للوائح',
                'تم قبول الطلب لوجود مبرر قوي'
            ],
            'rejected' => [
                'تم رفض الطلب لعدم توفر المبررات الكافية',
                'الطلب مرفوض لمخالفته اللوائح',
                'لا يمكن الموافقة على الطلب في الوقت الحالي',
                'طلب مرفوض لعدم اكتمال الوثائق المطلوبة',
                'الطلب غير مقبول وفقاً للسياسات المعمول بها'
            ]
        ];

        $statusComments = $comments[$status] ?? ['تم معالجة الطلب'];
        return $statusComments[array_rand($statusComments)];
    }
} 