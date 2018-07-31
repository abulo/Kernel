<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 2016/9/9
 * Time: 17:33
 */

namespace Kernel\Coroutine;

class CoroutineNull
{
    private static $instance;

    public function __construct()
    {
        self::$instance = &$this;
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            new CoroutineNull();
        }
        return self::$instance;
    }
}
