<?php

use VandatPiko\BplusVTPay\Contracts\BplusVTPayContract;

if (!function_exists('convertPhonenumberTo84')) {
    /**
     * convert phonenumber
     */

    function convertPhonenumberTo84($phonenumber)
    {
        $CELL = array(
            '016966' => '03966',
            '0169' => '039',
            '0168' => '038',
            '0167' => '037',
            '0166' => '036',
            '0165' => '035',
            '0164' => '034',
            '0163' => '033',
            '0162' => '032',
            '0120' => '070',
            '0121' => '079',
            '0122' => '077',
            '0126' => '076',
            '0128' => '078',
            '0123' => '083',
            '0124' => '084',
            '0125' => '085',
            '0127' => '081',
            '0129' => '082',
            '01992' => '059',
            '01993' => '059',
            '01998' => '059',
            '01999' => '059',
            '0186' => '056',
            '0188' => '058'
        );
        /**
         * replace space with underscore
         */

        $phonenumber = preg_replace('/\s+/', '', $phonenumber);

        $phonenumber = str_replace(['.','-','+','(',')'], '', $phonenumber);
        //7. Chuyển 84 đầu thành 0
        if (substr($phonenumber, 0, 2) == '84') {
            $phonenumber = '0' . substr($phonenumber, 2, strlen($phonenumber) - 2);
        }
        foreach ($CELL as $key => $value) {
            //$prefixlen=strlen($key);
            if (strpos($phonenumber, $key) === 0) {
                $prefix = $key;
                $prefixlen = strlen($key);
                $phone = substr($phonenumber, $prefixlen, strlen($phonenumber) - $prefixlen);
                $prefix = str_replace($key, $value, $prefix);
                $phonenumber = $prefix . $phone;
                //$phonenumber=str_replace($key,$value,$phonenumber);
                break;
            }
        }

        return '84' . substr($phonenumber, 1);
    }
}

if (!function_exists('generateRandom')) {
    function generateRandom($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length){
        $characters = '0123456789abcdef';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('getMicrotime')) {
    function getMicrotime()
    {
        return round(microtime(true) * 1000);
    }
}

if (!function_exists('generateImei')){
    function generateImei(){
        return generateRandomString(8) . '-' . generateRandomString(4) . '-' . generateRandomString(4) . '-' . generateRandomString(4) . '-' . generateRandomString(12);
    }
}

if (!function_exists('generateToken')) {
    function generateToken(){
        return generateRandom(22) . ':' .generateRandom(9) . '-' .generateRandom(20) . '-' .generateRandom(12) . '-' .generateRandom(7) . '-' .generateRandom(7) . '-' .generateRandom(53) . '-' .generateRandom(9) . '_' .generateRandom(11) . '-' .generateRandom(4);
    }
}

if (!function_exists('getOrderId')){
    function getOrderId(){
        return date('Ymdhis');
    }
}

if (!function_exists('bplusvtpay')){
    /**
     * @return \VandatPiko\BplusVTPay\Contracts\BplusVTPayContract
     */
    function bplusvtpay($username = ''){
        /**
         * @var \VandatPiko\BplusVTPay\Contracts\BplusVTPayContract $factory
         */
        $factory = app()->make(BplusVTPayContract::class);
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->setBplusVTPay($username);
    }
}
