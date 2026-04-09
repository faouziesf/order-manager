<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyKolixyConfig extends Model
{
    protected $table = 'company_kolixy_config';

    protected $fillable = [
        'api_token',
        'pickup_address_id',
        'company_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
    ];

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
