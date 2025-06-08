<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SuperAdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'priority',
        'related_admin_id',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    // Relations
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'related_admin_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeImportant($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute()
    {
        $icons = [
            'admin_registered' => 'fas fa-user-plus',
            'admin_expired' => 'fas fa-exclamation-triangle',
            'admin_expiring' => 'fas fa-clock',
            'high_order_volume' => 'fas fa-chart-line',
            'system' => 'fas fa-cog',
            'security' => 'fas fa-shield-alt',
            'backup' => 'fas fa-download',
            'maintenance' => 'fas fa-tools',
            'default' => 'fas fa-bell'
        ];

        return $icons[$this->type] ?? $icons['default'];
    }

    public function getColorClassAttribute()
    {
        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    public function getBadgeClassAttribute()
    {
        if ($this->is_read) {
            return 'badge-secondary';
        }

        $badges = [
            'high' => 'badge-danger',
            'medium' => 'badge-warning',
            'low' => 'badge-info'
        ];

        return $badges[$this->priority] ?? 'badge-primary';
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    // Static methods pour les types de notifications
    public static function getTypes()
    {
        return [
            'admin_registered' => 'Administrateur inscrit',
            'admin_expired' => 'Administrateur expiré',
            'admin_expiring' => 'Administrateur expire bientôt',
            'admin_inactive' => 'Administrateur inactif',
            'high_order_volume' => 'Volume de commandes élevé',
            'system' => 'Système',
            'security' => 'Sécurité',
            'backup' => 'Sauvegarde',
            'maintenance' => 'Maintenance'
        ];
    }

    public static function getPriorities()
    {
        return [
            'low' => 'Faible',
            'medium' => 'Moyenne',
            'high' => 'Élevée'
        ];
    }

    // Boot method pour les événements
    protected static function boot()
    {
        parent::boot();

        // Nettoyer automatiquement les anciennes notifications (plus de 30 jours)
        static::creating(function ($notification) {
            self::where('created_at', '<', now()->subDays(30))->delete();
        });
    }
}