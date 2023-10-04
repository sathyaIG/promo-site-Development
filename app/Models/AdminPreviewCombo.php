<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminPreviewCombo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;
    protected $table = 'admin_preview_combo';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'manufacturer_name',
        'combo_code',
        'combo_code_name',
        'code',
        'description',
        'mrp',
        'start_date',
        'endDate',
        'offerDetails',
        'redemptionLimit',
        'citiesSelection',
        'isPanIndia',
        'cities_yes_lists',
        'cities_no_lists',
        'department',
        'businessModel',
        'skuId',
        'ecID',
        'saCityId',
        'saLists',
        'discountType',
        'discountCategory',
        'discountValue',
        'fundingCategory',
        'fundingMarket',
        'fundtionVendor',
        'isInvoiced',
        'redemptionLimitPerOrder',
        'redemptionLimitPerOrder',
        'redemptionLimitPerCampaign',        
        'category_value',
        'status',
        'trash',
        'created_at',
        'updated_at',


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
        return $this->orderBy('created_at', 'Desc')->get();
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

        return $this->where('uploadedId', $id)->update($update_data);
    }

    public function UpdatePromotionPreview($id,$uploadedId,$update_data)
    {
        return $this->where('id', $id)->where('uploadedId',$uploadedId)->update($update_data);
    }

    public function AllUserPromotionList($id)
    {
        return $this->where('manufacturer_id',$id)->orderBy('created_at', 'Desc')->get();
    }

    public function GetPreviewDetails($id)
    {
        return $this->where('uploadedId',$id)->orderBy('id','Asc')->get();
    }
    public function getPromotionDetails($uploadPromotionIds){
        return $this->whereIn('uploadedId',$uploadPromotionIds)->where('approveRejectStatus','0')->get();
    }

    public function rejectSku($id, $update_data){
        return $this->where('id', $id)->update($update_data);
    }

    public function updateExceptRejected($id, $update_data){
        return $this->where('uploadedId', $id)->where('approveRejectStatus','0')->update($update_data);
    }

    public function getRejectedDetails($promotionId){
        return $this->where('uploadedId',$promotionId)->where('approveRejectStatus','2')->get();
    }

    public function getApprovedList($uploadPromotionIds,$selected_creteria,$selected_zone,$selected_cities){
        if($selected_creteria == 'Panindia'){
            return $this->whereIn('uploadedId',$uploadPromotionIds)->where('approveRejectStatus','1')->where('citiesSelection','Pan India')->get();
        }else if($selected_creteria == 'Zone'){
            $usezones = string_to_array($selected_zone);
            return $this->whereIn('uploadedId',$uploadPromotionIds)->where('approveRejectStatus','1')->where('citiesSelection','Selected Zone')
            ->where(function ($query) use ($usezones) {
                foreach ($usezones as $item) {
                    $query->orwhereRaw("FIND_IN_SET('$item', admin_preview_combo.zone_yes_lists) > 0");
                }
            })->get();
        }else if($selected_creteria == 'Cities'){
            $usecities = string_to_array($selected_cities);
            return $this->whereIn('uploadedId',$uploadPromotionIds)->where('approveRejectStatus','1')->where('citiesSelection','Selected Cities')
            ->where(function ($query) use ($usecities) {
                foreach ($usecities as $item) {
                    $query->orwhereRaw("FIND_IN_SET('$item', admin_preview_combo.cities_yes_lists) > 0");
                }
            })->get();
        }
        else{
            return $this->whereIn('uploadedId',$uploadPromotionIds)->where('approveRejectStatus','1')->get();
        }
        
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_preview_combo'));
    }
}
