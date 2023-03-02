<?php

namespace VandatPiko\BplusVTPay;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use VandatPiko\BplusVTPay\Contracts\BplusVTPayContract;
use VandatPiko\BplusVTPay\Traits\BplusVTPayTrait;

class BplusVTPay implements BplusVTPayContract
{
    use BplusVTPayTrait;
    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    /**
     * @var Illuminate\Contracts\Auth\Factory
     */
    protected $authFactory;

    /**
     * @array configure
     */

    protected $configure;


    protected $bplusVTPay;


    public function __construct(Client $client,$authFactory)
    {
        /**
         * @var GuzzleHttp\Client
         */
        $this->client = $client;

        $this->configure = config('bplusvtpay');

        $this->authFactory = $authFactory;
    }

    public function setBplusVTPay($username)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Username is required');
        }
        $username = convertPhonenumberTo84($username);
        $this->bplusVTPay = $this->configure['model']::find($username);
        if (!$this->bplusVTPay) {
            $this->bplusVTPay = new $this->configure['model']();
            $this->bplusVTPay->user_id  = $this->authFactory->id();
            $this->bplusVTPay->username = $username;
            $this->bplusVTPay->imei     = generateImei();
            $this->bplusVTPay->token_notification = generateToken();
            $this->generateRsa();
        }
        $this->refreshAccessToken();
        return $this;
    }

    public function getStatus() : bool
    {
        $decoded_token = Encrypt::decode($this->bplusVTPay->access_token);
        if (is_object($decoded_token)){
            if ($decoded_token->exp >= time()) {
                return true;
            }
        }
        return false;
    }

    public function getGift()
    {
        $result = $this->CODEX();
        $data   = array();
        if($result !== false) {
            if($result->status->code == '00') {
                foreach ($result->data->available as $item) {
                    $base64 = base64_encode(json_encode([
                        'type'         => '1',
                        'voucherCodex' => $item->codes['0']->codex,
                        'voucherId'    => (string) $item->voucher->id
                    ],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
                    $data[] = (object) [
                        'title'         => $item->voucher->title,
                        'voucher_code'  => $base64
                    ];
                }
            }
            return (object) [
                'success' => true,
                'message' => 'Lấy mã giảm giá thành công',
                'data'    => $data
            ];
        }
        return (object) [
            'success' => false,
            'message' => 'Lấy danh sách mã giảm giá thất bại'
        ];
    }

    public function ekycActive($data)
    {
        $this->bplusVTPay->password = $data['password'];
        $result = $this->ACTIVE($data);
        if($result != false) {
            if (!empty($result->data)){
                $this->bplusVTPay->account_id      = $result->data->customerInfo->accountId;
                $this->bplusVTPay->session_id      = $result->data->customerInfo->otherData->sessionId;
                $this->bplusVTPay->refresh_token   = $result->data->loginInfo->refreshToken;
                $this->bplusVTPay->acc_no          = $result->data->customerInfo->sources->infra[0]->accNo;
                $this->bplusVTPay->display_name    = $result->data->loginInfo->customerName;
                $this->bplusVTPay->save();
                return (object) array(
                    'success' => true,
                    'message' => $result->status->displayMessage
                );
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) array(
            'success' => false,
            'message' => $result->status->displayMessage ?? 'Xác minh tài khoản thất bại vui lòng thử lại'
        );
    }

    public function ekycImage($imageFront = '',$imageBack = '')
    {
        $result = $this->GOV_ID($imageFront, $imageBack);
        if($result != false) {
            if($result->status->code == '00') {
                return (object) array(
                    'success' => true,
                    'message' => 'Xác minh thành công',
                    'data'    => $result->data
                );
            }
            return (object) array(
                'success' => false,
                'message' => $result->status->displayMessage
            );
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) array(
            'success' => false,
            'message' => 'Xác thực hình ảnh giấy tờ thất bại'
        );
    }

    public function ekycFace(object $base64ImageArray)
    {
        $result = $this->ONBOARD();
        if ($result !== false) {
            if ($result->status->code == '00') {
                $this->pushOfExtraData('correlation_id', $result->data->correlationId);
                $result = $this->PORTRAIT($base64ImageArray);
                if($result != false) {
                    if($result->status->code == '00') {
                        $this->bplusVTPay->save();
                        return (object) array(
                            'success' => true,
                            'message' => 'Xác thực hình ảnh khuôn mặt thành công'
                        );
                    }
                }
                return (object) [
                    'success'   => false,
                    'message'   => $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại'
                ];
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) array(
            'success' => false,
            'message' => $result->status->displayMessage ?? 'Xác thực hình ảnh khuôn mặt thất bại'
        );
    }

    public function getHistoryVTP($start_date = null, $end_date = null)
    {
        $start_date = $start_date ?: Carbon::today()->format('Y-m-01');
        $end_date = $end_date ?: Carbon::today()->format('Y-m-t');
        $result   = $this->GET_HISTORY_VTP($start_date, $end_date);
        if ($result !== false) {
            if ($result->responseCode == '00') {
                return (object) [
                    'success'   => true,
                    'message'   => 'Thành công',
                    'data'      => $result->listVTPTransactionHistory
                ];
            }
        }
        return (object) array(
            'success' => false,
            'message' => $result->errorCodeDetail ?? 'Đã xảy ra lỗi xác nhận OTP'
        );
    }

    public function registerWithOTP($otp)
    {
        if (empty($otp)) {
            throw new \InvalidArgumentException('OTP is required');
        }
        $result = $this->REGISTER_OTP($otp);
        if ($result !== false) {
            if ($result->status->code == '00'){
                $this->bplusVTPay->authorization = $result->data->loginInfo->accessToken;
                $this->bplusVTPay->account_id    = $result->data->customerInfo->accountId;
                $this->bplusVTPay->session_id    = $result->data->customerInfo->otherData->sessionId;
                $this->bplusVTPay->refresh_token = $result->data->loginInfo->refreshToken;
                $this->bplusVTPay->save();
                return (object) array(
                    'success' => true,
                    'message' => 'Đăng ký tài khoản thành công'
                );
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) array(
            'success' => false,
            'message' => $result->status->displayMessage ?? 'Đã xảy ra lỗi xác nhận OTP'
        );
    }

    public function registerAccount()
    {
        $result = $this->CHECK_USER();
        if ($result !== false) {
            switch ($result->status->code) {
                case '00':
                    return (object) [
                        'success' => false,
                        'message' => 'Tài khoản đã được đăng ký',
                        'is_empty'=> false
                    ];
                    break;
                case 'CS9901':
                    $result = $this->REGISTER_ACCOUNT();
                    if ($result !== false) {
                        $this->pushOfExtraData('transaction_id', $result->data->transactionId);
                        $this->pushOfExtraData('state', 'REGISTER');
                        if ($result->status->code == 'CS0203'){
                            return (object) array(
                                'success' => false,
                                'otp'     => true,
                                'message' => 'Vui lòng nhập mã OTP để tiếp tục'
                            );
                        }
                    }
                    break;
            }
        }
        return (object) [
            'success' => false,
            'message' => 'Đã xảy ra lỗi vui lòng thử lại'
        ];
    }

    public function getBalance()
    {
        $response = $this->BALANCE_INQUIRY_NO_PIN();
        if ($response !== false) {
            if ($response->response_code == '00') {
                $this->bplusVTPay->balance = $response->balance;
                $this->bplusVTPay->save();
                return (object) [
                    'success'   => true,
                    'message'   => 'Thành công',
                    'balance'   => (int) $this->bplusVTPay->balance
                ];
            }
        }
        return (object) [
            'success' => false,
            'message' => 'Đã xảy ra lỗi vui lòng thử lại'
        ];
    }

    public function checkUser()
    {
        $result = $this->CHECK_USER();
        if ($result !== false) {
            switch ($result->status->code) {
                case '00':
                    $this->bplusVTPay->account_id = $result->data->accountId;
                    $this->bplusVTPay->display_name = $result->data->displayName;
                    $this->bplusVTPay->save();
                    return (object) [
                        'success'   => true,
                        'message'   => $result->status->displayMessage,
                        'need_pin'  => $result->data->needPin,
                    ];
                    break;
                case 'CS9901':
                    return (object) [
                        'success' => false,
                        'message' => $result->status->displayMessage,
                        'is_empty'=> true
                    ];
                    break;
                default:
                    return (object) [
                        'success' => false,
                        'message' => 'Đã xảy ra lỗi vui lòng thử lại'
                    ];
                    break;
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) [
            'success' => false,
            'message' => 'Đã xảy ra lỗi vui lòng thử lại'
        ];
    }

    public function loginWithOTP($otp)
    {
        $result = $this->LOGIN_WITH_OTP($otp);
        if ($result !== false) {
            if ($result->status->code == '00') {
                $this->bplusVTPay->access_token = $result->data->accessToken;
                $this->bplusVTPay->refresh_token = $result->data->refreshToken;
                $this->bplusVTPay->login_at = Carbon::now();
                $this->getSessionId();
                $this->bplusVTPay->save();
                return (object) [
                    'success' => true,
                    'message' => $result->status->displayMessage,
                ];
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) [
            'success' => false,
            'message' => $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại'
        ];
    }

    public function loginWithPassword($password = null)
    {
        $password = $password ?? $this->bplusVTPay->password;
        if (empty($password)) {
            throw new \InvalidArgumentException("Invalid password");
        }
        $result = $this->LOGIN_NEED_PIN($password);
        if ($result != false) {
            switch ($result->status->code) {
                case '00':
                    $this->bplusVTPay->access_token = $result->data->accessToken;
                    $this->bplusVTPay->refresh_token = $result->data->refreshToken;
                    $this->bplusVTPay->password = $password;
                    $this->getSessionId();
                    $this->bplusVTPay->login_at = Carbon::now();
                    $this->bplusVTPay->save();
                    return (object) [
                        'success' => true,
                        'message' => $result->status->displayMessage
                    ];
                    break;
                case 'AUT0014':
                    $this->bplusVTPay->password   = $password;
                    $this->pushOfExtraData('request_id', $result->data->requestId);
                    $this->pushOfExtraData('state', 'LOGIN');
                    $this->bplusVTPay->save();
                    return (object) [
                        'success' => false,
                        'message' => $result->status->displayMessage,
                        'otp'     => true
                    ];
                    break;
            }
        }
        $this->pushOfExtraData('error_message', $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại');
        return (object) [
            'success' => false,
            'message' => $result->status->displayMessage ?? 'Đã xảy ra lỗi vui lòng thử lại'
        ];
    }

    public function getBplusVTPay($username = null)
    {
        return $this->configure['model']::where('user_id', '=', $this->authFactory->id())->where(function ($query) use ($username) {
            if ($username) {
                $query->where('username', '=', $username);
            }
        })->get()->toArray();
    }
}
