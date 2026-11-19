<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMeetingParticipant extends Model
{
    protected $fillable = ['meeting_id', 'user_id', 'external_attendee_name', 'external_attendee_email', 'status'];

    public function meeting() { return $this->belongsTo(ProjectMeeting::class); }
    public function user() { return $this->belongsTo(User::class); }
}
