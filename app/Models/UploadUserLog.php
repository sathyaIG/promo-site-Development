<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Scopes\TrashScope;

class UploadUserLog extends Model
{
    use HasFactory;


    protected $table = 'admin_user_upload_log';
    protected $fillable = [
        'id',
        'extract_status',
        'upload_type',
        'file_name',
        'file_orgname',
        'file_path',
        'status',
        'created_by',
        'trash',
        'created_at',
        'updated_at',

    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_user_upload_log'));
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
    public function Updatefun($data,$id)
    {
        return $this->where('file_uploaded_id',$id)->update($data);
    }
    public function Getidfun($id)
    {
        return $this->where('file_uploaded_id', $id)->orderBy('id', 'DESC')->first();
    }
    /** Partner List */
    public function List()
    {
        return $this->where('trash', 'NO')->orderBy('id', 'DESC')->get();
    }

    public function getPromotionBasedOnCondition($manufacturer_id){
        return $this->whereIn('created_by',$manufacturer_id)->orderBy('created_at', 'DESC')->get();
    }

    public function getById($manufacturer_id){
        return $this->where('created_by',$manufacturer_id)->get();
    }

    /** Partner View */
    public function View($id)
    {
        return $this->where('trash', 'NO')->where('id', $id)->first();
    }
}
