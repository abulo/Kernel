<?php

namespace Kernel\CoreBase;

use Kernel\Components\AOP\Proxy;
use Kernel\Coroutine\CoroutineNull;
use Kernel\Memory\Pool;


class TheTaskProxy extends Proxy
{

    public function beforeCall($name, $arguments = null)
    {
    }

    public function afterCall($name, $arguments = null)
    {
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->own, $name], $arguments);
        if ($result == null) {
            $result = CoroutineNull::getInstance();
        }
        return $result;
    }
}
