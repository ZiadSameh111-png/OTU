<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = App\Models\Role::all();

echo "الأدوار المسجلة في النظام:\n";
echo "------------------------\n";

if ($roles->count() > 0) {
    foreach ($roles as $role) {
        echo "ID: {$role->id} | الاسم: {$role->name}\n";
    }
} else {
    echo "لا توجد أدوار مسجلة في النظام.\n";
}

echo "\nعدد الأدوار: " . $roles->count() . "\n"; 