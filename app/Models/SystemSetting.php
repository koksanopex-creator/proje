<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, $type = 'string', $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'group' => $group]);
    }
}
