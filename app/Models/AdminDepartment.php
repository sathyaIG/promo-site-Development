<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminDepartment extends Model
{
    use HasFactory;
    protected $table = 'admin_department';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'department',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'trash',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'department' => 'string',
        'created_by' => 'string',
        'status' => 'integer',
        'trash' => 'string',
    ];

    public function DepartmentList()
    {
        return $this->where('trash', 'NO')->where('id','!=','11')->get();
    }
    /** Get Country  name  with matching User Id*/

    public function DepartmentName($department)
    {
        return $this->where('id', $department->department)->first();
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_department'));
    }
}
