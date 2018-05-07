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
        $abc = @openssl_encrypt($string, 'des-ede3', $key, true);
        return strtoupper(@bin2hex($abc));
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
        try {
            $string=@pack("H*", strtolower($string));
            $abc = @openssl_decrypt($string, 'des-ede3', $key, true);
        } catch (\Exception $e) {
            $abc = false;
        }


        return  $abc;
    }
}
