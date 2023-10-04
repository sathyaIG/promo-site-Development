<?php

namespace App\Models;

use App\Scopes\TrashScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingGeneral extends Model {

    use HasFactory;

    protected $table = 'admin_setting_general_settings';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id',
        'minimum_rate_card_amt',
        'number_of_slot',
        'slot_block_time',
        'threshold_days',
        'approve_limit',
        'otp_date',
        'post_approval_threshold',
        'free_campaign_users',
        'status',
        'trash',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',       
    ];
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected static function booted() {
        static::addGlobalScope(new TrashScope);
    }

        
}
