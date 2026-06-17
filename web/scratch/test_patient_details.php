<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PatientController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock login patient
$patientUser = User::where('role', 'patient')->first();
Auth::login($patientUser);

$controller = new PatientController();
try {
    $response = $controller->getConsultationDetails(1);
    echo "SUCCESS:\n";
    print_r(json_decode($response->getContent(), true));
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
