<?php
/**
 * @Date: 13-9-4 下午4:35
 * @version  2.5
 *  验证码扩展类
 */
namespace Kernel\Utilities;

class VerifyCode
{
    /**
     * 生成图像数字验证码
     *
     * @param string $length  位数
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
     * @param string $font字体名
     *
     * @return void
     */
    public static function numVerify($length = 4, $type = 'png', $width = 150, $height = 35, $verifyName = 'verifyCode', $font = 'tahoma.ttf')
    {
        $randNum = substr(str_shuffle(str_repeat('0123456789', 5)), 0, $length);
        $authKey = md5(mt_rand().microtime());
        // \Kernel\Http\Cookie::set($verifyName, $authKey);
        // \Kernel\Model::getInstance()->cache()->set($authKey, $randNum, 60);
        $width = ($length * 33 + 20) > $width ? $length * 33 + 20 : $width;
        $height = $length < 35 ? 35 : $height;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($width, $height);
        } else {
            $im = imagecreate($width, $height);
        }
        $r = array(225, 255, 255, 223);
        $g = array(225, 236, 237, 255);
        $b = array(225, 236, 166, 125);
        $key = mt_rand(0, 3);

        $backColor = @imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);//背景色（随机）
        $borderColor = @imagecolorallocate($im, 100, 100, 100); //边框色
        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = @imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 干扰
        for ($i = 0; $i < 10; $i++) {
            @imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $stringColor);
        }
        for ($i = 0; $i < 25; $i++) {
            @imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $stringColor);
        }
        for ($i = 0; $i < $length; $i++) {
            $x = $i === 0 ? 15 : $i*35;
            $stringColor = @imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
            @imagettftext($im, 28, mt_rand(0, 60), $x, 35, $stringColor, __DIR__.DIRECTORY_SEPARATOR.$font, $randNum[$i]);
        }
        return self::output($im, $type, $randNum);
    }

    /**
     * 中文验证码
     *
     * @param int $length
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $font
     * @param string $verifyName
     *
     * @return void
     */
    public static function CnVerify($length = 4, $type = 'png', $width = 180, $height = 50, $font = 'tahoma.ttf', $verifyName = 'verifyCode')
    {
        $code = StringProcess::randString($length, 4);
        $width = ($length * 45) > $width ? $length * 45 : $width;
        $authKey = md5(mt_rand().microtime());
        // \Kernel\Http\Cookie::set($verifyName, $authKey);
        // \Kernel\Model::getInstance()->cache()->set($authKey, md5($code), 60);
        $im = imagecreatetruecolor($width, $height);
        $borderColor = imagecolorallocate($im, 100, 100, 100);  //边框色
        $bkcolor = imagecolorallocate($im, 250, 250, 250);
        imagefill($im, 0, 0, $bkcolor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        // 干扰
        for ($i = 0; $i < 15; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $fontcolor);
        }
        for ($i = 0; $i < 255; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $fontcolor);
        }
        if (!is_file($font)) {
            $font = __DIR__.DIRECTORY_SEPARATOR.$font;
        }
        for ($i = 0; $i < $length; $i++) {
            $fontcolor = imagecolorallocate($im, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            $codex = StringProcess::substrCn($code, $i, 1);
            imagettftext($im, mt_rand(16, 20), mt_rand(-60, 60), 40 * $i + 20, mt_rand(30, 35), $fontcolor, $font, $codex);
        }
        return self::output($im, $type, $code);
    }

    /**
     * 生成数字计算题验证码
     *
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $font
     * @param string $verifyName
     *
     * @return void
     */
    public static function calocVerify($type = 'png', $width = 170, $height = 45, $font = 'tahoma.ttf', $verifyName = 'verifyCode')
    {
        $la = rand(0, 9);
        $ba = rand(0, 9);
        $randnum = rand(1, 3);
        if ($randnum == 3) {
            if ($la < $ba) {
                $tmp = $la;
                $la = $ba;
                $ba = $tmp;
            }
        }
        $randarr= array(
            1 => $la + $ba,
            2 => $la * $ba,
            3 => $la - $ba
            // 4 => $la / $ba,
        );
        $randstr = $randarr[$randnum];
        $randResult = array(
            1 => $la .'+'. $ba.'=?',
            2 => $la .'*'. $ba.'=?',
            3 => $la .'-'.$ba.'=?'
            // 4 => $la .'/'. $ba.'='. $randarr[4],
        );
        $randval = $randResult[$randnum];
        $authKey = md5(mt_rand().microtime());
        // \Kernel\Http\Cookie::set($verifyName, $authKey);

        // \Kernel\Model::getInstance()->cache()->set($authKey, $randstr, 60);
        //$width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($width, $height);
        } else {
            $im = imagecreate($width, $height);
        }
        $r = array(225, 255, 255, 223);
        $g = array(225, 236, 237, 255);
        $b = array(225, 236, 166, 125);
        $key = mt_rand(0, 3);

        $backColor = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 干扰
        for ($i = 0; $i < 10; $i++) {
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $stringColor);
        }
        for ($i = 0; $i < 25; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $stringColor);
        }
        for ($i = 0; $i < 5; $i++) {
            //  imagestring($im, 5, $i * 10 + 5, mt_rand(1, 8), $randval{$i}, $stringColor);
            $x = $i === 0 ? 20 : $i*50;
            $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
            if ($i == 1 || $i == 3 || $i ==4) {
                $fontSize = $randnum == 3 ? 50 : 28;
                if ($i == 1) {
                    imagettftext($im, $fontSize, 0, $x, 35, $stringColor, __DIR__.DIRECTORY_SEPARATOR.$font, $randval[$i]);
                } else {
                    $decNum = $i == 3 ? 30 : 55;
                    imagettftext($im, 25, 0, $x-$decNum, 35, $stringColor, __DIR__.DIRECTORY_SEPARATOR.$font, $randval[$i]);
                }
            } else {
                imagettftext($im, 28, mt_rand(0, 60), $x, 35, $stringColor, __DIR__.DIRECTORY_SEPARATOR.$font, $randval[$i]);
            }
        }
        return self::output($im, $type, $randstr);
    }

    /**
     * 输出图片
     *
     * @param string $image 被载入的图片
     * @param string $type 输出的类型
     *
     * @return void
     */
    public static function output($image, $type = 'png', $code = null)
    {
        $quality = 100;
        $type == 'jpg' && $type = 'jpeg';
        $imageFun = "image{$type}";
        ob_start();
        if ($type == 'jpeg') {
            $imageFun($image, null, $quality);
        } else {
            $imageFun($image);
        }
        $data = ob_get_clean();
        ImageDestroy($image);
        $header = "image/{$type}";
        return array('code' => $code, 'data' => $data,'content_type'=>$header);
    }
}