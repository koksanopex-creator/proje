<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'task_id', 'original_name', 'storage_path', 'mime_type',
        'file_size', 'category', 'tags', 'revision', 'uploaded_by'
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function task() { return $this->belongsTo(ProjectTask::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    /**
     * Etiketleri temiz bir dizi olarak döndürür
     */
    public function getTagsArrayAttribute(): array
    {
        $tags = $this->tags;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $tags)));
        }
        return is_array($tags) ? array_values(array_filter($tags)) : [];
    }

    /**
     * Belirli bir etikete sahip mi?
     */
    public function hasTag(string $tag): bool
    {
        $tag = ltrim(trim($tag), '#');
        foreach ($this->tags_array as $t) {
            if (strcasecmp(ltrim(trim($t), '#'), $tag) === 0) {
                return true;
            }
        }
        return false;
    }
}