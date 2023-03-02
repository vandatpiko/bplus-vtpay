<?php
namespace VandatPiko\BplusVTPay\Models;

use Illuminate\Database\Eloquent\Model;

class BplusVTPay extends Model
{
    protected $primaryKey = 'username';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'username', 'display_name', 'client_private_key', 'viettel_public_key','token_notification','account_id'
    ];

    protected $casts = [
        'username'  => 'string',
        'extra_data'=> 'object',
    ];

    /**
     * @return string
     */
    protected $hidden = [
        'client_private_key', 'viettel_public_key', 'token_notification', 'imei', 'user_id', 'password'
    ];

    protected $appends = [
        'status', 'rank_type'
    ];

    public function getTable()
    {
        return config('bplusvtpay.table');
    }

    /**
     * @return App\Models\User
     */

    public function user()
    {
        return $this->belongsTo(config('bplusvtpay.model_user'), 'user_id', 'id');
    }
    /**
     * @return App\Models\BplusVTPay
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }

    public function getStatusAttribute()
    {
        return bplusvtpay()->setBplusVTPay($this->username)->getStatus();
    }

    public function getRankTypeAttribute()
    {
        $bankPlusPackage = $this->extra_data->bankPlusPackage ?? '';
        switch ($bankPlusPackage) {
            case 'VTT_BANKPLUS_VDS':
                return 'Gói 1';
            case 'VTT_PACKAGE_26':
                return 'Gói trải nghiệm';
            case 'VTT_BANKPLUS_ECO':
                return 'Gói không giới hạn';
            case 'VTT_BANKPLUS_START';
                return 'Gói tiêu chuẩn';
            case 'VTT_BANKPLUS_FLEX':
                return 'Gói 2';
            default:
                return 'Trống';
                break;
        }
    }

    public function pushOfExtraData($key, $value) : void
    {
        $extra_data = (array) $this->extra_data;
        $extra_data[$key]   = $value;
        $this->extra_data = (object) $extra_data;
    }
}
