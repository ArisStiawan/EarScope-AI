<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\ConsultationRequest::all() as $r) {
    echo "Request ID: {$r->id}, Doctor ID: " . ($r->doctor_id ?? 'NULL') . ", Doctor exists: " . ($r->doctor ? 'Yes' : 'No') . "\n";
}
