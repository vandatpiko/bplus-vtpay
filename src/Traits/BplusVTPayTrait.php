<?php

namespace VandatPiko\BplusVTPay\Traits;

use Carbon\Carbon;
use VandatPiko\BplusVTPay\Encrypt;

trait BplusVTPayTrait
{
    protected function CODEX()
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $res = $this->client->request('GET','https://vtpgw.viettel.vn/evoucher/public/v2/vouchers/codex-new',array(
                'headers' => array(
                    'Host'          => 'vtpgw.viettel.vn',
                    'X-SESSION-ID'  => $encrypt->encrypt($this->bplusVTPay->session_id),
                    'Channel'       => 'APP',
                    'Authorization' => 'Bearer '. $this->bplusVTPay->session_id,
                    'User-Id'       => $this->bplusVTPay->username,
                    'Accept-Language' => 'vi;q=1.0',
                    'Accept'        => '*/*',
                    'X-Request-Id'  => getOrderId(),
                    'User-Agent'    => 'Viettel Money/5.0.8 (com.viettel.viettelpay; build:2; iOS 15.4.1) Alamofire/5.0.8',
                    'Connection'    => 'keep-alive'
                )
            ));
            return json_decode($res->getBody());
        }
        catch (\Throwable $e){
        }
        return false;
    }
    /**
     * @return object
     */
    protected function PORTRAIT(object $base64ImageArray)
    {
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/customer-ekyc/v1/kyc/portrait', array(
                'headers'   => array(
                    'content-type'      => 'application/json',
                    'authorization'     => 'Bearer '.$this->bplusVTPay->access_token,
                    'app-version'       => $this->configure['app_version'],
                    'channel'           => 'APP',
                    'product'           => 'VIETTELPAY',
                    'accept'            => '*/*',
                    'type-os'           => 'ios',
                    'accept-language'   => 'vi',
                    'accept-encoding'   => 'gzip;q=1.0, compress;q=0.5',
                    'device-name'       => 'iPhone',
                    'imei'              => $this->bplusVTPay->imei,
                    'user-agent'        => 'Viettel Money/5.0.8 (com.viettel.viettelpay; build:2; iOS 15.4.1) Alamofire/5.0.8',
                    'os-version'        => $this->configure['os_version'],
                    'authority-party'   => 'APP'
                ),
                'json'     => array (
                    'activation'            => '0',
                    'base64BottomImage'     => $base64ImageArray->base64BottomImage,
                    'base64Image'           => $base64ImageArray->base64Image,
                    'base64LeftImage'       => $base64ImageArray->base64LeftImage,
                    'base64RightImage'      => $base64ImageArray->base64RightImage,
                    'base64StraightImage'   => $base64ImageArray->base64StraightImage,
                    'base64TopImage'        => $base64ImageArray->base64TopImage,
                    'device_id'             => $this->bplusVTPay->imei,
                    'msisdn'                => $this->bplusVTPay->username,
                    'otp'                   => '',
                    'trans_id'              => ''
                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */

    protected function ACTIVE($data)
    {
        info(json_encode($data, JSON_UNESCAPED_UNICODE));
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/customer/v2/accounts/active', array(
                'headers'   => array(
                    'content-type'      => 'application/json',
                    'authorization'     => 'Bearer '.$this->bplusVTPay->access_token,
                    'app-version'       => $this->configure['app_version'],
                    'channel'           => 'APP',
                    'product'           => 'VIETTELPAY',
                    'accept'            => '*/*',
                    'type-os'           => 'ios',
                    'accept-language'   => 'vi',
                    'accept-encoding'   => 'gzip;q=1.0, compress;q=0.5',
                    'device-name'       => 'iPhone',
                    'imei'              => $this->bplusVTPay->imei,
                    'user-agent'        => 'Viettel Money/5.0.8 (com.viettel.viettelpay; build:2; iOS 15.4.1) Alamofire/5.0.8',
                    'os-version'        => $this->configure['os_version'],
                    'authority-party'   => 'APP'
                ),
                'json'      => array (
                    'type'        => 'NHS',
                    'app_version' => $this->configure['app_version'],
                    'birthDate'   => str_replace('-','/',$data['dateOfBirth']),
                    'gender'      => $data['gender'],
                    'currentAddress' => $data['addressPermanent'],
                    'order_id'    => getOrderId(),
                    'imei'        => $this->bplusVTPay->imei,
                    'typeOs'      => 'iOS',
                    'notifyToken' => $this->bplusVTPay->token_notification,
                    'ekycData'    => array (
                        'partner' => 'TS',
                    ),
                    'idIssueDate'  => str_replace('-','/',$data['govIdIssueDate']),
                    'app_name'     => $this->configure['app_name'],
                    'districtName' => $data['district'],
                    'pin'          => $data['password'],
                    'custName'     => $data['fullname'],
                    'refreshToken' => $this->bplusVTPay->refresh_token,
                    'onboardingId' => $this->bplusVTPay->extra_data->correlation_id,
                    'birthplace'   => $data['addressPermanent'],
                    'residenceStatus' => '',
                    'identityValue'=> $this->bplusVTPay->username,
                    'nationality'  => 'VN',
                    'identityType' => 'msisdn',
                    'idNo'         => $data['govId'],
                    'idType'       => '6',
                    'provinceName' => $data['province'],
                    'precinctName' => $data['area'],
                    'type_os'      => 'ios',
                    'residentialAddress' => $data['addressPermanent'],
                    'idIssuePlace' => $data['govIdIssuePlace'],
                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
            info($e);
        }
        return false;
    }

    protected function ONBOARD()
    {
        try {
            $res = $this->client->request('GET', 'https://api8.viettelpay.vn/customer/v2/accounts/onboard', array(
                'query'     => [
                    'msisdn'        => $this->bplusVTPay->username,
                    'app_name'      => 'VIETTELPAY',
                    'app_version'   => $this->configure['app_version'],
                    'imei'          => $this->bplusVTPay->imei,
                    'order_id'      => getOrderId(),
                    'type_os'       => 'ios'
                ],
                'headers'   => array(
                    'content-type'      => 'application/json',
                    'authorization'     => 'Bearer '.$this->bplusVTPay->access_token,
                    'app-version'       => $this->configure['app_version'],
                    'channel'           => 'APP',
                    'product'           => 'VIETTELPAY',
                    'accept'            => '*/*',
                    'type-os'           => 'ios',
                    'accept-language'   => 'vi',
                    'accept-encoding'   => 'gzip;q=1.0, compress;q=0.5',
                    'device-name'       => 'iPhone',
                    'imei'              => $this->bplusVTPay->imei,
                    'user-agent'        => 'Viettel Money/5.0.8 (com.viettel.viettelpay; build:2; iOS 15.4.1) Alamofire/5.0.8',
                    'os-version'        => $this->configure['os_version'],
                    'authority-party'   => 'APP'
                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
            dd($e);
        }
        return false;
    }
    /**
     * @return object
     */
    protected function GOV_ID($imageFront, $imageBack)
    {
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/customer-ekyc/v1/kyc/gov-id', array(
                'headers'   => array(
                    'content-type'      => 'application/json',
                    'authorization'     => 'Bearer '.$this->bplusVTPay->access_token,
                    'app-version'       => $this->configure['app_version'],
                    'channel'           => 'APP',
                    'product'           => 'VIETTELPAY',
                    'accept'            => '*/*',
                    'type-os'           => 'ios',
                    'accept-language'   => 'vi',
                    'accept-encoding'   => 'gzip;q=1.0, compress;q=0.5',
                    'device-name'       => 'iPhone',
                    'imei'              => $this->bplusVTPay->imei,
                    'user-agent'        => 'Viettel Money/5.0.8 (com.viettel.viettelpay; build:2; iOS 15.4.1) Alamofire/5.0.8',
                    'os-version'        => $this->configure['os_version'],
                    'authority-party'   => 'APP'
                ),
                'json'      => array(
                    'activation'      => '0',
                    'govIdType'       => 'CCCD',
                    'base64ImageBack' => $imageBack,
                    'base64ImageFront'=> $imageFront                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }

    protected function REGISTER_OTP($otp)
    {
        $data  = array(
            'notifyToken'   => $this->bplusVTPay->token_notification,
            'transactionId' => $this->bplusVTPay->extra_data->transaction_id,
            'type'          => 'VERIFY',
            'verifyMethod'  => 'sms',
            'identityType'  => 'msisdn',
            'otp'           => $otp,
            'identityValue' => $this->bplusVTPay->username,
            'hash'          => hash('sha256', $this->bplusVTPay->imei),
            'imei'          => $this->bplusVTPay->imei,
            'typeOs'        => $this->configure['type_os'],
        );
        return $this->REGISTER($data);
    }
    /**
     * @return object
     */
    protected function REGISTER_ACCOUNT()
    {
        $data  = array(
            'type'          => 'REGISTER',
            'identityType'  => 'msisdn',
            'identityValue' => $this->bplusVTPay->username,
        );
        return $this->REGISTER($data);
    }
    /**
     * @return object
     */
    protected function MONEY_TRANSFER_INSIDE_SMS_OTP($amount, $receiver, $comment = 'NULL', $order_id, $otp)
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'recv_cust_mobile'  => $receiver,
                'recv_cust_bank_acc' => '',
                'recv_bankcode'     => 'VTT',
                'trans_amount'      => $amount,
                'trans_content'     => $comment,
                'service_type'      => '0',
                'money_source'      => $this->bplusVTPay->acc_no,
                'money_source_bank_code' => 'VTT',
                'otp_order_id'      => $order_id,
                'otp_code'          => $otp,
                'pin'               => $this->bplusVTPay->password
            ));
            $request = Encrypt::xml_encrypt('MONEY_TRANSFER_INSIDE_SMS_OTP', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */
    protected function MONEY_TRANSFER_INSIDE_SMS($amount, $receiver, $comment = 'NULL')
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'recv_cust_mobile'  => $receiver,
                'recv_cust_bank_acc' => '',
                'recv_bankcode'     => 'VTT',
                'trans_amount'      => $amount,
                'trans_content'     => $comment,
                'service_type'      => '0',
                'money_source'      => $this->bplusVTPay->acc_no,
                'money_source_bank_code' => 'VTT',
            ));
            $request = Encrypt::xml_encrypt('MONEY_TRANSFER_INSIDE_SMS', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }

    /**
     * @return object
     */
    protected function GET_LIST_BANK_FROM_MSISDN()
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'ben_msisdn'    => ''
            ));
            $request = Encrypt::xml_encrypt('GET_LIST_BANK_FROM_MSISDN', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */
    protected function GET_BENNAME_FROM_ACCOUNT_NUMBER($bank_acc, $bank_code)
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'recv_cust_bank_acc'    => $bank_acc,
                'recv_bankcode'         => $bank_code
            ));
            $request = Encrypt::xml_encrypt('GET_BENNAME_FROM_ACCOUNT_NUMBER', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }

    /**
     * @param int $amount
     * @param string $comment
     * @param string $bank_acc
     * @param string $bank_code
     * @param string $branch_name
     * @param string $order_id
     * @param string $otp
     * @return object
     */

    protected function MONEY_TRANSFER_OUTSIDE_SMS_OTP($amount, $comment, $bank_acc, $bank_code, $branch_name, $order_id, $otp)
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'money_source_bank_code' => 'VTT',
                'recv_cust_mobile'      => '',
                'bank_code'     => 'VTT',
                'recv_bank_branch_name' => $branch_name,
                'trans_content'         => $comment,
                'trans_amount'          => $amount,
                'recv_cust_bank_acc'    => $bank_acc,
                'recv_bankcode' => $bank_code,
                'money_source'  => $this->bplusVTPay->acc_no,
                'pin'           => $this->bplusVTPay->password,
                'otp_order_id'  => $order_id,
                'otp_code' => $otp
            ));
            $request = Encrypt::xml_encrypt('MONEY_TRANSFER_OUTSIDE_SMS_OTP', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @param int $amount
     * @param string $comment
     * @param string $bank_acc
     * @param string $bank_code
     * @param string $branch_name
     * @return object
     */

    protected function MONEY_TRANSFER_OUTSIDE_SMS($amount, $comment, $bank_acc, $bank_code, $branch_name = '')
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'money_source_bank_code' => 'VTT',
                'recv_cust_mobile'      => '',
                'bank_code'     => 'VTT',
                'recv_bank_branch_name' => $branch_name,
                'trans_content'         => $comment,
                'trans_amount'          => $amount,
                'recv_cust_bank_acc'    => $bank_acc,
                'recv_bankcode' => $bank_code,
                'money_source'  => $this->bplusVTPay->acc_no,
                'pin'           => $this->bplusVTPay->password
            ));
            $request = Encrypt::xml_encrypt('MONEY_TRANSFER_OUTSIDE_SMS', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }

    /**
     * @param string $code
     * @param string $service
     * @return object
     */
    protected function BILL_DEBIT_INQUIRY($code, $service = 'EVN')
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'service_code'  => $service,
                'tid_number'    => '',
                'app_name'      => 'VIETTELPAY',
                'bill_code'     => $code,
            ));
            $request = Encrypt::xml_encrypt('BILL_DEBIT_INQUIRY', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */

    protected function GET_LIST_WATER_SUPPLIER_NEWS()
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'is_new'        => '1'
            ));
            $request = Encrypt::xml_encrypt('GET_LIST_WATER_SUPPLIER_NEWS', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @param string $cust_identify
     * @param string $card_pin
     * @return object
     */

    protected function ADD_MONEY_SOURCE_SMS($cust_identify, $card_pin)
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'account_number' => $this->bplusVTPay->username,
                'money_source'  => $this->bplusVTPay->acc_no,
                'cust_identify' => $cust_identify,
                'card_pin'      => $card_pin,
                'pin'           => $this->bplusVTPay->password
            ));
            $request = Encrypt::xml_encrypt('ADD_MONEY_SOURCE_SMS', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @param string $start_date
     * @param string $end_date
     * @return object
     */
    protected function GET_HISTORY_VTP($start_date, $end_date)
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'      => $this->configure['app_name'],
                'typeos'        => $this->configure['type_os'],
                'app_version'   => $this->configure['app_version'],
                'os_version'    => $this->configure['os_version'],
                'imei'          => $this->bplusVTPay->imei,
                'order_id'      => getOrderId(),
                'session_id'    => $this->bplusVTPay->session_id,
                'account_number' => $this->bplusVTPay->username,
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'process_code'  => '0',
                'service_code'  => '',
                'bank_code_query' => 'ALL',
                'page'          => '0',
                'transfer_type' => '1',
            ));
            $request = Encrypt::xml_encrypt('GET_HISTORY_VTP', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */

    protected function BALANCE_INQUIRY_NO_PIN()
    {
        $encrypt = new Encrypt;
        $encrypt->setBplusVTPayKey($this->bplusVTPay->viettel_public_key, $this->bplusVTPay->client_private_key);
        try {
            $request = $encrypt->query(array(
                'app_name'    => $this->configure['app_name'],
                'typeos'      => $this->configure['type_os'],
                'app_version' => $this->configure['app_version'],
                'os_version'  => $this->configure['os_version'],
                'imei'        => $this->bplusVTPay->imei,
                'order_id'    => getOrderId(),
                'session_id'  => $this->bplusVTPay->session_id,
                'bank_code'   => 'VTT',
                'money_source' => $this->bplusVTPay->acc_no,
                'money_source_bank_code' => 'VTT'
            ));
            $request = Encrypt::xml_encrypt('BALANCE_INQUIRY_NO_PIN', $encrypt->encrypt($request), $encrypt->signature($request));
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'         => 'bankplus.vn',
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Connection'   => 'keep-alive',
                    'SOAPAction'   => 'gwOperator',
                    'Accept'       => '*/*',
                    'User-Agent'   => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => trim($request)
            ));
            return $encrypt->decrypt($encrypt->xml_decrypt($res->getBody()->getContents()));
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */
    protected function REFRESH()
    {
        try {
            $res = $this->client->request('GET', 'https://api8.viettelpay.vn/auth/v1/authn/refresh', array(
                'headers'   => array(
                    'host'            => 'api8.viettelpay.vn',
                    'content-type'    => 'application/json',
                    'accept'          => '*/*',
                    'app_version'     => $this->configure['app_version'],
                    'product'         => 'VIETTELPAY',
                    'type_os'         => $this->configure['type_os'],
                    'accept-language' => 'vi',
                    'imei'            => $this->bplusVTPay->imei,
                    'user-agent'      => 'ViettelPay/' . $this->configure['app_version'] . ' (com.viettel.viettelpay; build:1; iOS 15.4) Alamofire/' . $this->configure['app_version'] . '',
                    'os_version'      => $this->configure['os_version'],
                    'authority-party' => 'APP',
                    'authorization'   => 'Bearer ' . $this->bplusVTPay->access_token
                ),
                'json'      => array(
                    'refreshToken'  => $this->bplusVTPay->refresh_token
                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }
        /**
     * @return object
     */

    protected function SESSION()
    {
        try {
            $res = $this->client->request('GET', 'https://api8.viettelpay.vn/customer/v1/accounts', array(
                'headers' => array(
                    'host'            => 'api8.viettelpay.vn',
                    'content-type'    => 'application/json',
                    'accept'          => '*/*',
                    'app_version'     => $this->configure['app_version'],
                    'product'         => 'VIETTELPAY',
                    'type_os'         => 'ios',
                    'accept-language' => 'vi',
                    'imei'            => $this->bplusVTPay->imei,
                    'user-agent'      => 'ViettelPay/' . $this->configure['app_version'] . ' (com.viettel.viettelpay; build:1; iOS 15.4) Alamofire/' . $this->configure['app_version'] . '',
                    'os_version'      => $this->configure['os_version'],
                    'authority-party' => 'APP',
                    'authorization'   => 'Bearer ' . $this->bplusVTPay->access_token
                ),
                'query' => array(
                    'sources' => '1',
                    'recommendations' => '1'
                )
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @param string $otp
     * @return object
     */
    protected function LOGIN_WITH_OTP($otp)
    {
        $data = array(
            'typeOs'      => $this->configure['type_os'],
            'notifyToken' => $this->bplusVTPay->token_notification,
            'userType'    => 'msisdn',
            'pin'         => $this->bplusVTPay->password,
            'imei'        => $this->bplusVTPay->imei,
            'msisdn'      => $this->bplusVTPay->username,
            'loginType'   => 'BASIC',
            'otp'         => $otp,
            'username'    => $this->bplusVTPay->username,
            'requestId'   => $this->bplusVTPay->extra_data->request_id,
        );
        return $this->LOGIN($data);
    }
    /**
     * @return object
     */

    protected function LOGIN_NEED_PIN($password)
    {
        $data = array(
            'userType'    => 'msisdn',
            'loginType'   => 'BASIC',
            'pin'         => $password,
            'msisdn'      => $this->bplusVTPay->username,
            'imei'        => $this->bplusVTPay->imei,
            'username'    => $this->bplusVTPay->username,
            'notifyToken' => $this->bplusVTPay->token_notification,
            'typeOs'      => $this->configure['type_os'],
        );
        return $this->LOGIN($data);
    }
    /**
     * @return object
     */
    protected function REGISTER(array $data)
    {
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/customer/v2/accounts/register', array(
                'headers' => array(
                    'host'            => 'api8.viettelpay.vn',
                    'content-type'    => 'application/json',
                    'accept'          => '*/*',
                    'app_version'     => $this->configure['app_version'],
                    'product'         => 'VIETTELPAY',
                    'type_os'         => 'ios',
                    'accept-language' => 'vi',
                    'imei'            => $this->bplusVTPay->imei,
                    'user-agent'      => 'ViettelPay/' . $this->configure['app_version'] . ' (com.viettel.viettelpay; build:1; iOS 15.4) Alamofire/' . $this->configure['app_version'] . '',
                    'os_version'      => $this->configure['os_version'],
                    'authority-party' => 'APP',
                ),
                'json'    => $data
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */
    protected function CHECK_USER()
    {
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/customer/v1/validate/account', array(
                'headers' => array(
                    'host'            => 'api8.viettelpay.vn',
                    'content-type'    => 'application/json',
                    'accept'          => '*/*',
                    'app_version'     => $this->configure['app_version'],
                    'product'         => 'VIETTELPAY',
                    'type_os'         => 'ios',
                    'accept-language' => 'vi',
                    'imei'            => $this->bplusVTPay->imei,
                    'user-agent'      => 'ViettelPay/' . $this->configure['app_version'] . ' (com.viettel.viettelpay; build:1; iOS 15.4) Alamofire/' . $this->configure['app_version'] . '',
                    'os_version'      => $this->configure['os_version'],
                    'authority-party' => 'APP',
                ),
                'json' => array(
                    'username' => $this->bplusVTPay->username,
                    'type'     => 'msisdn',
                ),
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }
    /**
     * @return object
     */
    protected function LOGIN(array $data)
    {
        try {
            $res = $this->client->request('POST', 'https://api8.viettelpay.vn/auth/v1/authn/login', array(
                'headers'   => array(
                    'host'            => 'api8.viettelpay.vn',
                    'content-type'    => 'application/json',
                    'accept'          => '*/*',
                    'app_version'     => $this->configure['app_version'],
                    'product'         => 'VIETTELPAY',
                    'type_os'         => 'ios',
                    'accept-language' => 'vi',
                    'imei'            => $this->bplusVTPay->imei,
                    'user-agent'      => 'ViettelPay/' . $this->configure['app_version'] . ' (com.viettel.viettelpay; build:1; iOS 15.4) Alamofire/' . $this->configure['app_version'] . '',
                    'os_version'      => $this->configure['os_version'],
                    'authority-party' => 'APP',
                ),
                'json'      => $data
            ));
            return json_decode($res->getBody());
        } catch (\Throwable $e) {
        }
        return false;
    }

    /**
     * @return string
     */

    private function SETUP_SOFTWARE()
    {
        try {
            $res = $this->client->request('POST', 'https://bankplus.vn/MobileAppService2/ServiceAPI', array(
                'headers' => array(
                    'Host'          => 'bankplus.vn',
                    'Content-Type'  => 'text/xml; charset=utf-8',
                    'Connection'    => 'keep-alive',
                    'SOAPAction'    => 'gwOperator',
                    'Accept'        => '*/*',
                    'User-Agent'    => 'ViettelPay/' . $this->configure['app_version'] . ' (iPhone; iOS 15.4; Scale/3.00)',
                    'Accept-Language' => 'vi;q=1',
                ),
                'body'    => Encrypt::xml_encrypt('SETUP_SOFTWARE', urlencode(http_build_query(array(
                    'app_version' => $this->configure['app_version'],
                    'order_id'    => getOrderId(),
                    'imei'        => $this->bplusVTPay->imei,
                    'os_version'  => $this->configure['os_version'],
                    'type_os'     => 'ios',
                    'token_notification' => $this->bplusVTPay->token_notification,
                    'app_name'    => $this->configure['app_name'],
                ))), 'null')
            ));
            return $res->getBody()->getContents();
        } catch (\Throwable $e) {
        }
        return false;
    }

    protected function pushOfExtraData($key, $value): void
    {

        $extra_data = (array) $this->bplusVTPay->extra_data;
        $extra_data[$key]   = $value;
        $this->bplusVTPay->extra_data = (object) $extra_data;
        $this->bplusVTPay->save();
    }

    protected function generateRsa(): void
    {
        $result = $this->SETUP_SOFTWARE();
        if ($result !== false) {
            $explode = explode('&amp', $result);
            foreach ($explode as $values) {
                if (strstr($values, 'client_private_key')) {
                    $client_private_key = substr($values, 20);
                    $this->bplusVTPay->client_private_key = "-----BEGIN PRIVATE KEY-----\n" . $client_private_key . "\n-----END PRIVATE KEY-----";
                } else if (strstr($values, 'viettel_public_key')) {
                    $viettel_public_key = substr($values, 20);
                    $this->bplusVTPay->viettel_public_key = "-----BEGIN PUBLIC KEY-----\n" . $viettel_public_key . "\n-----END PUBLIC KEY-----";
                }
            }
        }
    }

    protected function refreshAccessToken(): void
    {
        try {
            $decoded_token = Encrypt::decode($this->bplusVTPay->access_token);
            if (is_object($decoded_token)){
                if ($decoded_token->exp < time()) {
                    $result = $this->LOGIN_NEED_PIN($this->bplusVTPay->password);
                    if ($result !== false) {
                        if ($result->status->code == '00') {
                            $this->bplusVTPay->access_token  = $result->data->accessToken;
                            $this->bplusVTPay->refresh_token = $result->data->refreshToken;
                            $this->getSessionId();
                            $this->bplusVTPay->refresh_at = Carbon::now();
                            $this->bplusVTPay->save();
                        }
                    }
                }
            }
        } catch (\Throwable $th) {}
    }

    private function getSessionId(): void
    {
        $result = $this->SESSION();
        if ($result !== false) {
            if ($result->status->code == '00') {
                $this->bplusVTPay->session_id = $result->data->otherData->sessionId;
                $this->bplusVTPay->acc_no     = $result->data->sources->infra['0']->accNo ?? null;
            }
        }
    }
}
