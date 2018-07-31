<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-9-28
 * Time: 下午2:49
 */

namespace Kernel\Components\Middleware;

use Kernel\CoreBase\CoreBase;
use Kernel\CoreBase\SwooleInterruptException;

abstract class Middleware extends CoreBase implements IMiddleware
{
    abstract public function before_handle();

    abstract public function after_handle($path);

    public function interrupt()
    {
        throw new SwooleInterruptException('interrupt');
    }
}
