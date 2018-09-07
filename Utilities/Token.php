<?php

namespace Kernel\Utilities;

use Kernel\Utilities\JWT\JWT;

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



        try {
            return JWT::encode($string, $key, 'HS256');
        } catch (\Exception $e) {
            return false;
        }
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
            return JWT::decode($string, $key, ['HS256']);
        } catch (\Exception $e) {
            return false;
        }
    }
}
