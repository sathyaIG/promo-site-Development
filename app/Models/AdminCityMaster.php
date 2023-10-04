<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminCityMaster extends Model
{
    use HasFactory;
    protected $table = 'admin_city_master';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'city',
        'state_id',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'trash',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'city' => 'string',
        'state_id' => 'integer',
        'created_by' => 'string',
        'status' => 'integer',
        'trash' => 'string',
    ];

    public function CityList()
    {
        return $this->where('trash', 'NO')->get();
    }
    /** Get Country  name  with matching User Id*/

    public function StateCityList($state_id)
    {
        return $this->where('state_id', $state_id)->get();
    }

    public function CityName($city)
    {

        return $this->where('id', $city)->first();
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_city_master'));
    }
}
