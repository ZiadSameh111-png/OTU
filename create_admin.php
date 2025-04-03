<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// Set admin credentials
$name = 'Admin User';
$email = 'admin@otu.edu';
$password = 'Admin@123';

// Create user if doesn't exist
$user = User::where('email', $email)->first();
if (!$user) {
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password)
    ]);
    echo "Admin user created successfully!\n";
} else {
    // Update password if user exists
    $user->password = Hash::make($password);
    $user->save();
    echo "Admin user already exists, password updated!\n";
}

// Ensure admin role exists
$adminRole = Role::where('name', 'Admin')->first();
if (!$adminRole) {
    $adminRole = Role::create(['name' => 'Admin']);
    echo "Admin role created!\n";
}

// Assign admin role to user
if (!$user->hasRole('Admin')) {
    $user->assignRole('Admin');
    echo "Admin role assigned to user!\n";
} else {
    echo "User already has Admin role!\n";
}

echo "\nAdmin Account Details:\n";
echo "Email: " . $email . "\n";
echo "Password: " . $password . "\n"; 