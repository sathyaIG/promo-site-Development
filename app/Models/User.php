<?php


namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Scopes\TrashScope;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'email_1',
        'email_2',
        'status',
        'mobile',
        'role',
        'profile_image',
        'department',
        'business_type',
        'is_active',
        'remember_token',
        'active_tokan',
        'password',
        'created_by',
        'created_date',
        'status',
        'trash',
        // 'manufacturerLists',
        'redemptionPerOrder',
        'redemptionPerMember',
        'invoice',
        'fundingCategory',
        'created_at',
        'updated_at',
        'categories',
        'email_1',
        'email_2',
        'manufacturerLists'

    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */


    /**
     * This is a description of the method or function.
     *
     * @param  array $data
     * @param  int   $id
     * @param  mixed   $find_data
     * @return mixed
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** User Add */
    public function Store($data)
    {
        return $this->create($data);
    }

    /** User Add */
    public function Find_Single_User($find_data)
    {
        return $this->where($find_data)->first();
    }
    /** All List User*/


    public function AllUserList()
    {
        return $this->orderBy('created_at', 'Desc')->get();
    }
    /** List User*/


    public function UserList()
    {
        return $this->where('trash', 'NO')->get();
    }
    /**Insert User*/
    public function InsertUser($data)
    {

        return $this->create($data);
    }
    /**View User */
    public function GetUser($id)
    {
        return $this->where('id', $id)->first();
    }
    /**Update User */
    public function UpdateUser($id, $update_data)
    {
        return $this->where('id', $id)->update($update_data);
    }
    public function EmailCheck($email)
    {
        return $this->where('email', $email)->orWhere('email_1',$email)->orWhere('email_2',$email)->where('status', 1)->get();
    }
    public function ExistEmailCheck($email, $userid)
    {
        return $this->where('email', $email)->Where('email_1',$email)->Where('email_2',$email)
            ->where('id', '!=', decryptId($userid))
            ->where('status', 1)
            ->get();
    }
    public function Getmanufacturer(){
        return $this->where('role','5')->where('trash','NO')->get();
    }
    public function checkAdminExists(){
        return $this->where('role','1')->where('trash','No')->first();
    }
}
