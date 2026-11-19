<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'role_group',
        'is_system',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->slug === 'superadmin' || $this->id === 1;
    }

    public function isYonetim(): bool
    {
        return $this->slug === 'yonetim' || $this->id === 19;
    }

    public function isAdmin(): bool
    {
        return $this->slug === 'admin' || $this->id === 20;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    public function group()
    {
        return $this->belongsTo(RoleGroup::class, 'role_group', 'slug');
    }

    public function getRoleGroupNameAttribute(): string
    {
        return $this->group ? $this->group->name : ucfirst($this->role_group ?? 'Genel');
    }

    public function getBadgeBgAttribute(): string
    {
        $hex = ltrim($this->color ?? '#64748b', '#');
        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, 0.12)";
        }
        return "#f1f5f9";
    }

    public function getBadgeBorderAttribute(): string
    {
        $hex = ltrim($this->color ?? '#64748b', '#');
        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, 0.35)";
        }
        return "#cbd5e1";
    }

    public function getBadgeTextAttribute(): string
    {
        return $this->color ?? '#334155';
    }

    public function getGroupTitleAttribute(): string
    {
        return match($this->role_group) {
            'yonetim' => 'Yönetim & Sistem',
            'proje' => 'Proje & Saha',
            'denetim' => 'Denetim & Kalite',
            default => 'Diğer',
        };
    }
}