<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doctors = App\Models\Doctor::all();
foreach ($doctors as $doctor) {
    if ($doctor->practice_start_time && $doctor->practice_end_time) {
        $start = \Carbon\Carbon::parse($doctor->practice_start_time);
        $end = \Carbon\Carbon::parse($doctor->practice_end_time);
        if ($end->lessThan($start)) {
            $end->addDay();
        }
        $diffInMinutes = $start->diffInMinutes($end);
        $hours = $diffInMinutes / 60;
        $doctor->patient_quota = floor($hours * 3);
        $doctor->save();
        echo "Doctor {$doctor->id} quota updated to {$doctor->patient_quota}\n";
    }
}
