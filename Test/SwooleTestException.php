<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-12-30
 * Time: 下午2:33
 */

namespace Kernel\Test;

class SwooleTestException extends \Exception
{
    const ERROR = 0;
    const SKIP = 1;

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}
