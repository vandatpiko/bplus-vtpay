<?php
namespace VandatPiko\BplusVTPay\Contracts;

interface BplusVTPayContract
{
    /**
     *
     * @return VandatPiko\BplusVTPay\Contracts\BplusVTPayContract
     */
    public function getBplusVTPay($username = null);

    /**
     * @param string $username
     * @return \VandatPiko\BplusVTPay\Contracts\BplusVTPayContract
     */
    public function setBplusVTPay($username);
    /**
     * @return object
     */
    public function loginWithPassword($password = null);
        /**
     * @return object
     */
    public function checkUser();
    /**
     * @return object
     */
    public function loginWithOTP($otp);
    /**
     * @return object
     */
    public function getBalance();
    /**
     * @return object
     */
    public function registerAccount();
    /**
     * @return object
     */
    public function registerWithOTP($otp);
    /**
     * @return object
     */
    public function getHistoryVTP($start_date = null, $end_date = null);
    /**
     * @return object
     */
    public function ekycFace(object $base64ImageArray);
    /**
     * @return object
     */
    public function ekycImage($imageFront = '',$imageBack = '');
    /**
     * @return object
     */
    public function ekycActive($data);
    /**
     * @return object
     */
    public function getGift();
    /**
     * @return bool
     */
    public function getStatus();
}
