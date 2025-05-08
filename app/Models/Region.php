<?php
// app/Models/Region.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Relations
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}

// app/Models/City.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
        'shipping_cost',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:3',
    ];

    /**
     * Relations
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}