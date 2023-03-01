<?php
namespace VandatPiko\BplusVTPay;

use VandatPiko\BplusVTPay\Contracts\EncryptContract;

class Encrypt implements EncryptContract
{

    protected $viettel_public_key;

    protected $client_private_key;

    public function setBplusVTPayKey($viettel_public_key, string $client_private_key)
    {
        $this->viettel_public_key = $viettel_public_key;
        $this->client_private_key = $client_private_key;
        return $this;
    }
    public static function xml_encrypt(string $cmd, string $data, string $signature)
    {
        $xmlheader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ba=\"http://bankplus.vn\"><SOAP-ENV:Header/> \r\n<SOAP-ENV:Body>\r\n<ba:gwOperator><cmd>";
        $xmlheader .= $cmd . "</cmd>";
        $xmlheader .= "<encrypted>".$data."</encrypted>";
        $xmlheader .= "<signature>".$signature."</signature>";
        $xmlheader .= "</ba:gwOperator>";
        $xmlheader .= "\r\n";
        $xmlheader .= "</SOAP-ENV:Body>";
        $xmlheader .= "\r\n";
        $xmlheader .= "</SOAP-ENV:Envelope>";
        return $xmlheader;
    }

    public function xml_decrypt(string $encrypted)
    {
        $string = '';
        $explode = explode('&amp', $encrypted);
        foreach ($explode as $item){
            if(strstr($item, 'encrypted')){
                $encrypted = explode('encrypted=', $item)[1];
                break;
            }
        }
        $array = str_split($encrypted, 344);
        foreach ($array as $rows) {
            $string .= openssl_private_decrypt(strrev(base64_decode($rows)),$decrypted_data,$this->client_private_key,OPENSSL_PKCS1_PADDING) ? $decrypted_data : null;
        }

        return $string;
    }

    public function signature($data)
    {
        openssl_sign($data,$singature,$this->client_private_key,OPENSSL_ALGO_SHA1);
        return base64_encode($singature);
    }

    public function encrypt($request)
    {
        $string = '';
        $array  = str_split($request, 86);
        foreach ($array as $item) {
            if(openssl_public_encrypt($item,$encrypted_data,$this->viettel_public_key,OPENSSL_PKCS1_PADDING)) {
                $base64  = strrev($encrypted_data);
                $string .= base64_encode($base64);
            }
        }
        return $string;
    }

    public function query(array $array)
    {
        $string = '';
        foreach ($array as $keys => $item) {

            $string .= $keys;
            $string .= '='.$item;
            $string .= '&';

        }
        return rtrim($string,'&');
    }

    public function decrypt($encrypted)
    {
        if (!is_object(json_decode($encrypted))) {
            $array   = array();
            $explode = explode('&',$encrypted);
            foreach ($explode as $item) {
                $exp = explode('=',$item);
                switch (count($exp)) {
                    case 1:
                        break;
                    case 2:
                        $array[$exp[0]] = $exp[1];
                        break;
                    default:
                        $array[$exp[0]] = substr($item, strlen($exp[0]) + 1);
                        break;
                }

            }
            return (object) $array;
        }
        return json_decode($encrypted);
    }

    public static function decode($token)
    {
        if (is_string($token)) {
            $split_token = explode('.',$token);
            $payload_base= $split_token[1];
            return json_decode(base64_decode($payload_base));
        }
        return 0;
    }

}
