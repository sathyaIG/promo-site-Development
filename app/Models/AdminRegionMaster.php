<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminRegionMaster extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;
    protected $table = 'admin_region_master';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'region',
        'state',
        'city',
        'status',
        'region_code',
        'region_id',
        'created_by',
        'updated_by',
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
    public function Find_Single_Region($find_data)
    {
        return $this->where($find_data)->first();
    }
    /** All List User*/


    public function AllRegionList()
    {
        return $this->orderBy('created_at', 'Desc')->get();
    }
    /** List User*/


    public function RegionList()
    {
        
        return $this->where('trash', 'NO')->get();
    }
    /**Insert User*/
    public function InsertRegion($data)
    {

        return $this->create($data);
    }
    /**View User */
    public function GetRegion($id)
    {
        return $this->where('id', $id)->first();
    }
    /**Update User */
    public function UpdateRegion($id, $update_data)
    {

        return $this->where('id', $id)->update($update_data);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_region_master'));
    }
}
