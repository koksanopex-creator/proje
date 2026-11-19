<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'event_key', 'event_title', 'category', 'bell_title_template',
        'bell_body_template', 'mail_subject_template', 'mail_body_template',
        'available_variables', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
