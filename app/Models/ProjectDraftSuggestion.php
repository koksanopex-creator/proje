<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDraftSuggestion extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'field_key',
        'field_label',
        'suggestion_text',
        'external_email',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}