<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user associated with the activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk get latest logs
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope untuk filter by activity type
     */
    public function scopeByActivityType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope untuk get logs dari user specific
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
