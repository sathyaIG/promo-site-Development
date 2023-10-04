<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TrashScope;

class AdminMinimumPurchase extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;
    protected $table = 'admin_minimum_purchase';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'minimum_purchase',
        'status',
        'trash',
        'created_at',
        'updated_at',
        'category_value',
        'process_type'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TrashScope('admin_minimum_purchase'));
    }
}
