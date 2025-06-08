<?php

// =====================================

// app/Models/ReportExecution.php (pour les rapports programmés)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'status',
        'started_at',
        'completed_at',
        'file_path',
        'file_size',
        'error_message'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Relations
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        $colors = [
            'completed' => 'success',
            'failed' => 'danger',
            'running' => 'warning',
            'pending' => 'info'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at) . ' sec';
    }
}