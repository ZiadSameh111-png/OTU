<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;

// إضافة دور المعلم إذا لم يكن موجودًا
$teacherRole = Role::where('name', 'Teacher')->first();
if (!$teacherRole) {
    $teacherRole = Role::create(['name' => 'Teacher', 'description' => 'دور المعلم في النظام']);
    echo "تم إضافة دور المعلم (Teacher) بنجاح.\n";
} else {
    echo "دور المعلم (Teacher) موجود بالفعل.\n";
}

// إضافة دور الطالب إذا لم يكن موجودًا
$studentRole = Role::where('name', 'Student')->first();
if (!$studentRole) {
    $studentRole = Role::create(['name' => 'Student', 'description' => 'دور الطالب في النظام']);
    echo "تم إضافة دور الطالب (Student) بنجاح.\n";
} else {
    echo "دور الطالب (Student) موجود بالفعل.\n";
}

// عرض جميع الأدوار بعد التحديث
$roles = Role::all();
echo "\nالأدوار المسجلة في النظام بعد التحديث:\n";
echo "-----------------------------------\n";

foreach ($roles as $role) {
    echo "ID: {$role->id} | الاسم: {$role->name}\n";
}

echo "\nعدد الأدوار: " . $roles->count() . "\n"; 