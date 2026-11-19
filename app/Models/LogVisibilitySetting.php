<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogVisibilitySetting extends Model
{
    protected $fillable = ['role_id', 'log_category', 'can_view_logs', 'show_full_details'];

    protected function casts(): array
    {
        return [
            'can_view_logs' => 'boolean',
            'show_full_details' => 'boolean',
        ];
    }

    public function role() { return $this->belongsTo(Role::class); }
}
