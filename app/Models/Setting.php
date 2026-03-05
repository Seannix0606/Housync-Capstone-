<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        // Clear cache
        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Get all settings grouped by group
     */
    public static function getGrouped()
    {
        return Cache::remember('settings.grouped', 3600, function () {
            return self::all()->groupBy('group');
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup($group)
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return self::where('group', $group)->get();
        });
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer', 'int' => (int) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'float', 'double' => (float) $value,
            default => $value,
        };
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
        Cache::forget('settings.grouped');
        Cache::forget('settings.group.general');
        Cache::forget('settings.group.email');
        Cache::forget('settings.group.security');
        Cache::forget('settings.group.features');
        Cache::forget('settings.group.payment');
        Cache::forget('settings.group.notifications');
        Cache::forget('settings.group.system');
    }

    /**
     * Boot method to clear cache on update
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.grouped');
            Cache::forget("settings.group.{$setting->group}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.grouped');
            Cache::forget("settings.group.{$setting->group}");
        });
    }
}
