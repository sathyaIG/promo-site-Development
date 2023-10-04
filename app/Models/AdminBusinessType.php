<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminBusinessType extends Model
{
    use HasFactory;
    protected $table = 'admin_business_type';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'business_type',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'trash',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'business_type' => 'string',
        'created_by' => 'string',
        'status' => 'integer',
        'trash' => 'string',
    ];

    public function BusinessTypeList()
    {
        return $this->where('trash', 'NO')->get();
    }
    /** Get Country  name  with matching User Id*/

    public function BusinessTypeName($business_type)
    {
        return $this->where('id', $business_type->business_type)->first();
    }

    public function BussinessTypeMultiple($bussinessId){
        return $this->whereIn('id',$bussinessId)->get();
    }


    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_business_type'));
    }
}
