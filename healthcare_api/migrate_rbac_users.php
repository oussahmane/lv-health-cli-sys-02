<?php

use App\Models\User;
use App\Models\Role;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting RBAC Migration...\n";

$users = User::all();
foreach ($users as $user) {
    if ($user->role) {
        // Map current enum role to system-wide default role
        $user->assignRole($user->role);
        echo "Assigned '{$user->role}' role to user: {$user->email}\n";
    }
}

echo "Migration completed!\n";
