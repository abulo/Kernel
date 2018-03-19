<?php

namespace Kernel\Components\WeChat\Kernel\Support;

/**
 * Class Util
 *
 * @package \Kernel\Components\WeChat\Support
 */
class Util
{
    public static function GenerateSign(array $attributes, $key, $encryptMethod = 'md5')
    {
        ksort($attributes);

        $attributes['key'] = $key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }
}
