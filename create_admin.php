<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// حذف المستخدم إذا كان موجودًا
$existingAdmin = User::where('email', 'admin@otu.edu')->first();
if ($existingAdmin) {
    $existingAdmin->roles()->detach();
    $existingAdmin->delete();
    echo "تم حذف المستخدم المدير القديم\n";
}

// إنشاء مستخدم جديد
$admin = User::create([
    'name' => 'مدير النظام',
    'email' => 'admin@otu.edu',
    'password' => Hash::make('password123'),
]);

// تعيين دور المدير
$adminRole = Role::where('name', 'Admin')->first();
if ($adminRole) {
    $admin->roles()->attach($adminRole);
    echo "تم إنشاء حساب المدير بنجاح!\n";
    echo "البريد الإلكتروني: admin@otu.edu\n";
    echo "كلمة المرور: password123\n";
} else {
    echo "خطأ: دور المدير (Admin) غير موجود في قاعدة البيانات!\n";
} 