<?php
namespace VandatPiko\BplusVTPay\Contracts;

interface EncryptContract
{
    /**
     * @return string
     */
    public static function xml_encrypt(string $cmd, string $data, string $signature);
    /**
     * @param string $encrypted
     * @return object
     */

    public function xml_decrypt(string $encrypted);
    /**
     * @param string $data
     * @return string
     */
    public function signature($data);
    /**
     * @param string $token
     * @return object $payload
     */
    public static function decode($token);



}
