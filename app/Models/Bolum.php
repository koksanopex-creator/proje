<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bolum extends Model
{
    use SoftDeletes;

    protected $table = 'bolumler';

    protected $fillable = ['ad', 'kod', 'director_id', 'lider_id', 'parent_id', 'is_active'];

        public function getNameAttribute()
    {
        return $this->ad ?? 'Bölüm #' . $this->id;
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'bolum_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'bolum_id');
    }
}
