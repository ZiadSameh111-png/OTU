<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing notifications with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Notification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $users = User::all();
        $admins = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->get();

        // System notifications for all users
        $systemNotifications = [
            [
                'title' => 'تحديث النظام',
                'description' => 'تم تحديث النظام بنجاح. يرجى تسجيل الخروج وإعادة تسجيل الدخول.',
                'notification_type' => 'general'
            ],
            [
                'title' => 'صيانة مجدولة',
                'description' => 'سيتم إجراء صيانة للنظام يوم الجمعة من الساعة 12 ظهراً حتى 2 ظهراً.',
                'notification_type' => 'announcement'
            ],
            [
                'title' => 'تحديث كلمة المرور',
                'description' => 'يُنصح بتحديث كلمة المرور الخاصة بك لضمان أمان الحساب.',
                'notification_type' => 'general'
            ]
        ];

        // Academic notifications
        $academicNotifications = [
            [
                'title' => 'بداية الفصل الدراسي',
                'description' => 'يبدأ الفصل الدراسي الجديد يوم الأحد القادم. يرجى مراجعة الجدول الدراسي.',
                'notification_type' => 'academic'
            ],
            [
                'title' => 'موعد التسجيل',
                'description' => 'ينتهي موعد التسجيل للمقررات الاختيارية خلال أسبوع.',
                'notification_type' => 'academic'
            ],
            [
                'title' => 'إعلان النتائج',
                'description' => 'تم إعلان نتائج امتحانات منتصف الفصل. يمكنكم الاطلاع عليها الآن.',
                'notification_type' => 'academic'
            ]
        ];

        // Exam notifications
        $examNotifications = [
            [
                'title' => 'موعد الامتحان',
                'description' => 'تذكير: امتحان مقرر البرمجة غداً الساعة 10 صباحاً.',
                'notification_type' => 'exam'
            ],
            [
                'title' => 'نتائج الامتحان',
                'description' => 'تم نشر نتائج امتحان منتصف الفصل.',
                'notification_type' => 'exam'
            ]
        ];

        // Create notifications for all users
        foreach ($users as $user) {
            // System notifications (for everyone)
            foreach ($systemNotifications as $notification) {
                Notification::create([
                    'title' => $notification['title'],
                    'description' => $notification['description'],
                    'sender_id' => $admins->random()->id,
                    'receiver_id' => $user->id,
                    'receiver_type' => 'user',
                    'notification_type' => $notification['notification_type'],
                    'read_at' => rand(1, 3) == 1 ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }

            // Role-specific notifications
            if ($user->hasRole('Student')) {
                // Academic notifications for students
                foreach ($academicNotifications as $notification) {
                    Notification::create([
                        'title' => $notification['title'],
                        'description' => $notification['description'],
                        'sender_id' => $admins->random()->id,
                        'receiver_id' => $user->id,
                        'receiver_type' => 'user',
                        'notification_type' => $notification['notification_type'],
                        'read_at' => rand(1, 2) == 1 ? Carbon::now()->subDays(rand(1, 3)) : null,
                        'created_at' => Carbon::now()->subDays(rand(1, 20)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 20)),
                    ]);
                }

                // Exam notifications for students
                foreach ($examNotifications as $notification) {
                    Notification::create([
                        'title' => $notification['title'],
                        'description' => $notification['description'],
                        'sender_id' => $admins->random()->id,
                        'receiver_id' => $user->id,
                        'receiver_type' => 'user',
                        'notification_type' => $notification['notification_type'],
                        'read_at' => rand(1, 2) == 1 ? Carbon::now()->subHours(rand(1, 12)) : null,
                        'created_at' => Carbon::now()->subDays(rand(1, 10)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 10)),
                    ]);
                }
            }

            if ($user->hasRole('Teacher')) {
                // Teacher-specific notifications
                $teacherNotifications = [
                    [
                        'title' => 'تحديث الدرجات',
                        'description' => 'يرجى تحديث درجات الطلاب قبل نهاية الأسبوع.',
                        'notification_type' => 'academic'
                    ],
                    [
                        'title' => 'اجتماع هيئة التدريس',
                        'description' => 'اجتماع هيئة التدريس غداً الساعة 2 ظهراً في قاعة الاجتماعات.',
                        'notification_type' => 'announcement'
                    ]
                ];

                foreach ($teacherNotifications as $notification) {
                    Notification::create([
                        'title' => $notification['title'],
                        'description' => $notification['description'],
                        'sender_id' => $admins->random()->id,
                        'receiver_id' => $user->id,
                        'receiver_type' => 'user',
                        'notification_type' => $notification['notification_type'],
                        'read_at' => rand(1, 3) == 1 ? Carbon::now()->subDays(rand(1, 2)) : null,
                        'created_at' => Carbon::now()->subDays(rand(1, 7)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 7)),
                    ]);
                }
            }
        }

        // Create some recent unread urgent notifications
        $recentUsers = $users->random(5);
        foreach ($recentUsers as $user) {
            Notification::create([
                'title' => 'إشعار عاجل',
                'description' => 'يرجى مراجعة الإدارة في أقرب وقت ممكن.',
                'sender_id' => $admins->random()->id,
                'receiver_id' => $user->id,
                'receiver_type' => 'user',
                'notification_type' => 'announcement',
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(rand(1, 6)),
                'updated_at' => Carbon::now()->subHours(rand(1, 6)),
            ]);
        }

        $this->command->info('تم إنشاء الإشعارات بنجاح');
    }
} 