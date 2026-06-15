<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Get the appropriate dashboard route based on user role
     */
    protected function getDashboardRoute()
    {
        $user = auth()->user();
        
        if (!$user) {
            return route('patient.dashboard'); // default
        }
        
        return match($user->role) {
            'admin' => route('admin.dashboard'),
            'doctor' => route('doctor.dashboard'),
            'patient' => route('patient.dashboard'),
            default => route('patient.dashboard'),
        };
    }
}
