<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskDependency extends Model
{
    protected $fillable = ['task_id', 'depends_on_task_id', 'dependency_type'];

    public function task() { return $this->belongsTo(ProjectTask::class, 'task_id'); }
    public function dependsOnTask() { return $this->belongsTo(ProjectTask::class, 'depends_on_task_id'); }
}
