<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'penilai@demo.test')->first();

if (!$user) {
    echo "USER NOT FOUND\n";
} else {
    echo "FOUND: " . $user->name . "\n";
    echo "is_active: " . var_export($user->is_active, true) . "\n";
    echo "password check: " . var_export(Hash::check('password', $user->password), true) . "\n";
}
