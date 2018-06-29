<?php

namespace Kernel\Utilities;

class Token
{
    /**
     * 加密
     * @param  string  $string    待加密字符串
     * @param  string  $key       秘钥
     * @return string
     */
    public static function encode($string, $key = '')
    {
        $key = md5($key ? $key : 'weimeng');



        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        $ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);
        return $ciphertext;
        // $abc = @openssl_encrypt($string, 'des-ede3', $key, true);
        // return strtoupper(@bin2hex($abc));
    }
    /**
     * 解密
     * @param  string  $string    密字符串
     * @param  string  $key       秘钥
     * @return string
     */
    public static function decode($string, $key = '')
    {
        $key = md5($key ? $key : 'weimeng');


        $c = base64_decode($string);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {//PHP 5.6+ timing attack safe comparison
            return $original_plaintext;
        } else {
            return false;
        }
        // try {
        //     $string=@pack("H*", strtolower($string));
        //     $abc = @openssl_decrypt($string, 'des-ede3', $key, true);
        // } catch (\Exception $e) {
        //     $abc = false;
        // }


        // return  $abc;
    }
}
