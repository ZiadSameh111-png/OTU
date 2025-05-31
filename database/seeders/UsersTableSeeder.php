<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing users (except admin)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_user')->whereNotIn('user_id', function($query) {
            $query->select('users.id')
                  ->from('users')
                  ->join('role_user', 'users.id', '=', 'role_user.user_id')
                  ->join('roles', 'role_user.role_id', '=', 'roles.id')
                  ->where('roles.name', 'Admin');
        })->delete();
        
        User::whereDoesntHave('roles', function($query) {
            $query->where('name', 'Admin');
        })->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Get roles
        $teacherRole = Role::where('name', 'Teacher')->first();
        $studentRole = Role::where('name', 'Student')->first();
        $groups = Group::where('active', true)->get();

        // Create Teachers
        $teacherNames = [
            'د. أحمد محمد علي',
            'د. فاطمة أحمد السيد',
            'د. محمد عبدالله حسن',
            'د. نورا سالم المطيري',
            'د. خالد عبدالعزيز النجار',
            'د. سارة محمد الزهراني',
            'د. عبدالرحمن صالح القحطاني',
            'د. ليلى أحمد البلوشي'
        ];

        $teacherEmails = [
            'ahmed.ali@otu.edu',
            'fatima.ahmed@otu.edu',
            'mohammed.hassan@otu.edu',
            'nora.salem@otu.edu',
            'khalid.najjar@otu.edu',
            'sara.zahrani@otu.edu',
            'abdulrahman.qahtani@otu.edu',
            'layla.balushi@otu.edu'
        ];

        for ($i = 0; $i < count($teacherNames); $i++) {
            $teacher = User::create([
                'name' => $teacherNames[$i],
                'email' => $teacherEmails[$i],
                'password' => Hash::make('password123'),
                'group_id' => null, // Teachers don't belong to student groups
            ]);

            $teacher->roles()->attach($teacherRole);
        }

        // Create Students
        $studentNames = [
            'علي أحمد محمد',
            'فاطمة سالم العتيبي',
            'محمد عبدالله الشمري',
            'نورا خالد المطيري',
            'عبدالعزيز صالح القحطاني',
            'سارة محمد الزهراني',
            'أحمد عبدالرحمن النجار',
            'ليلى سعد البلوشي',
            'خالد محمد العنزي',
            'هند عبدالله الدوسري',
            'سعد أحمد الغامدي',
            'رنا محمد الحربي',
            'عبدالرحمن علي السبيعي',
            'نوف سالم المالكي',
            'طارق عبدالعزيز الراشد',
            'دانا خالد الفيصل',
            'يوسف محمد العمري',
            'ريم أحمد الشهري',
            'عبدالله سعد الخالدي',
            'مريم علي الحمادي',
            'فهد عبدالرحمن السديري',
            'شهد محمد القرني',
            'بندر خالد العجمي',
            'رغد سالم الحارثي',
            'ماجد عبدالله الفهد'
        ];

        foreach ($studentNames as $index => $name) {
            $email = 'student' . ($index + 1) . '@otu.edu';
            $randomGroup = $groups->random();

            $student = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'group_id' => $randomGroup->id,
            ]);

            $student->roles()->attach($studentRole);
        }

        // Create some additional test users with simple credentials for easy testing
        $testUsers = [
            [
                'name' => 'Teacher Test',
                'email' => 'teacher@test.com',
                'role' => 'Teacher',
                'group_id' => null
            ],
            [
                'name' => 'Student Test',
                'email' => 'student@test.com',
                'role' => 'Student',
                'group_id' => $groups->first()->id
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'group_id' => $userData['group_id'],
            ]);

            $role = Role::where('name', $userData['role'])->first();
            $user->roles()->attach($role);
        }

        $this->command->info('تم إنشاء المستخدمين بنجاح!');
        $this->command->info('المدرسين: ' . count($teacherNames) + 1);
        $this->command->info('الطلاب: ' . count($studentNames) + 1);
    }
}
