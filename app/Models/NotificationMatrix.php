<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationMatrix extends Model
{
    protected $table = 'notification_matrix';

    protected $fillable = ['event_key', 'role_id', 'user_id', 'is_bell_active', 'is_mail_active', 'template_id'];

    protected function casts(): array
    {
        return [
            'is_bell_active' => 'boolean',
            'is_mail_active' => 'boolean',
        ];
    }

    public function role() { return $this->belongsTo(Role::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function template() { return $this->belongsTo(NotificationTemplate::class, 'template_id'); }
}
