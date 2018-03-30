<?php

namespace Kernel\Components\WeChat\Payment\Notify;

/**
 * Class Paid
 *
 * @package \Kernel\Components\WeChat\Payment\Notify
 */
class Paid extends Handler
{
    public function handle(\Closure $closure)
    {
        $result =  $closure($this->getMessage(), [$this, 'fail']);

        $this->strict($result);

        return $this->toResponseData();
    }
}
