<?php

// =====================================

// app/Models/SystemLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'message',
        'context',
        'extra',
        'channel',
        'datetime',
        'remote_addr',
        'user_agent',
        'url',
        'method'
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'datetime' => 'datetime'
    ];

    // Scopes
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeErrors($query)
    {
        return $query->where('level', 'error');
    }

    public function scopeWarnings($query)
    {
        return $query->where('level', 'warning');
    }

    public function scopeInfo($query)
    {
        return $query->where('level', 'info');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    // Accessors
    public function getLevelColorAttribute()
    {
        $colors = [
            'emergency' => 'danger',
            'alert' => 'danger',
            'critical' => 'danger',
            'error' => 'danger',
            'warning' => 'warning',
            'notice' => 'info',
            'info' => 'info',
            'debug' => 'secondary'
        ];

        return $colors[$this->level] ?? 'secondary';
    }

    public function getLevelIconAttribute()
    {
        $icons = [
            'emergency' => 'fas fa-exclamation-circle',
            'alert' => 'fas fa-exclamation-triangle',
            'critical' => 'fas fa-times-circle',
            'error' => 'fas fa-times-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'notice' => 'fas fa-info-circle',
            'info' => 'fas fa-info-circle',
            'debug' => 'fas fa-bug'
        ];

        return $icons[$this->level] ?? 'fas fa-circle';
    }

    public function getShortMessageAttribute()
    {
        return \Str::limit($this->message, 100);
    }

    // Methods
    public function hasContext()
    {
        return !empty($this->context);
    }

    public function hasExtra()
    {
        return !empty($this->extra);
    }

    public static function cleanOldLogs($days = 30)
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }

    public static function getLogStats()
    {
        return [
            'total' => static::count(),
            'today' => static::today()->count(),
            'errors_today' => static::today()->errors()->count(),
            'warnings_today' => static::today()->warnings()->count(),
            'by_level' => static::selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray()
        ];
    }
}

