<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminUserRole extends Model
{
    use HasFactory;
    protected $table = 'admin_user_role';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'user_role',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'trash',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'user_role' => 'string',
        'created_by' => 'string',
        'status' => 'integer',
        'trash' => 'string',
    ];

    public function UserRoleList()
    {
        return $this->where('trash', 'NO')->get();
    }
    /** Get Country  name  with matching User Id*/

    public function UserRoleName($name)
    {
        return $this->where('id', $name->role)->first();
    }

    public function getUserRole($flag){
        if($flag == 'yes'){
            return $this->where('trash','No')->get();

        }else{
            return $this->where('trash','No')->where('id','!=','1')->get();

        }
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_user_role'));
    }
}
