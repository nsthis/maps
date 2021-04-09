<?php
/**
 * 2  * Created by PhpStorm.
 * 3  * User: 86156
 * 4  * Date: 2020/1/7
 * 5  * Time: 13:48
 * 6  */
namespace elliot;

class Crypt
{

    /**
     * 加密字符串
     * @param  string $string 要加密的字符串
     * @param  string $key    加密私钥
     * @return array
     */
    public function encrypt128($string, $key)
    {
        $iv = mt_rand(1111111111111111, 9999999999999999);
        $encrypted = openssl_encrypt($string, 'AES-128-CBC', $key, $options=OPENSSL_RAW_DATA, $iv);
        $data = [
            'sign' => hash_hmac('sha256', base64_encode($encrypted), $key),
            'data' => base64_encode($iv.$encrypted)
        ];
        return $data;
    }

    /**
     * 解密字符串
     * @param  string $string 要解密的字符串
     * @param  string $key    私钥
     * @param  string $hash   校验hash
     * @return string
     */
    public function decrypt128($string, $key, $sign)
    {
        $data = base64_decode($string);
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = substr($data, 0, $ivlen);
        $data = substr($data, $ivlen);
        $decrypted = openssl_decrypt($data, 'AES-128-CBC', $key, $options=OPENSSL_RAW_DATA, $iv);

        if(!$decrypted) {
            return false;
        }
        $hash_str = hash_hmac('sha256', base64_encode($data), $key);

        if($hash_str !== $sign){
            return false;
        }
        return $decrypted;
    }
}