<?php
// app/Models/Report.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'format',
        'date_from',
        'date_to',
        'metrics',
        'data',
        'include_charts',
        'is_scheduled',
        'schedule_frequency',
        'next_execution',
        'generated_by',
        'file_path',
        'file_size',
        'is_automated'
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'metrics' => 'array',
        'data' => 'array',
        'include_charts' => 'boolean',
        'is_scheduled' => 'boolean',
        'is_automated' => 'boolean',
        'next_execution' => 'datetime'
    ];

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return 'N/A';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
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
            'admin_activity' => 'Activité Administrateurs',
            'system_usage' => 'Utilisation Système',
            'performance' => 'Performance',
            'revenue' => 'Revenus',
            'custom' => 'Personnalisé'
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getStatusAttribute()
    {
        if ($this->file_path && \Storage::exists($this->file_path)) {
            return 'available';
        }
        return 'expired';
    }

    // Relations
    public function executions()
    {
        return $this->hasMany(ReportExecution::class);
    }

    // Methods
    public function isExpired()
    {
        return $this->created_at->addDays(30)->isPast();
    }

    public function canBeDownloaded()
    {
        return $this->file_path && \Storage::exists($this->file_path);
    }
}

