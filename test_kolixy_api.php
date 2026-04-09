<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Show all NOT NULL columns without defaults in admins table
$columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM admins");
echo "=== Required Admin fields (NOT NULL, no default) ===\n";
foreach ($columns as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra === '') {
        echo "  $col->Field ($col->Type)\n";
    }
}

// Show existing admin for reference
$admin = \App\Models\Admin::find(2);
echo "\n=== Admin ID 2 fields ===\n";
foreach ($admin->toArray() as $k => $v) {
    if ($v !== null) echo "  $k: $v\n";
}
