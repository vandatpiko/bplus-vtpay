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
}
