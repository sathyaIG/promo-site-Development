<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminStateMaster extends Model
{
    use HasFactory;
    protected $table = 'admin_state_master';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'state',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'trash',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'state' => 'string',
        'created_by' => 'string',
        'status' => 'integer',
        'trash' => 'string',
    ];

    public function StateList()
    {
        return $this->where('trash', 'NO')->get();
    }
    /** Get Country  name  with matching User Id*/

    public function StateName($state)
    {

        return $this->where('id', $state)->first();
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_state_master'));
    }
}
