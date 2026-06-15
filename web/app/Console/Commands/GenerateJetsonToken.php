<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateJetsonToken extends Command
{
    protected $signature = 'jetson:generate-token {device_name}';
    protected $description = 'Generate a Sanctum API token for the Nvidia Jetson device';

    public function handle()
    {
        $deviceName = $this->argument('device_name');

        // Asosiasikan token dengan user admin pertama
        $user = User::where('role', 'admin')->first();

        if (!$user) {
            $this->error('Admin user not found. Please seed the database first.');
            return 1;
        }

        // Generate token menggunakan Sanctum
        $tokenResult = $user->createToken($deviceName);

        $this->info("Sanctum token generated successfully for device: {$deviceName}");
        $this->line("Token: {$tokenResult->plainTextToken}");
        $this->line("Please copy this token and configure it in your Jetson client application.");

        return 0;
    }
}
