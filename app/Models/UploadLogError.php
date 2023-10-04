<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Scopes\TrashScope;

class UploadLogError extends Model
{
    use HasFactory;

    protected $table = 'admin_upload_error_log';
    protected $fillable = [
        'id',
        'log_id',
        'file_name',
        'error',
        'status',
        'trash',
        'created_at',
        'updated_at',
        'lineNumber'
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_upload_error_log'));
    }

    /**
     * This is a description of the method or function.
     *
     * @param  array $data
     * @param  array $where_data
     * @param  int   $id
     * @return mixed
     */


    protected function Exist($id)
    {
        return $this->where('id', $id)->count();
    }

    protected function Store($data)
    {
        return $this->create($data);
    }

    protected function Updates($data, $id)
    {
        return $this->where('id', $id)->update($data);
    }
    /** Partner List */
    public function List()
    {
        return $this->where('trash', 'NO')->get();
    }


    /** Partner View */
    public function View($id)
    {
        return $this->where('trash', 'NO')->where('id', $id)->first();
    }

    public function UploadLogErrorList($id)
    {
        return $this->where('log_id', $id)->orderBy('id','Desc')->get();
    }
    public function UploadLogErrorListPromotion($id)
    {
        return $this->where('log_id', $id)->orderBy('id','Desc')->get();
    }
}
