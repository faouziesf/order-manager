<?php
// =====================================

// app/Models/Backup.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'status',
        'file_path',
        'size',
        'compression',
        'started_at',
        'completed_at',
        'error_message',
        'retention_days',
        'is_automated'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_automated' => 'boolean'
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAutomated($query)
    {
        return $query->where('is_automated', true);
    }

    // Accessors
    public function getSizeFormattedAttribute()
    {
        if (!$this->size) return 'N/A';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getTypeNameAttribute()
    {
        $types = [
            'database' => 'Base de données',
            'files' => 'Fichiers',
            'full' => 'Complète'
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'completed' => 'success',
            'failed' => 'danger',
            'in_progress' => 'warning',
            'pending' => 'info'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at) . ' min';
    }

    // Methods
    public function isExpired()
    {
        if (!$this->retention_days) return false;
        
        return $this->created_at->addDays($this->retention_days)->isPast();
    }

    public function canBeDownloaded()
    {
        return $this->status === 'completed' && 
               $this->file_path && 
               \Storage::exists($this->file_path) && 
               !$this->isExpired();
    }

    public function getDownloadUrl()
    {
        if (!$this->canBeDownloaded()) return null;
        
        return route('super-admin.system.backup.download', $this);
    }
}


