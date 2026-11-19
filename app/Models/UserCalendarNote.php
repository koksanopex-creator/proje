<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCalendarNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'note_date',
        'note_time',
        'color',
        'send_bell',
        'send_email',
        'email_subject',
        'email_body',
        'is_sent',
    ];

    protected $casts = [
        'note_date' => 'date:Y-m-d',
        'send_bell' => 'boolean',
        'send_email' => 'boolean',
        'is_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
