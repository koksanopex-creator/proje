<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'sort_order',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function roles()
    {
        return $this->hasMany(Role::class, 'role_group', 'slug');
    }
}