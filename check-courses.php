<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$courses = App\Models\Course::all();

echo "Courses in the database:\n";
echo "------------------------\n";

foreach ($courses as $course) {
    echo "ID: {$course->id} | Name: {$course->name} | Code: {$course->code}\n";
}

echo "\nTotal courses: " . $courses->count() . "\n"; 