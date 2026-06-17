<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DoctorController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock login doctor
$doctorUser = User::where('role', 'doctor')->first();
Auth::login($doctorUser);

$controller = new DoctorController();
try {
    $response = $controller->getConsultationDetails(1);
    echo "SUCCESS:\n";
    print_r(json_decode($response->getContent(), true));
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
