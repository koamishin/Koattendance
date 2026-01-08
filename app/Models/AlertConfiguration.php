<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'alert_type',
        'condition',
        'notification_channels',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'condition' => 'array',
        'notification_channels' => 'array',
        'is_active' => 'boolean',
    ];

    public function alertLogs()
    {
        return $this->hasMany(AlertLog::class);
    }
}
