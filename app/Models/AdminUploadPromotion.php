<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminUploadPromotion extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;
    protected $table = 'admin_upload_promotion';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'file_name',
        'file_path',
        'promo_type',
        'manufacturer_id',
        'status',
        'created_by',
        'updated_by',
        'created_date',
        'status',
        'trash',
        'created_at',
        'updated_at',
        'promo_type',
        'business_type_id',
        'theme_id',
        'region_id',
        'upload_status',
        'review',
        'file_orgname',
        'minimum_value',
        'minimum_purchase',

    ];

    /** User Add */
    public function Store($data)
    {
        return $this->create($data);
    }

    /** User Add */
    public function Find_Single_Promotion($find_data)
    {
        return $this->where($find_data)->first();
    }
    /** All List User*/


    public function AllPromotionList()
    {
        return $this->where('totalSku', '>', 0)->orderBy('created_at', 'Desc')->get();
    }
    /** List User*/


    /**Insert User*/
    public function InsertPromotion($data)
    {

        return $this->create($data);
    }
    /**View User */
    public function GetPromotion($id)
    {
        return $this->where('id', $id)->first();
    }
    /**Update User */
    public function UpdatePromotion($id, $update_data)
    {

        return $this->where('id', $id)->update($update_data);
    }

    public function AllUserPromotionList($id)
    {
        return $this->where('manufacturer_id',$id)->where('totalSku', '>', 0)->orderBy('created_at', 'Desc')->get();
    }

    public function getPromotionBasedOnCondition($manufacturer_id){
        return $this->whereIn('manufacturer_id',$manufacturer_id)->where('totalSku', '>', 0)->orderBy('created_at', 'Desc')->get();
    }

    public function adminPreview()
    {
        return $this->hasOne(AdminPreview::class, 'uploadedId', 'id');
    }


    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_upload_promotion'));
    }
}
