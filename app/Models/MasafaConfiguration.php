<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasafaConfiguration extends Model
{
    protected $table = 'masafa_configurations';

    protected $fillable = [
        'admin_id',
        'api_token',
        'masafa_user_name',
        'masafa_user_email',
        'masafa_user_id',
        'masafa_client_id',
        'default_gouvernorat',
        'default_delegation',
        'default_address',
        'default_phone',
        'pickup_name',
        'is_active',
        'auto_send',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_send' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
