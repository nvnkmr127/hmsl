<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = Illuminate\Support\Facades\DB::table('users')->get(['id','email','sync_id']);
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->email . ' | sync_id=' . $u->sync_id . "\n";
}
echo "Total users: " . count($users) . "\n";

// Check roles
echo "\nRoles in DB:\n";
$roles = Illuminate\Support\Facades\DB::table('roles')->get(['id','name']);
foreach ($roles as $r) {
    echo "  " . $r->id . ' => ' . $r->name . "\n";
}

echo "\nUser-role assignments:\n";
$mur = Illuminate\Support\Facades\DB::table('model_has_roles')->get();
foreach ($mur as $mr) {
    echo "  user_id=" . $mr->model_id . " role_id=" . $mr->role_id . "\n";
}
